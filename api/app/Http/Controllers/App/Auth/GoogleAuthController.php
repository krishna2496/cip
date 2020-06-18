<?php
namespace App\Http\Controllers\App\Auth;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\User;
use Hybridauth\Hybridauth;
use Illuminate\Http\Request;
use App\Repositories\User\UserRepository;

class GoogleAuthController extends Controller
{
    private $helpers;
    private $user;
    private $userRepository;

    public function __construct(
        Helpers $helpers,
        User $user,
        UserRepository $userRepository
    ) {
        $this->helpers = $helpers;
        $this->user = $user;
        $this->userRepository = $userRepository;
    }

    // TODO: Refactor!!!
    public function login(Request $request)
    {
        $frontendFqdn = $request->input('domain');
        $tenantId = $request->input('tenant');
        $state = $request->input('state');

        if ($state) {
            $decodedToken = $this->helpers->decodeJwtToken($state);
            $frontendFqdn = $decodedToken->domain;
            $tenantId = $decodedToken->tenant;
        }

        $this->helpers->createConnection($tenantId);
        $state = $this->helpers->encodeJwtToken([
            'tenant' => $tenantId,
            'domain' => $frontendFqdn
        ], 60);

        $config = [
            'callback' => route('google.authentication'),
            'providers' => [
                "Google" => [
                    "enabled" => true,
                    "keys" => [
                        "id" => env('GOOGLE_AUTH_ID'),
                        "secret" => env('GOOGLE_AUTH_SECRET'),
                    ],
                    'authorize_url_parameters' => [
                        'state' => $state,
                    ]
                ]
            ]
        ];

        $hybridauth = new Hybridauth($config);
        $adapter = $hybridauth->authenticate('Google');
        $isConnected = $adapter->isConnected();
        $errorUrlPattern = 'http%s://%s/auth/sso/error?errors=%s&source=google';

        if (!$isConnected) {
            $redirectUrl = sprintf(
                $errorUrlPattern,
                ($request->secure() ? 's' : ''),
                $frontendFqdn,
                'GOOGLE_AUTH_ERROR',
            );
            return redirect($redirectUrl);
        }

        $userProfile = $adapter->getUserProfile();

        $userEmail = $userProfile->email;

        $isOptimyDomain = preg_match('/\.optimy\.com$/i', $userEmail) || preg_match('/@optimy\.com$/i', $userEmail);

        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL) || !$isOptimyDomain) {
            $redirectUrl = sprintf(
                $errorUrlPattern,
                ($request->secure() ? 's' : ''),
                $frontendFqdn,
                'INVALID_EMAIL',
            );
            return redirect($redirectUrl);
        }

        $isAdminUser = $this->helpers->isAdminUser($userEmail);

        if (!$isAdminUser) {
            $redirectUrl = sprintf(
                $errorUrlPattern,
                ($request->secure() ? 's' : ''),
                $frontendFqdn,
                'GOOGLE_AUTH_UNAUTHORIZE',
            );
            return redirect($redirectUrl);
        }

        $userDetail = $this->user
            ->where('email', $userEmail)
            ->first();

        $userData = [
            'first_name' => $userProfile->firstName,
            'last_name' => $userProfile->lastName,
            'email' => $userProfile->email,
        ];

        if (!$userDetail) {
            $language = $this->languageHelper->getLanguages()->first();
            $userData['language_id'] = $language->id;
        }

        $userDetail = $userDetail ?
            $this->userRepository->update($userData, $userDetail->user_id) :
            $this->userRepository->store($userData);

        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        $token = $this->helpers->getJwtToken(
            $userDetail->user_id,
            $tenantName,
            true,
            60
        );

        $redirectUrl = sprintf(
            'http%s://%s/auth/sso?token=%s',
            ($request->secure() ? 's' : ''),
            $frontendFqdn,
            $token,
        );

        return redirect($redirectUrl);
    }
}

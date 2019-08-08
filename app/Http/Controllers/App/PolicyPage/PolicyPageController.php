<?php
namespace App\Http\Controllers\App\PolicyPage;

use App\Repositories\PolicyPage\PolicyPageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\PolicyPage;
use App\Models\PolicyPagesLanguage;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Helpers\LanguageHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;

class PolicyPageController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\PolicyPage\PolicyPageRepository
     */
    private $policyPageRepository;
    
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
    
    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\PolicyPage\PolicyPageRepository $policyPageRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        PolicyPageRepository $policyPageRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper
    ) {
        $this->policyPageRepository = $policyPageRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
    }
    
    /**
     * Display a listing of policy pages.
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $languages = $this->languageHelper->getLanguages($request);

            $language = ($request->hasHeader('X-localization')) ?
            $request->header('X-localization') : env('TENANT_DEFAULT_LANGUAGE_CODE');
            $languageId = $languages->where('code', $language)->first()->language_id;
            
            $pageList = $this->policyPageRepository->getPageList($languageId);
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_POLICY_PAGE_LISTING');
            $apiMessage = ($pageList->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
             trans('messages.success.MESSAGE_POLICY_PAGE_LISTING');
            return $this->responseHelper->success($apiStatus, $apiMessage, $pageList->toArray());
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string $slug
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $languages = $this->languageHelper->getLanguages($request);

            $language = ($request->hasHeader('X-localization')) ?
            $request->header('X-localization') : env('TENANT_DEFAULT_LANGUAGE_CODE');
            $languageId = $languages->where('code', $language)->first()->language_id;
           
            $policyPage = $this->policyPageRepository->getPageDetail($slug, $languageId);
          
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_PAGE_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $policyPage->toArray());
        } catch (PDOException $e) {
            return $this->PDO(
                config('constants.error_codes.ERROR_DATABASE_OPERATIONAL'),
                trans(
                    'messages.custom_error_message.ERROR_DATABASE_OPERATIONAL'
                )
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NO_DATA_FOUND_FOR_SLUG'),
                trans('messages.custom_error_message.ERROR_NO_DATA_FOUND_FOR_SLUG')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }
}

<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Repositories\TenantSetting\TenantSettingRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Models\TenantSetting;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Validator;

class TenantSettingsController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var  App\Repositories\TenantSetting\TenantOptionRepository
     */
    private $tenantSettingRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new controller instance.
     *
     * @param   App\Repositories\TenantSetting\TenantSettingRepository $tenantSettingRepository
     * @return void
     */
    public function __construct(TenantSettingRepository $tenantSettingRepository, ResponseHelper $responseHelper) {
        $this->tenantSettingRepository = $tenantSettingRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $settings = $this->tenantSettingRepository->getAllSettings();

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_TENANT_SETTINGS_LISTING');
            $apiMessage = ($settings->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
             trans('messages.success.MESSAGE_TENANT_SETTINGS_LISTING');

            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $settings);
        }  catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $settingId     
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $settingId): JsonResponse
    {
        try {
            // Server side validataions
            $validator = Validator::make(
                $request->all(),
                [
                    "value" => "required|numeric",
                ]
            );
            
            // If post parameter have any missing parameter
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TENANT_SETTING_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }
            
            $setting = $this->tenantSettingRepository->updateSetting($request->toArray(), $settingId);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_TENANT_SETTING_UPDATE_SUCCESSFULLY');
            $apiData = ['tenant_setting_id' => $setting->tenant_setting_id];

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);

        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_SETTING_FOUND'),
                trans('messages.custom_error_message.ERROR_SETTING_FOUND')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

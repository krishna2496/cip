<?php

namespace App\Http\Controllers;

use App\Repositories\Currency\TenantAvailableCurrencyRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\RestExceptionHandlerTrait;
use DB;
use App\Events\ActivityLogEvent;
use Validator;
use App\Rules\DefaultCurrencyAvailable;
use Illuminate\Validation\Rule;
use App\Repositories\Tenant\TenantRepository;

//!  TenantCurrencyController controller
/*!
This controller is responsible for handling currency setting store/delete and show operations.
 */
class TenantCurrencyController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\Currency\TenantAvailableCurrencyRepository
     */
    private $tenantAvailableCurrencyRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenantRepository;

    /**
     * Create a new Tenant currency controller instance.
     *
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param App\Repositories\Currency\TenantAvailableCurrencyRepository $tenantAvailableCurrencyRepository
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        TenantAvailableCurrencyRepository $tenantAvailableCurrencyRepository,
        TenantRepository $tenantRepository
    ) {
        $this->responseHelper = $responseHelper;
        $this->tenantAvailableCurrencyRepository = $tenantAvailableCurrencyRepository;
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * List tenant’s currency
     *
     * @param Request $request
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function index(Request $request, int $tenantId): JsonResponse
    {
        try {
            $tenantCurrencyList = $this->tenantAvailableCurrencyRepository->getTenantCurrencyList($request, $tenantId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiData = $tenantCurrencyList;
            $apiMessage = (count($apiData) > 0)  ?
                trans('messages.success.MESSAGE_TENANT_CURRENCY_LISTING') :
                trans('messages.custom_error_message.ERROR_TENANT_CURRENCY_EMPTY_LIST');
                        
            return $this->responseHelper->successWithPagination($apiData, $apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }
    }

    /**
     * Store a newly created tenant currency into database
     *
     * @param \Illuminate\Http\Request $request
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function store(Request $request, int $tenantId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }

        $validator = Validator::make($request->toArray(), [
            'code' => [
                'required',
                'regex:/^[A-Z]{3}$/', 
                Rule::unique('tenant_currency')->where(function ($query) use ($tenantId, $request) {
                $query->where(['tenant_id' => $tenantId]);
            })],
            'default' => 'in:0,1',
            'is_active' => 'in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_CURRENCY_FIELD_REQUIRED'),
                $validator->errors()->first()
            );
        }

        if (!$this->tenantAvailableCurrencyRepository->isAvailableCurrency($request['code'])) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_CURRENCY_CODE_NOT_AVAILABLE'),
                trans('messages.custom_error_message.ERROR_CURRENCY_CODE_NOT_AVAILABLE')
            );
        }

        // Store tenant currency details
        $this->tenantAvailableCurrencyRepository->store($request, $tenantId);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_CURRENCY_ADDED');

        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.TENANT_CURRENCY'),
            config('constants.activity_log_actions.CREATED'),
            get_class($this),
            $request->toArray(),
            $tenantId
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Update tenant currency for tenant into database
     *
     * @param Request $request
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function update(Request $request, int $tenantId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }

        $validator = Validator::make($request->toArray(), [
            'code' => 'required|regex:/^[A-Z]{3}$/',
            'default' => 'in:0,1',
            'is_active' => 'in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_CURRENCY_FIELD_REQUIRED'),
                $validator->errors()->first()
            );
        }

        if (!$this->tenantAvailableCurrencyRepository->isAvailableCurrency($request['code'])) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_CURRENCY_CODE_NOT_AVAILABLE'),
                trans('messages.custom_error_message.ERROR_CURRENCY_CODE_NOT_AVAILABLE')
            );
        }

        try {
            $this->tenantAvailableCurrencyRepository->update($request, $tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.CURRENCY_CODE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_CURRENCY_CODE_NOT_FOUND')
            );
        }

        // Update tenant currency details
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_TENANT_CURRENCY_UPDATED');

        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.TENANT_CURRENCY'),
            config('constants.activity_log_actions.UPDATED'),
            get_class($this),
            $request->toArray(),
            $tenantId
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}

<?php

namespace App\Repositories\Tenant;

use App\Repositories\Tenant\TenantInterface;
use Illuminate\Http\{Request, Response};
use Validator, PDOException;
use App\Models\Tenant;
use App\Jobs\{TenantDefaultLanguageJob, TenantMigrationJob};
use App\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TenantRepository implements TenantInterface
{
    public $tenant;
	
	private $response;

    function __construct(Tenant $tenant, Response $response) {
		$this->tenant = $tenant;
		$this->response = $response;
    }


    public function tenantList(Request $request)
    {
        try {
			$tenantQuery = $this->tenant->with('options', 'tenantLanguages', 'tenantLanguages.language');
			
			if ($request->has('search')) {
				$tenantQuery->where('name', 'like', '%' . $request->input('search') . '%');
			}
			if ($request->has('order')) {
				$orderDirection = $request->input('order','asc');
				$tenantQuery->orderBy('tenant_id', $orderDirection);
			}
			
			$tenantList = $tenantQuery->paginate(config('constants.PER_PAGE_LIMIT'));
			$responseMessage = (count($tenantList) > 0) ? trans('messages.success.MESSAGE_TENANT_LISTING') : trans('messages.success.MESSAGE_NO_RECORD_FOUND');
			return ResponseHelper::successWithPagination($this->response->status(), $responseMessage, $tenantList);
			
		} catch(\InvalidArgumentException $e) {
			throw new \InvalidArgumentException($e->getMessage());
		}
    }
	
	public function store(Request $request)
    {
        try {
			$validator = Validator::make($request->toArray(), $this->tenant->rules);
			
			if ($validator->fails()) {
				return ResponseHelper::error(trans('messages.status_code.HTTP_STATUS_422'), 
										trans('messages.status_type.HTTP_STATUS_TYPE_422'), 
										trans('messages.custom_error_code.ERROR_10001'), 
										$validator->errors()->first());
			} 
			
			$tenant = $this->tenant->create($request->toArray());
			
			dispatch(new TenantDefaultLanguageJob($tenant));
			
			 // ONLY FOR TESTING START Create api_user data (PLEASE REMOVE THIS CODE IN PRODUCTION MODE)
            if(env('APP_ENV')=='local'){
                $apiUserData['api_key'] = base64_encode($tenant->name.'_api_key');
                $apiUserData['api_secret'] = base64_encode($tenant->name.'_api_secret');
                // Insert api_user data into table
                $tenant->apiUsers()->create($apiUserData);
            }
            // ONLY FOR TESTING END
            
            // Add options data into `tenant_has_option` table            
            if (isset($request->options) && count($request->options) > 0) {
				foreach ($request->options as $option_name => $option_value) {
					$tenantOptionData['option_name'] = $option_name;
                    $tenantOptionData['option_value'] = $option_value;
                    $tenant->options()->create($tenantOptionData);
                }
            }

            // Set response data
            $apiStatus = app('Illuminate\Http\Response')->status();
            $apiData = ['tenant_id' => $tenant->tenant_id];
            $apiMessage =  trans('messages.success.MESSAGE_TENANT_CREATED');

            // Job dispatched to create new tenant's database and migrations
            dispatch(new TenantMigrationJob($tenant));
			
			return ResponseHelper::success($apiStatus, $apiMessage, $apiData);
			
		} catch(PDOException $e) {
			
			throw new PDOException($e->getMessage());
			
		} catch(\Exception $e) {
			
			throw new \Exception($e->getMessage());
			
		}
    }

    public function find(int $id)
    {
        try {
			$tenantDetail = $this->tenant->findTenant($id);
			
			$apiStatus = app('Illuminate\Http\Response')->status();
			$apiData = $tenantDetail->toArray();
			$apiMessage =  trans('messages.success.MESSAGE_TENANT_FOUND');
			return ResponseHelper::success($apiStatus, $apiMessage, $apiData);
			
		} catch(ModelNotFoundException $e){
			
			throw new ModelNotFoundException(trans('messages.custom_error_message.10004'));
			
        } catch(\Exception $e) {
			
			throw new \Exception($e->getMessage());
			
		}	
    }


    public function delete(int $id)
    {
		try {
			$this->tenant->deleteTenant($id);
			// Set response data
			$apiStatus = app('Illuminate\Http\Response')->status();            
			$apiMessage = trans('messages.success.MESSAGE_TENANT_DELETED');

			return ResponseHelper::success($apiStatus, $apiMessage);
			
		} catch(ModelNotFoundException $e){
			
			throw new ModelNotFoundException(trans('messages.custom_error_message.10004'));
			
        }
    }
	
	public function update(Request $request, int $id)
    {
        try {
			
			$validator = Validator::make($request->toArray(), $this->tenant->rules);
			
			if ($validator->fails()) {
				return ResponseHelper::error(trans('messages.status_code.HTTP_STATUS_422'), 
										trans('messages.status_type.HTTP_STATUS_TYPE_422'), 
										trans('messages.custom_error_code.ERROR_10001'), 
										$validator->errors()->first());
			} 
			
			$tenant = Tenant::findOrFail($id);
			$tenant->update($request->toArray());
			
			// Add options data into `tenant_has_option` table            
            if (isset($request->options) && count($request->options) > 0) {
				foreach ($request->options as $option_name => $option_value) {
					$tenantOptionData['option_name'] = $option_name;
                    $tenantOptionData['option_value'] = $option_value;
                    $tenant->options()->where('option_name', $option_name)->update($tenantOptionData);
                }
            }
			$apiStatus = app('Illuminate\Http\Response')->status();
            $apiData = ['tenant_id' => $id];
			$apiMessage = trans('messages.success.MESSAGE_TENANT_UPDATED');
			
			return ResponseHelper::success($apiStatus, $apiMessage, $apiData);
			
		} catch(ModelNotFoundException $e){
			
			throw new ModelNotFoundException(trans('messages.custom_error_message.10004'));
			
        } catch(PDOException $e) {
			
			throw new PDOException($e->getMessage());
			
		}  catch(\Exception $e) {
			
			throw new \Exception($e->getMessage());
			
		}
    }
}
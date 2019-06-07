<?php

namespace App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Response;
use DB;

class Helpers
{
	/**
	* It will return 
	* @param Illuminate\Http\Request $request
	* @return string
	*/
    public static function getSubDomainFromRequest(Request $request)
    {
    	try{    	
        	return explode(".",parse_url($request->headers->all()['referer'][0])['host'])[0];
        } catch (\Exception $e) {
            if(env('APP_ENV')=='local')
                return env('DEFAULT_TENANT');
            else
        	   return $e->getMessage();
        }

    }

    /**
     * Prepare success response
     * 
     * @param int $apiStatus
     * @param string $apiMessage     
     * @param Model Object $apiData
     * @return mixed
     */
    public static function response($apiStatus = 200, $apiMessage = '', $apiData = '')
    {

        $response['status'] = $apiStatus;
        
        if(!empty((array)$apiData) && $apiData != '')
            $response['data'] = $apiData;

        // Check response data have pagination or not? Pagination response parameter sets
        if((is_object($apiData)) && ($apiData) && get_class($apiData) == "Illuminate\Pagination\LengthAwarePaginator"){            
            $response['data'] = $apiData->toArray()['data'];
            $response['pagination'] = [
                "total" => $apiData->total(),
                "per_page" => $apiData->perPage(),
                "current_page" => $apiData->currentPage(),
                "total_pages" => $apiData->lastPage(),
                "next_url" => $apiData->nextPageUrl()
            ];
        }
        if($apiMessage)
            $response['message'] = $apiMessage;
            
        return response()->json($response, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Prepare error response
     * 
     * @param int $status_code
     * @param string $status_type
     * @param int $custom_error_code
     * @param string $custom_error_message
     * @return mixed
     */ 
    public static function getRefererFromRequest(Request $request)
    {
    	try{
    		if(isset($request->headers->all()['referer'])){    	
        		$parseUrl = parse_url($request->headers->all()['referer'][0]);
        		return $parseUrl['scheme'].'://'.$parseUrl['host'].':'.$parseUrl['port'];
        	}else{
        		return env('APP_MAIL_BASE_URL');
        	}
        } catch (\Exception $e) {
        	return $e->getMessage();
        }
    }   
    

    /**
     * Prepare error response
     * 
	 * @param int $status_code
     * @param string $status_type
     * @param int $custom_error_code
     * @param string $custom_error_message
	 * @return mixed
     */
    public static function errorResponse($status_code, $status_type, $custom_error_code, $custom_error_message)
    {
       
        $response['status'] = $status_code;
        $response['type'] = $status_type;
        $response['code'] = $custom_error_code;
        $response['message'] = $custom_error_message;
        $data["errors"][] = $response;
       
        return response()->json($data, $status_code, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Sorting of multidimensional array
     * 
     * @param array $array
     * @param string $subfield
     * @param int $sort
     */
    public static function sortMultidimensionalArray(&$array, $subfield, $sort) {
        $sortarray = array();
        $arrayLength = count($array);
        $sortOrder = 1;
        if(!empty($array) && (isset($array))){

            foreach ($array as $key => $row) {
                
                if((!isset($row[$subfield]) || $row[$subfield] == '')){
                     $row[$subfield] = $array[$key][$subfield] = $arrayLength;
                     $arrayLength++;
                }

                $sortarray[$key] =  $row[$subfield] ;
            }

            array_multisort($sortarray, $sort, $array);

            foreach ($array as $key => $row) {
                
                $array[$key][$subfield] = $sortOrder;
                 $sortOrder++;
                 
            }
        }
    }

    /**
     * Get city name from city_id
     * 
     * @param int $city_id
     *
     * @return string
     */
    public static function getCityName($city_id)
    {
        $city = DB::table("city")->where("city_id", $city_id)->first();
        return $city->name;
    }

    /**
     * Get country name from country_id
     * 
     * @param int $country_id
     *
     * @return string
     */
    public static function getCountryName($country_id)
    {
        $country = DB::table("country")->where("country_id", $country_id)->first();
        return $country->name;
    }

    /**
     * Get timezone from timezone_id
     * 
     * @param int $timezone_id
     *
     * @return string
     */
    public static function getTimezone($timezone_id)
    {
        $timezone = DB::table("timezone")->where("timezone_id", $timezone_id)->first();
        return $timezone->timezone;
    }

    /**
     * Switch database connection runtime
     * 
     * @param int $connection
     * @param mixed $request
     *
     * @return string
     */
    public static function switchDatabaseConnection($connection, $request = '')
    {
        $domain = Self::getSubDomainFromRequest($request);
        // Set master connection         
        $pdo = DB::connection('mysql')->getPdo();
        Config::set('database.default', 'mysql');

        if($connection=="tenant"){
            // Set tenant connection 
            /*$tenant = DB::table('tenant')->where('name',$domain)->whereNull('deleted_at')->first();
            $this->createConnection($tenant);*/
            $pdo = DB::connection('tenant')->getPdo();
            Config::set('database.default', 'tenant');
        }
        return;
    }
    public function createConnection($tenant)
    {        
        Config::set('database.connections.tenant', array(
            'driver'    => 'mysql',
            'host'      => env('DB_HOST'),
            'database'  => 'ci_tenant_'.$tenant->tenant_id,
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
        ));        
        try {
            // Create connection for the tenant database
            $pdo = DB::connection('tenant')->getPdo();
            // Set default database
            Config::set('database.default', 'tenant');
        } catch (\PDOException $e) {
            if ($e instanceof \PDOException) {            
                return Helpers::errorResponse(trans('api_error_messages.status_code.HTTP_STATUS_403'), 
                                        trans('api_error_messages.status_type.HTTP_STATUS_TYPE_403'), 
                                        trans('api_error_messages.custom_error_code.ERROR_41000'), 
                                        trans('api_error_messages.custom_error_message.41000'));
            }
        }        
    }

}

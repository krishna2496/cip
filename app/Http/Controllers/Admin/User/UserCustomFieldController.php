<?php

namespace App\Http\Controllers\Admin\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\UserCustomField;
use App\Helpers\Helpers;
use Illuminate\Validation\Rule;
use Validator;

class UserCustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       //
    }

    /**
     * Store user custom field
     *
     * @param \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {       
        // Server side validataions
        $validator = Validator::make($request->toArray(), ["name" => "required", "type" => ['required', Rule::in(['Text', 'Email', 'Drop-down', 'radio'])], "is_mandatory" => "required", "translation" => "required" 
        ]);
        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'),
                                        config('errors.status_type.HTTP_STATUS_TYPE_422'),
                                        config('errors.custom_error_code.ERROR_20018'),
                                        $validator->errors()->first());
        }   
        $translation = $request->translation;                           
        if ((($request->type == 'Drop-down' ) || ($request->type == 'radio')) && ($translation['values'] == "")) {
            // Set response data if values are null for Drop-down and radio type
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'),
                                config('errors.status_type.HTTP_STATUS_TYPE_422'),
                                config('errors.custom_error_code.ERROR_20026'),
                                config('errors.custom_error_message.20026'));
        } 
        try {           
            // Set data for create new record
            $insert = array( 'name' => $request->name, 'type' => $request->type, 'is_mandatory' => $request->is_mandatory, 'translations' => serialize($translation));
            // Create new user custom field
            $insertData = UserCustomField::create($insert);
            // Set response data
            $apiStatus = app('Illuminate\Http\Response')->status();
            $apiMessage = config('messages.success_message.MESSAGE_CUSTOM_FIELD_ADD_SUCCESS');
            return Helpers::response($apiStatus, $apiMessage);
        
        } catch (\Exception $e) {
            // Any other error occured when trying to insert data into database.
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'), 
                                    config('errors.status_type.HTTP_STATUS_TYPE_422'), 
                                    config('errors.custom_error_code.ERROR_20004'), 
                                    config('errors.custom_error_message.20004'));
            
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update user custom field
     *
     * @param \Illuminate\Http\Request  $request
     * @param int  $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $fieldData = UserCustomField::find($id);
        if (!$fieldData) {
        	return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'),
                                        config('errors.status_type.HTTP_STATUS_TYPE_422'),
                                        config('errors.custom_error_code.ERROR_20018'),
                                        config('errors.custom_error_message.20032'));
        } 
        // Server side validataions
        $validator = Validator::make($request->toArray(), ["name" => "required", "type" => ['required', Rule::in(['Text', 'Email', 'Drop-down', 'radio'])], "is_mandatory" => "required", "translation" => "required" 
        ]);
        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'),
                                        config('errors.status_type.HTTP_STATUS_TYPE_422'),
                                        config('errors.custom_error_code.ERROR_20018'),
                                        $validator->errors()->first());
        } 
        $translation = $request->translation; 
        if ((($request->type == 'Drop-down' ) || ($request->type == 'radio')) && ($translation['values'] == "")) {
            // Set response data if values are null for Drop-down and radio
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'),
                                config('errors.status_type.HTTP_STATUS_TYPE_422'),
                                config('errors.custom_error_code.ERROR_20026'),
                                config('errors.custom_error_message.20026'));
        } 
        try {                             
            // Set data for update record
            $update = array('name' => $request->name, 'type' => $request->type, 'is_mandatory' => $request->is_mandatory, 'translations' => serialize($translation));
            // Update user custom field
            $updateData = UserCustomField::where('field_id', $id)->update($update);
            // Set response data
            $apiStatus = app('Illuminate\Http\Response')->status();
            $apiMessage = config('messages.success_message.MESSAGE_CUSTOM_FIELD_UPDATE_SUCCESS');
            return Helpers::response($apiStatus, $apiMessage);               
        } catch (\Exception $e) { 
            // Any other error occured when trying to update data into database.
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_422'), 
                                    config('errors.status_type.HTTP_STATUS_TYPE_422'), 
                                    config('errors.custom_error_code.ERROR_20004'), 
                                    config('errors.custom_error_message.20004'));
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int  $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userField = UserCustomField::findorFail($id);
            $userField->delete();

            // Set response data
            $apiStatus = app('Illuminate\Http\Response')->status();            
            $apiMessage = config('messages.success_message.MESSAGE_CUSTOM_FIELD_DELETE_SUCCESS');
            return Helpers::response($apiStatus, $apiMessage);
            
        } catch(\Exception $e){
            return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_403'), 
                                        config('errors.status_type.HTTP_STATUS_TYPE_403'), 
                                        config('errors.custom_error_code.ERROR_20028'), 
                                        config('errors.custom_error_message.20028'));

        }
    }

    /**
     * Handle error if id is not passed in url
     *
     * @return mixed
     */
    public function handleError()
    {
        return Helpers::errorResponse(config('errors.status_code.HTTP_STATUS_400'), 
                                        config('errors.status_type.HTTP_STATUS_TYPE_400'), 
                                        config('errors.custom_error_code.ERROR_20034'), 
                                        config('errors.custom_error_message.20034'));
    }
}

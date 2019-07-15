<?php

return [
    /**
    * API success messages
    */
    'success' => [
        'MESSAGE_USER_FOUND' => 'DE: User found successfully',
        'MESSAGE_NO_DATA_FOUND' => 'DE: No Data Found',
        'MESSAGE_USER_CREATED' => 'DE: User created successfully',
        'MESSAGE_USER_DELETED' => 'DE: User deleted successfully',
        'MESSAGE_FOOTER_PAGE_CREATED' => 'DE: Page created successfully',
        'MESSAGE_FOOTER_PAGE_UPDATED' => 'DE: Page updated successfully',
        'MESSAGE_FOOTER_PAGE_DELETED' => 'DE: Page deleted successfully',
        'MESSAGE_FOOTER_PAGE_LISTING' => 'DE: Footer pages listing successfully.',
        'MESSAGE_USER_UPDATED' => 'DE: User updated successfully',
        'MESSAGE_NO_RECORD_FOUND' => 'DE: No records found',
        'MESSAGE_USER_LISTING' => 'DE: User listing successfully',
        'MESSAGE_USER_SKILLS_CREATED' => 'DE: User skills linked successfully',
        'MESSAGE_USER_SKILLS_DELETED' => 'DE: User skills unlinked successfully',
        'MESSAGE_SLIDER_ADD_SUCCESS' => 'DE: Slider image added successfully',
        'MESSAGE_USER_LOGGED_IN' => 'DE: You are successfully logged in',
        'MESSAGE_PASSWORD_RESET_LINK_SEND_SUCCESS' => 'DE: Reset Password link is sent to your email account,link will be expire in ' . config('constants.FORGOT_PASSWORD_EXPIRY_TIME') . ' hours',
        'MESSAGE_PASSWORD_CHANGE_SUCCESS' => 'DE: Your password has been changed successfully.',
        'MESSAGE_CUSTOM_FIELD_ADDED' => 'DE: User custom field added successfully',
        'MESSAGE_CUSTOM_FIELD_UPDATED' => 'DE: User custom field updated successfully',
        'MESSAGE_CUSTOM_FIELD_DELETED' => 'DE: User custom field deleted successfully',
        'MESSAGE_APPLICATION_LISTING' => 'DE: Mission application listing successfully',
        'MESSAGE_APPLICATION_UPDATED' => 'DE: Mission application updated successfully',
        'MESSAGE_CUSTOM_STYLE_UPLOADED_SUCCESS' => 'DE: Custom styling data uploaded successfully',
        'MESSAGE_CUSTOM_STYLE_RESET_SUCCESS' => 'DE: Custom styling reset successfully',
        'MESSAGE_CUSTOM_FIELD_LISTING' => 'DE: User custom field listing successfully',
        'MESSAGE_MISSION_ADDED' => 'DE: Mission created successfully',
        'MESSAGE_MISSION_UPDATED' => 'DE: Mission updated successfully',
        'MESSAGE_MISSION_DELETED' => 'DE: Mission deleted successfully',
        'MESSAGE_MISSION_LISTING' => 'DE: Mission listing successfully',
        'MESSAGE_SKILL_LISTING' => 'DE: Skill listing successfully',
        'MESSAGE_THEME_LISTING' => 'DE: Mission theme listing successfully',
        'MESSAGE_CITY_LISTING' => 'DE: City listing successfully',      
        'MESSAGE_MISSION_ADDED_TO_FAVOURITE' => 'DE: Mission added to favourite',
        'MESSAGE_MISSION_DELETED_FROM_FAVOURITE' => 'DE: Mission removed from favourite',
        'MESSAGE_COUNTRY_LISTING' => 'DE: Country listing successfully',
        'MESSAGE_MISSION_FOUND' => 'DE: Mission found successfully',
        'MESSAGE_PAGE_FOUND' => 'DE: Page found successfully',
        'MESSAGE_ASSETS_FILES_LISTING' => 'DE: Assets files listing successfully',
        'MESSAGE_TENANT_SETTING_UPDATE_SUCCESSFULLY' => 'DE: Settings has been update successfully',
        'MESSAGE_TENANT_SETTINGS_LISTING' => 'DE: Settings listing successfully',
		'MESSAGE_THEME_CREATED' => 'DE: Mission theme created successfully',
        'MESSAGE_THEME_UPDATED' => 'DE: Mission theme updated successfully',
        'MESSAGE_THEME_DELETED' => 'DE: Mission theme deleted successfully',
        'MESSAGE_THEME_FOUND' => 'DE: Mission theme found successfully',
        'MESSAGE_SKILL_CREATED' => 'DE: Skill created successfully',
        'MESSAGE_SKILL_UPDATED' => 'DE: Skill updated successfully',
        'MESSAGE_SKILL_DELETED' => 'DE: Skill deleted successfully',
        'MESSAGE_SKILL_FOUND' => 'DE: Skill found successfully',
        'MESSAGE_THEME_FOUND' => 'DE: Mission theme found successfully',
        'MESSAGE_TENANT_OPTION_CREATED' => 'DE: Tenant option created successfully',
        'MESSAGE_TENANT_OPTION_UPDATED' => 'DE: Tenant option update successfully',
        'MESSAGE_TENANT_OPTIONS_LIST' => 'DE: Tenant options listing successfully',
        'MESSAGE_MISSION_RATING_LISTING' => 'DE: Get mission rating successfully',
        'MESSAGE_TENANT_OPTION_FOUND' => 'DE: Tenant option found', 
        'MESSAGE_INVITED_FOR_MISSION' => 'DE: User invited to mission successfully',
    ],

        
    /**
    * API Error Codes and Message
    */
    'custom_error_message' => [
        // Custom error code for User Module - 100000 - 109999
        'ERROR_USER_NOT_FOUND' => 'DE: User does not found in system',
        'ERROR_SKILL_INVALID_DATA' => 'DE: Invalid skill data',
        'ERROR_USER_CUSTOM_FIELD_INVALID_DATA' => 'DE: Custom field creation failed. Please check input parameters',
        'ERROR_USER_CUSTOM_FIELD_NOT_FOUND' => 'DE: Requested user custom field does not exist',
        'ERROR_USER_INVALID_DATA' => 'DE: User creation failed. Please check input parameters',
        'ERROR_USER_SKILL_NOT_FOUND' => 'DE: Requested skills for user does not exist',
        'ERROR_SLIDER_IMAGE_UPLOAD' => 'DE: Unable to upload slider image',
        'ERROR_SLIDER_INVALID_DATA' => 'DE: Invalid input data',
        'ERROR_SLIDER_LIMIT' => 'DE: Sorry, you cannot add more than '.config('constants.SLIDER_LIMIT').' slides!',
        'ERROR_NOT_VALID_EXTENSION' => 'DE: File must have .scss type',
        'ERROR_FILE_NAME_NOT_MATCHED_WITH_STRUCTURE' => 'DE: File name doesn`t match with structure',
        
        // Custom error code for CMS Module - 300000 - 309999
        'ERROR_INVALID_ARGUMENT' => 'DE: Invalid argument',
        'ERROR_FOOTER_PAGE_NOT_FOUND' => 'DE: Footer page not found in the system',
        'ERROR_DATABASE_OPERATIONAL' => 'DE: Database operational error',
        'ERROR_NO_DATA_FOUND' => 'DE: No data found',

        // Custom error code for Mission Module - 400000 - 409999
        'ERROR_INVALID_MISSION_APPLICATION_DATA' => 'DE: Invalid application data or missing parameter',
        'ERROR_INVALID_MISSION_DATA' => 'DE: Invalid mission data or missing parameter',
        'ERROR_MISSION_NOT_FOUND' => 'DE: Requested mission does not exist',
        'ERROR_MISSION_DELETION' => 'DE: Mission deletion failed',
        'ERROR_MISSION_REQUIRED_FIELDS_EMPTY' => 'DE: Mission creation failed. Please check input parameters',
        'ERROR_NO_MISSION_FOUND' => 'DE: Mission does not found in system',
        'ERROR_THEME_INVALID_DATA' => 'DE: Mission theme creation failed. Please check input parameters',
        'ERROR_THEME_NOT_FOUND' => 'DE: Mission Theme does not found in system',
        'ERROR_SKILL_NOT_FOUND' => 'DE: Skill does not found in system',
        'ERROR_INVALID_MISSION_ID' => 'DE: Invalid mission id',
        'ERROR_INVALID_INVITE_MISSION_DATA' => 'DE: Invalid invite mission detail',
        'ERROR_INVITE_MISSION_ALREADY_EXIST' => 'DE: User is already invited for this mission',
        
        // Custom error code for Tenant Authorization - 210000 - 219999
        'ERROR_INVALID_API_AND_SECRET_KEY' => 'DE: Invalid API Key or Secret key',
        'ERROR_API_AND_SECRET_KEY_REQUIRED' => 'DE: API key and Secret key are required',
        'ERROR_EMAIL_NOT_EXIST' => 'DE: Email address does not exist in the system',
        'ERROR_INVALID_RESET_PASSWORD_LINK' => 'DE: Reset password link is expired or invalid',
        'ERROR_RESET_PASSWORD_INVALID_DATA' => 'DE: Invalid input data',
        'ERROR_SEND_RESET_PASSWORD_LINK' => 'DE: Something went wrong while sending reset password link',
        'ERROR_INVALID_DETAIL' => 'DE: Invalid reset password token or email address',
        'ERROR_INVALID_PASSWORD' => 'DE: Invalid password',
        'ERROR_TENANT_DOMAIN_NOT_FOUND' => 'DE: Tenant domain does not found',
        'ERROR_TOKEN_EXPIRED' => 'DE: Provided token is expired',
        'ERROR_IN_TOKEN_DECODE' => 'DE: An error while decoding token',
        'ERROR_TOKEN_NOT_PROVIDED' => 'DE: Token not provided',
        

        // Custom error code for common exception
        'ERROR_OCCURRED' => 'DE: An error has occurred',
        'ERROR_INVALID_JSON' => 'DE: Invalid Json format',
        
        // Custom erro code for other errors - 800000 - 809999
        'ERROR_ON_UPDATING_STYLING_VARIBLE_IN_DATABASE' => 'DE: An error has occured, while updating colors in database',
        'ERROR_WHILE_DOWNLOADING_FILES_FROM_S3_TO_LOCAL' => 'DE: File is failed to download from S3 to local',
        'ERROR_WHILE_COMPILING_SCSS_FILES' => 'DE: An error has occured, while compiling SCSS files to update SCSS changes',
        'ERROR_WHILE_STORE_COMPILED_CSS_FILE_TO_LOCAL' => 'DE: An error has occured, while storing compiled css file to local storage',
        'ERROR_NO_FILES_FOUND_TO_UPLOAD_ON_S3_BUCKET' => 'DE: No files found to upload on s3 bucket',
        'ERROR_FAILD_TO_UPLOAD_COMPILE_FILE_ON_S3' => 'DE: Failed to upload files on S3',
        'ERROR_FAILED_TO_RESET_STYLING' => 'DE: Failed to reset styling settings',
        'ERROR_DEFAULT_THEME_FOLDER_NOT_FOUND' => 'DE: Default theme folder not found on server',
        'ERROR_NO_FILES_FOUND_TO_DOWNLOAD' => 'DE: No assets file found on S3 for tenant',
        'ERROR_TENANT_ASSET_FOLDER_NOT_FOUND_ON_S3' => 'DE: Tenant asset folder not found',
        'ERROR_NO_FILES_FOUND_IN_ASSETS_FOLDER' => 'DE: No files found on S3 assets folder for this tenant',
        'ERROR_BOOSTRAP_SCSS_NOT_FOUND' => 'DE: Boostrap SCSS file not found while compiling SCSS files',
        'ERROR_SETTING_FOUND' => 'DE: Setting not found',
        'ERROR_IMAGE_FILE_NOT_FOUND_ON_S3' => 'DE: Image file not found on S3 server',
        'ERROR_WHILE_UPLOADING_IMAGE_ON_S3' => 'DE: An error while uploading image on S3',
        'ERROR_DOWNLOADING_IMAGE_TO_LOCAL' => 'DE: An error while downloading image from S3 to server',
        'ERROR_IMAGE_UPLOAD_INVALID_DATA' => 'DE: Invalid input file',
        'ERROR_TENANT_OPTION_NOT_FOUND' => 'DE: No tenant option found'
    ],

    /**
    * API custom text
    */
    'custom_text' => [
        'HAS_RECOMMENDED_A_MISSION_TO_YOU' => 'DE: has recommended a mission to you',
        'MISSION' => 'DE: MISSION :',
        'ALL_RIGHTS_RESERVED' => 'DE: All Rights Reserved.'
    ]
];

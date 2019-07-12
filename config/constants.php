<?php

return [
    
    /*
     * constants to use any where in system
     */
    'TENANT_OPTION_SLIDER' => 'slider',
    'FORGOT_PASSWORD_EXPIRY_TIME' => '4',
    'SLIDER_LIMIT' => '4',
    'SLIDER_IMAGE_PATH' => 'images/',
    'ACTIVE' => 1,
    'DB_DATE_FORMAT' => 'Y-m-d H:i:s',
    'PER_PAGE_LIMIT' => '9',
    'FRONT_DATE_FORMAT' => 'd/m/Y',
    
    /*
     * User custom field types
     */
     'custom_field_types' => [
        'TEXT' => 'text',
        'EMAIL' => 'email',
        'DROP-DOWN' => 'drop-down',
        'RADIO' => 'radio'
     ],
     
     /*
      * Language constants
      */
     
    'DEFAULT_LANGUAGE' => 'EN',
    'FRONTEND_LANGUAGE_FOLDER' => 'front_end',

    
    /*
     * Mission types
     */
    'mission_type' => [
        'TIME' => 'TIME',
        'GOAL' => 'GOAL'
    ],

    /*
     * Publication status
     */
    'publication_status' => [
        'DRAFT' => 'DRAFT',
        'PENDING_APPROVAL' => 'PENDING_APPROVAL',
        'REFUSED' => 'REFUSED',
        'APPROVED' => 'APPROVED',
        'PUBLISHED_FOR_VOTING' => 'PUBLISHED_FOR_VOTING',
        'PUBLISHED_FOR_APPLYING' => 'PUBLISHED_FOR_APPLYING',
        'UNPUBLISHED' => 'UNPUBLISHED'
    ],

    /*
     * Image types
     */
    'image_types' => [
        'JPG' => 'jpg',
        'JPEG' => 'jpeg',
        'PNG' => 'png'
    ],

    /*
     * Document types
     */
    'document_types' => [
        'DOC' => 'doc',
        'DOCX' => 'docx',
        'XLS' => 'xls',
        'XLSX' => 'xlsx',
        'PDF' => 'pdf',
        'TXT' => 'txt'
    ],

    /*
     * Application status
     */
    'application_status' => [
        'AUTOMATICALLY_APPROVED' => 'AUTOMATICALLY_APPROVED',
        'PENDING' => 'PENDING',
        'REFUSED' => 'REFUSED'
    ],

    /*
     * User notification types
     */
    'notification_types' => [
        'RECOMMENDED-MISSIONS' => '1',
        'VOLUNTEERING-HOURS' => '2',
        'VOLUNTEERING-GOALS' => '3',
        'MY-COMMENTS' => '4',
        'MY-STORIES' => '5',
        'NEW-STORIES-HOURS' => '6',
        'NEW-MISSIONS' => '7',
        'NEW-MESSAGES' => '8'
    ],
     
    'TOP_THEME' => "top_themes",
    'TOP_COUNTRY' => "top_countries",
    'TOP_ORGANISATION' => "top_organization",
    'TOP_ORGANISATION' => "top_organization",
    'MOST_RANKED' => "most_ranked_missions",
    'TOP_FAVOURITE' => "favourite_missions",
    'TOP_RECOMMENDED' => "recommended_missions",
    'THEME' => "themes",
    'COUNTRY' => "country",
    'CITY' => "city",
    'SKILL' => "skill",
    'RANDOM' => 'random_missions',

    'ORGANIZATION' => "organization",
    'EXPLORE_MISSION_LIMIT' => "5",

    'error_codes' => [
        'ERROR_FOOTER_PAGE_REQUIRED_FIELDS_EMPTY' => '300000',
        'ERROR_INVALID_ARGUMENT' => '300002',
        'ERROR_FOOTER_PAGE_NOT_FOUND' => '300003',
        'ERROR_DATABASE_OPERATIONAL' => '300004',
        'ERROR_NO_DATA_FOUND' => '300005',
        'ERROR_USER_NOT_FOUND' => '100000',
        'ERROR_SKILL_INVALID_DATA' => '100002',
        'ERROR_USER_CUSTOM_FIELD_INVALID_DATA' => '100003',
        'ERROR_USER_CUSTOM_FIELD_NOT_FOUND' => '100004',
        'ERROR_USER_INVALID_DATA' => '100010',
        'ERROR_USER_SKILL_NOT_FOUND' => '100011',
        'ERROR_SLIDER_IMAGE_UPLOAD' => '100012',
        'ERROR_SLIDER_INVALID_DATA' => '100013',
        'ERROR_SLIDER_LIMIT' => '100014',
        'ERROR_NOT_VALID_EXTENSION' => '100015',
        'ERROR_FILE_NAME_NOT_MATCHED_WITH_STRUCTURE' => '100016',
        'ERROR_INVALID_API_AND_SECRET_KEY' => '210000',
        'ERROR_API_AND_SECRET_KEY_REQUIRED' => '210001',
        'ERROR_EMAIL_NOT_EXIST' => '210002',
        'ERROR_INVALID_RESET_PASSWORD_LINK' => '210003',
        'ERROR_RESET_PASSWORD_INVALID_DATA' => '210004',
        'ERROR_SEND_RESET_PASSWORD_LINK' => '210005',
        'ERROR_INVALID_DETAIL' => '210006',
        'ERROR_INVALID_PASSWORD' => '210007',
        'ERROR_TENANT_DOMAIN_NOT_FOUND' => '210008',
        'ERROR_TOKEN_EXPIRED' => '210009',
        'ERROR_IN_TOKEN_DECODE' => '210010',
        'ERROR_TOKEN_NOT_PROVIDED' => '210012',
        'ERROR_INVALID_MISSION_APPLICATION_DATA' => '400000',
        'ERROR_INVALID_MISSION_DATA' => '400001',
        'ERROR_MISSION_NOT_FOUND' => '400003',
        'ERROR_MISSION_DELETION' => '400004',
        'ERROR_MISSION_REQUIRED_FIELDS_EMPTY' => '400006',
        'ERROR_NO_MISSION_FOUND' => '400007',
        'ERROR_THEME_INVALID_DATA' => '400008',
        'ERROR_THEME_NOT_FOUND' => '400009',
        'ERROR_NO_SKILL_FOUND' => '400010',
        'ERROR_SKILL_DELETION' => '400011',
        'ERROR_SKILL_REQUIRED_FIELDS_EMPTY' => '400012',
        'ERROR_SKILL_NOT_FOUND' => '400014',
        'ERROR_PARENT_SKILL_NOT_FOUND' => '400015',
        'ERROR_INVALID_MISSION_ID' => '400018',
        'ERROR_INVALID_INVITE_MISSION_DATA' => '400019',
        'ERROR_INVITE_MISSION_ALREADY_EXIST' => '400020',
        
        'ERROR_OCCURRED' => '999999',
        'ERROR_INVALID_JSON' => '900000',

        'ERROR_ON_UPDATING_STYLING_VARIBLE_IN_DATABASE' => '800000',
        'ERROR_WHILE_DOWNLOADING_FILES_FROM_S3_TO_LOCAL' => '800001',
        'ERROR_WHILE_COMPILING_SCSS_FILES' => '800002',
        'ERROR_WHILE_STORE_COMPILED_CSS_FILE_TO_LOCAL' => '800003',
        'ERROR_NO_FILES_FOUND_TO_UPLOAD_ON_S3_BUCKET' => '800004',
        'ERROR_FAILD_TO_UPLOAD_COMPILE_FILE_ON_S3' => '800005',
        'ERROR_FAILED_TO_RESET_STYLING' => '800006',
        'ERROR_DEFAULT_THEME_FOLDER_NOT_FOUND' => '800007',
        'ERROR_NO_FILES_FOUND_TO_DOWNLOAD' => '800008',
        'ERROR_TENANT_ASSET_FOLDER_NOT_FOUND_ON_S3' => '800009',
        'ERROR_NO_FILES_FOUND_IN_ASSETS_FOLDER' => '800010',
        'ERROR_BOOSTRAP_SCSS_NOT_FOUND' => '800011',
        'ERROR_TENANT_SETTING_REQUIRED_FIELDS_EMPTY' => '800012',
        'ERROR_SETTING_FOUND' => '800013',
        'ERROR_IMAGE_FILE_NOT_FOUND_ON_S3' => '800014',
        'ERROR_WHILE_UPLOADING_IMAGE_ON_S3' => '800015',
        'ERROR_DOWNLOADING_IMAGE_TO_LOCAL' => '800016',
        'ERROR_IMAGE_UPLOAD_INVALID_DATA' => '800017',
        'ERROR_TENANT_OPTION_REQUIRED_FIELDS_EMPTY' => '800018',
        'ERROR_TENANT_OPTION_NOT_FOUND' => '800019'
        
    ]
    
];

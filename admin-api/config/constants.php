<?php
    return [
        'PER_PAGE_LIMIT' => '10',
        'PER_PAGE_MAX' => '50',
        'error_codes' => [
            'ERROR_TENANT_REQUIRED_FIELDS_EMPTY' => '200001',
            'ERROR_TENANT_ALREADY_EXIST' => '200002',
            'ERROR_TENANT_NOT_FOUND' => '200003',
            'ERROR_DATABASE_OPERATIONAL' => '200004',
            'ERROR_NO_DATA_FOUND' => '200005',
            'ERROR_INVALID_ARGUMENT' => '200006',
            'ERROR_API_USER_NOT_FOUND' => '200009',
            'ERROR_INVALID_JSON' => '900000',
            'ERROR_LANGUAGE_NOT_FOUND' => '200021',
            'ERROR_LANGUAGE_REQUIRED_FIELDS_EMPTY' => '200022',
            'ERROR_TENANT_LANGUAGE_REQUIRED_FIELDS_EMPTY' => '200101',
            'ERROR_TENANT_LANGUAGE_NOT_FOUND' => '200103',
            'ERROR_TENANT_DEFAULT_LANGUAGE_REQUIRED' => '200104',
            'ERROR_MIGRATION_CHANGES_FILE_FIELDS_EMPTY' => '200105',
            'ERROR_ACTIVITY_LOG_REQUIRED_FIELDS_EMPTY' => '200106',
            'ERROR_NOT_VALID_EXTENSION' => '200107',
            'ERROR_DELETE_DEFAULT_TENANT_LANGUAGE' => '200108',

        ],
        'background_process_status' => [
            'PENDING' => '0',
            'COMPLETED' => '1',
            'IN_PROGRESS' => '2',
            'FAILED' => '-1'
        ],
        'AWS_S3_BUCKET_NAME' => 'optimy-dev-tatvasoft',
        'AWS_S3_DEFAULT_THEME_FOLDER_NAME' => 'default_theme',
        'AWS_S3_ASSETS_FOLDER_NAME' => 'assets',
        'AWS_S3_IMAGES_FOLDER_NAME' => 'images',
        'AWS_S3_SCSS_FOLDER_NAME' => 'scss',
        'AWS_S3_LOGO_IMAGE_NAME' => 'logo.png',
        'EMAIL_TEMPLATE_FOLDER' => 'emails',
        'EMAIL_TEMPLATE_JOB_NOTIFICATION' => 'tenant-notification',
        'activity_log_types' => [
            'TENANT' => 'TENANT',
            'API_USER' => 'API_USER',
            'API_USER_KEY_RENEW' => 'API_USER_KEY_RENEW',
            'TENANT_SETTINGS' => 'TENANT_SETTINGS',
            'LANGUAGE' => 'LANGUAGE',
            'TENANT_LANGUAGE' => 'TENANT_LANGUAGE',
        ],
        'activity_log_actions' => [
            'CREATED' => 'CREATED',
            'UPDATED' => 'UPDATED',
            'DELETED' => 'DELETED',
            'ENABLED' => 'ENABLED',
            'DISABLED' => 'DISABLED'
        ],
        'EMAIL_TESTING_TEMPLATE' => 'test-email',
        'ADMIN_EMAIL_ADDRESS' => 'vfauvarque@optimy.com',
        'language_status' => [
            'ACTIVE' => '1',
            'INACTIVE' => '0'
        ],
        'migration_file_type' => [
            'migration' => 'migration',
            'seeder' => 'seeder'
        ],
        'EMAIL_TEMPLATE_MIGRATION_NOTIFICATION' => 'migration-notification',
    ];
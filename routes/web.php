<?php

/*
|--------------------------------------------------------------------------
| Tenants Routes for super admin
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
/* Route to run background process for tenant. To perform SCSS and assets operations */
$router->get('/tenant/runBackgroundProcess/{tenantId}', 'TenantBackgroundProcessController@runBackgroundProcess');
$router->get('/tenant/runBackgroundProcess', 'TenantBackgroundProcessController@runBackgroundProcess');

$router->group(
    ['prefix' => 'tenants', 'middleware' => 'localization'],
    function ($router) {
        // Get tenants list
        $router->get('/', ['as' => 'tenants', 'middleware' => 'PaginationMiddleware',
        'uses'=>'TenantController@index']);
        // Get tenant details from id
        $router->get('/{tenant_id:[0-9]+}', ['as' => 'tenants.detail', 'uses'=>'TenantController@show']);
        // Create new tenant
        $router->post('/', ['as' => 'tenants.store', 'middleware' => 'JsonApiMiddleware',
        'uses'=>'TenantController@store']);
        // Update tenant details
        $router->patch('/{tenant_id}', ['as' => 'tenants.update',
        'middleware' => 'JsonApiMiddleware', 'uses'=>'TenantController@update']);
        // Delete tenant
        $router->delete('/{tenant_id}', ['as' => 'tenants.destroy', 'uses'=>'TenantController@destroy']);
        // Get tenant has setting detail
        $router->get('/{tenantId}/settings', ['as' => 'tenants.settings',
        'uses'=>'TenantHasSettingController@show']);
        // Store settings
        $router->post('/{tenantId}/settings', ['as' => 'tenants.store.settings',
        'uses'=>'TenantHasSettingController@store']);
        
        // Get api user list
        $router->get(
            '/{tenant_id}/api_users',
            ['as' => 'tenants.api-users',
            'uses' => 'ApiUserController@getAllApiUser']
        );
        // Get api user detail from id
        $router->get(
            '/{tenant_id}/api_users/{api_user_id}',
            ['as' => 'tenants.get-api-user',
            'uses' => 'ApiUserController@getApiUserDetail']
        );
        // create api user
        $router->post(
            '/{tenant_id}/api_users',
            ['as' => 'tenants.create-api-user',
            'uses' => 'ApiUserController@createApiUser']
        );
        // Regenarate api keys
        $router->patch(
            '/{tenant_id}/api_users/{api_user_id}',
            ['as' => 'tenants.renew-api-user',
            'uses' => 'ApiUserController@renewApiUser']
        );
        // Delete api user
        $router->delete(
            '/{tenant_id}/api_users/{api_user_id}',
            ['as' => 'tenants.delete-api-user',
            'uses' => 'ApiUserController@deleteApiUser']
        );
        // Get language detail
        $router->get(
            '/language/{languageId}',
            ['as' => 'language.show',
            'uses' => 'LanguageController@show']
        );
        // Get language lists
        $router->get(
            '/language',
            ['as' => 'language.get-language-lists', 'middleware' => ['PaginationMiddleware'],
            'uses' => 'LanguageController@index']
        );
        // Delete language details
        $router->delete(
            '/language/{languageId}',
            ['as' => 'language.delete-language',
            'uses' => 'LanguageController@destroy']
        );
        // Store language details
        $router->post(
            '/language',
            ['as' => 'language.store-language',
            'uses' => 'LanguageController@store']
        );
        // Update language details
        $router->patch(
            '/language/{languageId}',
            ['as' => 'language.update-language',
            'uses' => 'LanguageController@update']
        );
    }
);

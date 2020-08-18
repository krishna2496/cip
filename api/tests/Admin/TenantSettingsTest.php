<?php

use App\Helpers\Helpers;

class TenantSettingsTest extends TestCase
{
    /**
     * @test
     *
     * Get all tenant settings
     *
     * @return void
     */
    public function tenant_settings_it_should_return_all_tenant_settings()
    {
        DB::setDefaultConnection('mysql');
        $emailNotificationInviteColleague = config('constants.tenant_settings.EMAIL_NOTIFICATION_INVITE_COLLEAGUE');
        $settings = DB::select("SELECT * FROM tenant_setting as t WHERE t.key='$emailNotificationInviteColleague'");
        DB::setDefaultConnection('tenant');
        $tenantSetting = App\Models\TenantSetting::create(['setting_id' => $settings[0]->tenant_setting_id]);
        $tenantActivatedSetting = App\Models\TenantActivatedSetting::create(['tenant_setting_id' => $tenantSetting->tenant_setting_id]);

        DB::setDefaultConnection('mysql');

        $this->get('tenant-settings', ['Authorization' => Helpers::getBasicAuth()])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "message"
          ]);
        $tenantSetting->delete();
        $tenantActivatedSetting->delete();
    }

    /**
     * @test
     *
     * Update tenant setting
     *
     * @return void
     */
    public function tenant_settings_it_should_update_tenant_settings()
    {
        DB::setDefaultConnection('mysql');
        $emailNotificationInviteColleague = config('constants.tenant_settings.EMAIL_NOTIFICATION_INVITE_COLLEAGUE');
        $settings = DB::select("SELECT * FROM tenant_setting as t WHERE t.key='$emailNotificationInviteColleague'");
        DB::setDefaultConnection('tenant');
        $tenantSetting = App\Models\TenantSetting::create(['setting_id' => $settings[0]->tenant_setting_id]);
        
        DB::setDefaultConnection('tenant');
        $settingId = \App\Models\TenantSetting::get()->random()->tenant_setting_id;
        DB::setDefaultConnection('mysql');

        $params = [
                    "value" => "1"
                ];

        $this->patch("tenant-settings/" . $settingId, $params, ['Authorization' => Helpers::getBasicAuth()])
        ->seeStatusCode(200)
        ->seeJsonStructure([
            'message',
            'status'
        ]);
        $tenantSetting->delete();
    }

    /**
     * @test
     *
     * Update tenant setting return error if user enter wrong value
     *
     * @return void
     */
    public function tenant_settings_it_should_return_error_if_user_enter_wrong_value()
    {
        DB::setDefaultConnection('mysql');
        $emailNotificationInviteColleague = config('constants.tenant_settings.EMAIL_NOTIFICATION_INVITE_COLLEAGUE');
        $settings = DB::select("SELECT * FROM tenant_setting as t WHERE t.key='$emailNotificationInviteColleague'");
        DB::setDefaultConnection('tenant');
        $tenantSetting = App\Models\TenantSetting::create(['setting_id' => $settings[0]->tenant_setting_id]);

        DB::setDefaultConnection('tenant');
        $settingId = \App\Models\TenantSetting::get()->random()->tenant_setting_id;
        DB::setDefaultConnection('mysql');

        $params = [
                    "value" => "123456"
                ];

        $this->patch("tenant-settings/" . $settingId, $params, ['Authorization' => Helpers::getBasicAuth()])
        ->seeStatusCode(422)
        ->seeJsonStructure([
            'errors' => [
                [
                    'status',
                    'type',
                    'code',
                    'message'
                ]
            ]
        ]);
        $tenantSetting->delete();
    }

    /**
     * @test
     *
     * Update tenant setting return error if user enter invalid setting id
     *
     * @return void
     */
    public function tenant_settings_it_should_return_error_if_user_enter_invalid_setting_id()
    {
        $settingId = rand(100000, 500000);
        $params = [
                    "value" => "1"
                ];

        $this->patch("tenant-settings/" . $settingId, $params, ['Authorization' => Helpers::getBasicAuth()])
        ->seeStatusCode(404)
        ->seeJsonStructure([
            'errors' => [
                [
                    'status',
                    'type',
                    'code',
                    'message'
                ]
            ]
        ]);
    }

    /**
     * @test
     *
     * Update tenant setting return error if user enter blank value
     *
     * @return void
     */
    public function tenant_settings_it_should_return_error_if_user_enter_blank_value()
    {
        DB::setDefaultConnection('mysql');
        $emailNotificationInviteColleague = config('constants.tenant_settings.EMAIL_NOTIFICATION_INVITE_COLLEAGUE');
        $settings = DB::select("SELECT * FROM tenant_setting as t WHERE t.key='$emailNotificationInviteColleague'");
        DB::setDefaultConnection('tenant');
        $tenantSetting = App\Models\TenantSetting::create(['setting_id' => $settings[0]->tenant_setting_id]);

        DB::setDefaultConnection('tenant');
        $settingId = \App\Models\TenantSetting::get()->random()->tenant_setting_id;
        DB::setDefaultConnection('mysql');

        $params = [
                    "value" => ""
                ];

        $this->patch("tenant-settings/" . $settingId, $params, ['Authorization' => Helpers::getBasicAuth()])
        ->seeStatusCode(422)
        ->seeJsonStructure([
            'errors' => [
                [
                    'status',
                    'type',
                    'code',
                    'message'
                ]
            ]
        ]);
        $tenantSetting->delete();
    }

    /**
     * @test
     *
     * It should get empty data
     *
     * @return void
     */
    public function tenant_settings_it_should_return_no_tenant_settings_found()
    {
        DB::setDefaultConnection('tenant');

        \App\Models\TenantSetting::whereNull('deleted_at')->delete();

        DB::setDefaultConnection('mysql');
        $this->get('tenant-settings', ['Authorization' => Helpers::getBasicAuth()])
        ->seeStatusCode(200);
        
        \App\Models\TenantSetting::whereNotNull('deleted_at')->restore();
    }
}

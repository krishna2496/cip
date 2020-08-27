<?php

// require_once(__DIR__.'/../app.php');
require_once(__DIR__.'/../../../../bootstrap/app.php');

use App\Models\Tenant;
use App\Models\TenantHasSetting;
use App\Models\TenantSetting;
use App\Repositories\Tenant\TenantRepository;
use App\Repositories\TenantHasSetting\TenantHasSettingRepository;
use App\Repositories\TenantSetting\TenantSettingRepository;
use Illuminate\Console\Command;
use Illuminate\Console\Application;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;


class TenantSettingsDedupe extends Command
{
	/**
	 * @var App\Repositories\Tenant\TenantRepository
	 */
	private $tenantRepository;

	/**
	 * @var App\Repositories\TenantSetting\TenantSettingRepository
	 */
	private $tenantSettingRepository;

	/**
	 * @var App\Repositories\TenantHasSetting\TenantHasSettingRepository
	 */
	private $tenantHasSettingRepository;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(
		TenantRepository $tenantRepository,
		TenantSettingRepository $tenantSettingRepository,
		TenantHasSetting $tenantHasSetting,
		TenantHasSettingRepository $tenantHasSettingRepository
	) {
		parent::__construct();
		$this->output = new ConsoleOutput;
		$this->tenantRepository = $tenantRepository;
		$this->tenantSettingRepository = $tenantSettingRepository;
		$this->tenantHasSetting = $tenantHasSetting;
		$this->tenantHasSettingRepository = $tenantHasSettingRepository;
	}

	/**
	 * Poorman's progress bar.
	 *
	 * @return void
	 */
	public function progress()
	{
		print('·');
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$redundantSettings = $this->getRedundantSettings($this->tenantSettingRepository->getAllSettings());
		if (!$redundantSettings->count()) {
			return $this->info('There are no duplicate settings to process.');
		}

		$tenants = $this->tenantRepository->getAllTenants();
		if (!$tenants->count()) {
			return $this->info('There are no available tenants to process. ');
		}

		$this->info('Removing duplicate tenant settings for all existing tenants.');

		$firstIdPerKey = [];
		foreach ($redundantSettings as $settingKey => $settings) {
			// get the extraneousused per tenant setting key
			$firstIdPerKey[$settingKey] = min($settings->keys()->all());
		}

		foreach ($tenants as $tenant) {
			// $this->info("Tenant {$tenant->tenant_id}");
			$this->progress();
			try {
				$dupeTenantSettings = $this->getRedundantSettings(
					$this->tenantHasSettingRepository->getSettingsList($tenant->tenant_id)
				);
				foreach ($dupeTenantSettings as $settingKey => $tenantSettings) {
					$this->progress();
					$enabledSettings = $tenantSettings->count();
					$disableSettings = $tenantSettings->whereNotNull('deleted_at')->count();
					if ($enabledSettings == $disableSettings || $disableSettings == 0) {
						// either
						// tenant has no active setting for this duplicate key
						// or
						// tenant has all settings active for this duplicate key
						// then
						// retain the first, delete the rest
					}
					if (($enabledSettings - $disableSettings) > 0) {
						// if
						// tenant has at least 1 active setting for this key
						// then
						// check if first is active, activate if it is not, delete the rest
						$initialSetting = $tenantSettings->get($firstIdPerKey[$settingKey]);
						if (!$initialSetting->is_active) {
							$this->tenantHasSetting->enableSetting($tenant->tenant_id, $firstIdPerKey[$settingKey]);
						}
					}
					$excessSettings = $tenantSettings->where('tenant_setting_id', '!=', $firstIdPerKey[$settingKey]);
					foreach ($excessSettings as $setting) {
						$this->progress();
						$this->tenantHasSetting->disableSetting($tenant->tenant_id, $setting->tenant_setting_id);
					}
				}
			} catch (Exception $e) {
				print(PHP_EOL);
				$this->warn($e->getTraceAsString());
				throw $e;
			}

		}

		// Remove the redundant settings
		foreach ($redundantSettings as $settingKey => $settings) {
			$excessSettings = $settings->where('tenant_setting_id', '!=', $firstIdPerKey[$settingKey]);
			foreach ($excessSettings as $redundantSetting) {
				$this->progress();
				$redundantSetting->delete();
			}
		}

		print(PHP_EOL);
		$this->info('Processing all tenants finished successfully!');
	}

	/**
	 * Get duplicate settings by a setting's key value
	 * @codeCoverageIgnore
	 *
	 * @return Collection
	 */
	public function getRedundantSettings(Collection $settings): Collection
	{
		// get all keys with duplicate
		$settingKeys = array_keys(array_filter(array_count_values(
					$settings->map(function ($setting) {
						return $setting->key;
					})->all()),
					function ($count) {
						return $count > 1;
					}
				));
		$redundantSettings = collect();
		foreach ($settingKeys as $settingKey) {
			$settingsPerKey = collect();
			foreach ($settings->where('key', $settingKey) as $setting) {
				$settingsPerKey->put($setting->tenant_setting_id, $setting);
			}
			$redundantSettings->put($settingKey, $settingsPerKey);
		}
		return $redundantSettings;
	}
}


$app->boot();
$cli = $app->make(TenantSettingsDedupe::class);
$cli->handle();

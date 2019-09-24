<?php
namespace App\Repositories\TenantSetting;

use App\Repositories\TenantSetting\TenantSettingInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\ResponseHelper;
use App\Models\TenantSetting;
use Illuminate\Http\Request;
use Validator;
use DB;

class TenantSettingRepository implements TenantSettingInterface
{

    /**
     * The tenantSetting for the model.
     *
     * @var App\Models\TenantSetting
     */
    public $tenantSetting;

    /**
     * Create a new repository instance.
     * @codeCoverageIgnore
     *
     * @param App\Models\TenantSetting $tenantSetting
     * @return void
     */
    public function __construct(TenantSetting $tenantSetting)
    {
        $this->tenantSetting = $tenantSetting;
    }
    
    /**
     * Update setting value
     * @codeCoverageIgnore
     *
     * @param array $data
     * @param int $settingId
     * @return App\Models\TenantSetting
     */
    public function updateSetting(array $data, int $settingId): TenantSetting
    {
        $setting = $this->tenantSetting->findOrFail($settingId);
        $setting->update($data);
        return $setting;
    }

    /**
     * Get all tenant's settings data
     * @codeCoverageIgnore
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllSettings(Request $request): LengthAwarePaginator
    {
        return $this->tenantSetting->paginate($request->perPage);
    }

    /**
     * Get all tenant's settings data.
     * @codeCoverageIgnore
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllTenantSettings(): Collection
    {
        return $this->tenantSetting->getAllTenantSettings();
    }
}

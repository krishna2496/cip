<?php
namespace App\Repositories\TenantSetting;

use App\Repositories\TenantSetting\TenantSettingInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Helpers\ResponseHelper;
use App\Models\TenantSetting;
use Illuminate\Http\Request;
use PDOException;
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
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllSettings(Request $request): LengthAwarePaginator
    {
        return $this->tenantSetting->paginate($request->perPage);
    }

    /**
     * Get all tenant's settings data. Used for front end api.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllTenantSettings(): Collection
    {
        return $this->tenantSetting->select('tenant_setting_id', 'key', 'value')->get();
    }
}

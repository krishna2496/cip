<?php
namespace App\Repositories\TenantSetting;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TenantSetting;

interface TenantSettingInterface
{
    /**
     * Update setting value
     *
     * @param array $data
     * @param int $settingId
     * @return App\Models\TenantSetting
     */
    public function updateSetting(array $data, int $settingId): TenantSetting;

    /**
     * Get all tenant's settings data
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllSettings(Request $request): LengthAwarePaginator;

    /**
     * Get all tenant's settings data
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllTenantSettings(): Collection;
}

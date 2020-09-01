<?php

namespace App\Repositories\Currency;

use Illuminate\Http\Request;
use App\Models\TenantAvailableCurrency;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Currency\CurrencyRepository;

class TenantAvailableCurrencyRepository
{
    /**
     * @var App\Models\TenantAvailableCurrency
     */
    private $tenantAvailableCurrency;

    /**
     * @var App\Models\Tenant
     */
    private $tenant;

    /**
     * Create a new Currency repository instance.
     *
     * @param App\Models\TenantAvailableCurrency $tenantCurrency
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(
        TenantAvailableCurrency $tenantAvailableCurrency,
        Tenant $tenant
    ) {
        $this->tenantAvailableCurrency = $tenantAvailableCurrency;
        $this->tenant = $tenant;
    }

    /**
     * Store currency
     *
     * @param array $currency
     * @param int $tenantId
     * @return void
     */
    public function store(array $currency, int $tenantId)
    {
        $tenant = $this->tenant->findOrFail($tenantId);

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $currency['code'],
            'default' => $currency['default'],
            'is_active' => $currency['is_active']
        ];

        if ($currency['is_active'] === '1' && $currency['default'] === '1') {
            $this->tenantAvailableCurrency
            ->where('tenant_id', $tenantId)
            ->update(['default' => '0']);
        }

        $this->tenantAvailableCurrency->create($currencyData);
    }

    /**
     * Update currency
     *
     * @param array $currency
     * @param int $tenantId
     * @return void
     */
    public function update(array $currency, int $tenantId)
    {
        $tenantCurrencyData = $this->tenantAvailableCurrency
            ->where(['tenant_id' => $tenantId, 'code' => $currency['code']])
            ->firstOrFail();

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $currency['code'],
            'default' => $currency['default'],
            'is_active' => $currency['is_active']
        ];

        if ($currency['is_active'] === '1' && $currency['default'] === '1') {
            $this->tenantAvailableCurrency->where('tenant_id', $tenantId)->update(['default' => '0']);
        }
        $this->tenantAvailableCurrency->where(['tenant_id' => $tenantId, 'code' => $currency['code']])
            ->update($currencyData);
    }

    /**
     * List of all tenant currency
     *
     * @param int $perPage
     * @param int $tenantId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTenantCurrencyList(int $perPage, int $tenantId) : LengthAwarePaginator
    {
        // Check tenant is present in the system
        $tenantData = $this->tenant->findOrFail($tenantId);

        $currencyTenantDetails = $this->tenantAvailableCurrency
            ->where(['tenant_id' => $tenantId])
            ->orderBy('code', 'ASC')
            ->paginate($perPage);
        return $currencyTenantDetails;
    }
}

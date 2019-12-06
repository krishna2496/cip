<?php
namespace App\Repositories\City;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\City;

interface CityInterface
{
    /**
    * Get a listing of resource.
    *
    * @param int $countryId
    * @return Illuminate\Support\Collection
    */
    public function cityList(int $countryId): Collection;

    /**
     * Get city data from cityId
     *
     * @param string $cityId
     * @return array
     */
    public function getCity(string $cityId) : array;

    /**
     * Store city data
     *
     * @param string $countryId
     * @return City
     */
    public function store(string $countryId): City;
}

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
     * @param int $languageId
     * @param int $defaultLanguageId
     * @return array
     */
    public function getCity(string $cityId, int $languageId, int $defaultLanguageId) : array;

    /**
     * Store city data
     *
     * @param string $countryId
     * @return City
     */
    public function store(string $countryId): City;

    /**
     * Get listing of all city.
     *
     * @return Illuminate\Support\Collection
     */
    public function cityLists(): Collection;

    
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @param  int $id
    * @return App\Models\City
    */
    public function update(Request $request, int $id): City;

    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\City
     */
    public function find(int $id): City;
}

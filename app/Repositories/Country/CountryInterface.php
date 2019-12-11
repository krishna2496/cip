<?php
namespace App\Repositories\Country;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface CountryInterface
{
    /**
    * Get a listing of resource.
    *
    * @return Illuminate\Support\Collection
    */
    public function countryList(): Collection;

    /**
     * Get country id from country code
     *
     * @param string $countryCode
     * @return int
     */
    public function getCountryId(string $countryCode) : int;

    /**
     * Get country detail from country_id
     *
     * @param int  $countryId
     * @return array
     */
    public function getCountry(int $countryId) : array;
    
    /**
     * Country transformation.
     *
     * @param array $countryList
     * @param int $languageId 
     * @return Array
     */
    public function countryTransform(array $countryList,int $languageId): Array;
}

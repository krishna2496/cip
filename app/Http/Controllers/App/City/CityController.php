<?php
namespace App\Http\Controllers\App\City;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\City\CityRepository;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CityController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\City\CityRepository
     */
    private $cityRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\City\CityRepository $cityRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        CityRepository $cityRepository,
        ResponseHelper $responseHelper
    ) {
        $this->cityRepository = $cityRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
    * Fetch city by country id
    *
    * @param int $countryId
    * @return Illuminate\Http\JsonResponse
    */
    public function fetchCity(int $countryId): JsonResponse
    {
        try {
            $cityList = $this->cityRepository->cityList($countryId);
            $apiData = $cityList->toArray();
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_CITY_LISTING');
            $apiMessage = (!empty($apiData)) ?
            trans('messages.success.MESSAGE_CITY_LISTING') :
            trans('messages.success.MESSAGE_NO_CITY_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_COUNTRY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_COUNTRY_NOT_FOUND')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }
}

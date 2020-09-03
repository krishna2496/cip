<?php

namespace Tests\Unit\Http\Repositories\Mission;

use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\S3Helper;
use App\Repositories\Country\CountryRepository;
use App\Repositories\MissionMedia\MissionMediaRepository;
use App\Services\Mission\ModelsService;
use App\Repositories\MissionTab\MissionTabRepository;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Mission\MissionRepository;
use App\Models\Mission;
use App\Models\MissionLanguage;
use App\User;
use Mockery;
use TestCase;
use Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TimeMission;
use App\Models\MissionDocument;
use App\Models\FavouriteMission;
use App\Models\MissionSkill;
use App\Models\MissionRating;
use App\Models\MissionApplication;
use App\Models\City;
use App\Repositories\MissionImpact\MissionImpactRepository;
use App\Services\Mission\AdminMissionTransformService;
use App\Models\MissionImpact;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Models\MissionTabLanguage;
use App\Models\Organization;
use App\Models\MissionTab;

class MissionRepositoryTest extends TestCase
{
    /**
    * @testdox Test impact mission success
    *
    * @return void
    */
    public function testStoreImpactMissionSuccess()
    {
        // \DB::setDefaultConnection('tenant');
        
        $data = [
            'theme_id' => 1,
            'city_id' => 1,
            'country_id' => 233,
            'start_date' => '2019-05-15 10:40:00',
            'end_date' => '2022-10-15 10:40:00',
            'total_seats' => rand(10, 1000),
            'availability_id' => 1,
            'mission_type' => 'DONATION',
            'publication_status' => 'APPROVED',
            'availability_id' => 1,
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10)
            ],
            'location' => [
                'city_id' => 1,
                'country_id' => 233,
                'country_code' => 'US'
            ],
            'donation_attribute' => [
                'goal_amount_currency' => 'CAD',
                'goal_amount' => 253,
                'show_goal_amount' => 1,
                'show_donation_percentage' => 0,
                'show_donation_meter'=> 0,
                'show_donation_count' =>0,
                'show_donors_count' =>0,
                'disable_when_funded' => 0,
                'is_disabled' => 0
            ],
            'mission_detail'=> [
                [
                    'lang'=> 'en',
                    'title'=> 'New Organization Mission created',
                    'short_description'=> 'this is testing api with all mission details',
                    'objective'=> 'To test and check',
                    'label_goal_achieved'=> 'test percentage',
                    'label_goal_objective'=> 'check test percentage',
                    'section'=> [
                        [
                            'title'=> 'Section title',
                            'description'=> 'Section description'
                        ]
                    ],
                    'custom_information'=> [
                        [
                            'title'=> 'Customer info',
                            'description'=> 'Description of customer info'
                        ]
                    ]
                ]
            ],
            'impact' => [
                [
                    'icon_path' => str_random(100),
                    'sort_key' => rand(10000, 200000),
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ]
        ];

        $requestData = new Request($data);

        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $collection = $this->mock(Collection::class);
        $adminMissionTransformService = $this->mock(AdminMissionTransformService::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $organization = $this->mock(Organization::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage
        );

        $languages = [
            (object)[
                'language_id' => 1,
                'name' => 'English',
                'code' => 'en',
                'status' => '1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            (object)[
                'language_id' => 2,
                'name' => 'French',
                'code' => 'fr',
                'status' =>'1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ]
        ];

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $collectionLanguages = collect($languages);

        $organization->shouldReceive('updateOrCreate')
        ->once()
        ->with(['organization_id' => $requestData->organisation['organisation_id']], $requestData->organisation)
        ->andReturn();

        $languageHelper->shouldReceive('getLanguages')
        ->once()
        ->andReturn($collectionLanguages);

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
        ->once()
        ->with($requestData)
        ->andReturn($defaultLanguage);

        $countryId= $data['location']['country_id'];

        $countryRepository->shouldReceive('getCountryId')
        ->once()
        ->with($requestData->location['country_code'])
        ->andReturn($countryId);

        $missionModel = new Mission();
        $missionModel->mission_id = 6587;
        $modelService->mission
        ->shouldReceive('create')
        ->once()
        ->andReturn($missionModel);

        $hasOne = \Illuminate\Database\Eloquent\Relations\HasOne::class;
        $mission->shouldReceive('volunteeringAttribute')
        ->once()
        ->andReturn($hasOne);

        $hasOneMockObject = $this->mock(\Illuminate\Database\Eloquent\Relations\HasOne::class);
        $hasOneMockObject->shouldReceive('create')
        ->once()
        ->andReturn($missionModel);

        $modelService->missionLanguage->shouldReceive('create')
        ->once()
        ->andReturn(false);

        $tenantName = str_random(10);
        $helpers->shouldReceive('getSubDomainFromRequest')
        ->once()
        ->with($requestData)
        ->andReturn($tenantName);

        $activatedTenantSetting =  [
            'volunteering',
            'mission_impact'
        ];
        
        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
        ->once()
        ->with($requestData)
        ->andReturn($activatedTenantSetting);

        $missionImpactRepository->shouldReceive('store')
        ->once()
        ->andReturn();

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $missionImpactRepository,
            $adminMissionTransformService,
            $tenantActivatedSettingRepository,
            $missionTabRepository
        );

        $response = $repository->store($requestData);
        
        $this->assertInstanceOf(mission::class, $response);
    }

    /**
    * @testdox Test impact mission update success
    *
    * @return void
    */
    public function testUpdateImpactMissionSuccess()
    {
        $data = [
            'impact' => [
                [
                    'mission_impact_id' => str_random(36),
                    'icon_path' => str_random(100),
                    'sort_key' => rand(50000, 70000),
                    'translations' => [
                        [
                            'language_code' => 'fr',
                            'content' => str_random(160)
                        ]
                    ]
                ],
                [
                    'sort_key' => rand(50000, 70000),
                    'amount' => rand(100000, 200000),
                    'translations' => [
                        [
                            'language_code' => 'es',
                            'content' => str_random(160)
                        ]
                    ]
                ]
            ]
        ];
        $missionId = rand(50000, 70000);

        $requestData = new Request($data);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $collection = $this->mock(Collection::class);
        $adminMissionTransformService = $this->mock(AdminMissionTransformService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $collection = $this->mock(Collection::class);
        $organization = $this->mock(Organization::class);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage
        );

        $languages = [
            (object)[
                'language_id' => 1,
                'name' => 'English',
                'code' => 'en',
                'status' => '1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
            (object)[
                'language_id' => 2,
                'name' => 'French',
                'code' => 'fr',
                'status' =>'1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ]
        ];

        $defaultLanguage = (object)[
            'language_id' => 1,
            'code' => 'en',
            'name' => 'English',
            'default' => '1'
        ];

        $collectionLanguages = collect($languages);

        $languageHelper->shouldReceive('getLanguages')
        ->once()
        ->andReturn($collectionLanguages);

        $languageHelper->shouldReceive('getDefaultTenantLanguage')
        ->once()
        ->with($requestData)
        ->andReturn($defaultLanguage);

        $missionModel = new Mission();
        $modelService->mission->shouldReceive('findOrFail')
        ->once()
        ->andReturn($missionModel);

        $tenantName = str_random(10);
        $helpers->shouldReceive('getSubDomainFromRequest')
        ->once()
        ->with($requestData)
        ->andReturn($tenantName);

        $activatedTenantSetting =  [
            'volunteering',
            'mission_impact'
        ];
        
        $tenantActivatedSettingRepository->shouldReceive('getAllTenantActivatedSetting')
        ->times(2)
        ->with($requestData)
        ->andReturn($activatedTenantSetting);

        $missionImpactRepository->shouldReceive('update')
        ->once()
        ->andReturn();

        $missionImpactRepository->shouldReceive('store')
        ->once()
        ->andReturn();

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $missionImpactRepository,
            $adminMissionTransformService,
            $tenantActivatedSettingRepository,
            $missionTabRepository
        );

        $response = $repository->update($requestData, $missionId);

        $this->assertInstanceOf(mission::class, $response);
    }

    /**
    * @testdox mission impact linked to mission
    *
    */
    public function testMissionImpactLinkedToMissionSuccess()
    {
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);
        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $collection = $this->mock(Collection::class);
        $adminMissionTransformService = $this->mock(AdminMissionTransformService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $organization = $this->mock(Organization::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage
        );

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $missionImpactRepository,
            $adminMissionTransformService,
            $tenantActivatedSettingRepository,
            $missionTabRepository
        );

        $missionId = rand(50000, 70000);
        $missionImpactId = rand(50000, 70000);

        $modelService->missionImpact->shouldReceive('where')
        ->once()
        ->with([['mission_id', '=', $missionId], ['mission_impact_id', '=', $missionImpactId]])
        ->andReturn($missionImpact);

        $modelService->missionImpact->shouldReceive('firstOrFail')
        ->once()
        ->andReturn($missionImpact);

        $response = $repository->isMissionImpactLinkedToMission($missionId, $missionImpactId);
        $this->assertInstanceOf(MissionImpact::class, $response);
    }

    /**
    * @testdox Test mission tab deleted by mission_tab_id success
    *
    * @return void
    */
    public function testDeleteMissionTabByMissionTabIdSuccess()
    {
        $missionTabId = str_random(8).'-'.str_random(4).'-'.str_random(4).'-'.str_random(4).'-'.str_random(12);

        $mission = $this->mock(Mission::class);
        $timeMission = $this->mock(TimeMission::class);
        $missionLanguage = $this->mock(MissionLanguage::class);
        $missionDocument = $this->mock(MissionDocument::class);
        $favouriteMission = $this->mock(FavouriteMission::class);
        $missionSkill = $this->mock(MissionSkill::class);
        $missionRating = $this->mock(MissionRating::class);
        $missionApplication = $this->mock(MissionApplication::class);
        $city = $this->mock(City::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $languageHelper = $this->mock(LanguageHelper::class);
        $helpers = $this->mock(Helpers::class);
        $s3Helper = $this->mock(S3Helper::class);
        $countryRepository = $this->mock(CountryRepository::class);
        $missionMediaRepository = $this->mock(MissionMediaRepository::class);
        $modelService = $this->mock(ModelsService::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $collection = $this->mock(Collection::class);
        $organization = $this->mock(Organization::class);
        $adminMissionTransformService = $this->mock(AdminMissionTransformService::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $missionTabRepository = $this->mock(MissionTabRepository::class);
        $organization = $this->mock(Organization::class);
        $missionTab = $this->mock(MissionTab::class);
        $missionTabLanguage = $this->mock(MissionTabLanguage::class);
        $missionImpact = $this->mock(MissionImpact::class);
        $missionImpactRepository = $this->mock(MissionImpactRepository::class);

        $modelService = $this->modelService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage
        );

        $modelService->missionTab
        ->shouldReceive('deleteMissionTabByMissionTabId')
        ->with($missionTabId)
        ->andReturn();

        $repository = $this->getRepository(
            $languageHelper,
            $helpers,
            $s3Helper,
            $countryRepository,
            $missionMediaRepository,
            $modelService,
            $missionImpactRepository,
            $adminMissionTransformService,
            $tenantActivatedSettingRepository,
            $missionTabRepository
        );

        $response = $repository->deleteMissionTabByMissionTabId($missionTabId);
    }

    /**
     * Create a new respository instance.
     *
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\S3Helper $s3helper
     * @param  App\Repositories\Country\CountryRepository $countryRepository
     * @param  App\Repositories\MissionMedia\MissionMediaRepository $missionMediaRepository
     * @param  App\Services\Mission\ModelsService $modelsService
     * @param  App\Repositories\MissionImpact\MissionImpactRepository $missionImpactRepository
     * @param  App\Services\Mission\AdminMissionTransformService $adminMissionTransformService
     * @param  App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param  App\Repositories\MissionMedia\MissionTabRepository $missionTabRepository
     * @return void
     */
    private function getRepository(
        LanguageHelper $languageHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        CountryRepository $countryRepository,
        MissionMediaRepository $missionMediaRepository,
        ModelsService $modelsService,
        MissionImpactRepository $missionImpactRepository,
        AdminMissionTransformService $adminMissionTransformService,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        MissionTabRepository $missionTabRepository
    ) {
        return new MissionRepository(
            $languageHelper,
            $helpers,
            $s3helper,
            $countryRepository,
            $missionMediaRepository,
            $modelsService,
            $missionImpactRepository,
            $adminMissionTransformService,
            $tenantActivatedSettingRepository,
            $missionTabRepository
        );
    }

    /**
    * Mock an object
    *
    * @param string name
    *
    * @return Mockery
    */
    private function mock($class)
    {
        return Mockery::mock($class);
    }

    /**
    * get json reponse
    *
    * @param class name
    * @return JsonResponse
    */
    private function getJson($class)
    {
        return new JsonResponse($class);
    }

    /**
     * Create a new service instance.
     *
     * @param  App\Models\Mission $mission
     * @param  App\Models\TimeMission $timeMission
     * @param  App\Models\MissionLanguage $missionLanguage
     * @param  App\Models\MissionDocument $missionDocument
     * @param  App\Models\FavouriteMission $favouriteMission
     * @param  App\Models\MissionSkill $missionSkill
     * @param  App\Models\MissionRating $missionRating
     * @param  App\Models\MissionApplication $missionApplication
     * @param  App\Models\City $city
     * @param  App\Models\MissionImpact $missionImpact
     * @param  App\Models\Organization $organization
     * @param  App\Models\MissionTab $missionTab
     * @param  App\Models\MissionTabLanguage $missionTabLanguage
     * @return void
     */
    public function modelService(
        Mission $mission,
        TimeMission $timeMission,
        MissionLanguage $missionLanguage,
        MissionDocument $missionDocument,
        FavouriteMission $favouriteMission,
        MissionSkill $missionSkill,
        MissionRating $missionRating,
        MissionApplication $missionApplication,
        City $city,
        MissionImpact $missionImpact,
        Organization $organization,
        MissionTab $missionTab,
        MissionTabLanguage $missionTabLanguage
    ) {
        return new ModelsService(
            $mission,
            $timeMission,
            $missionLanguage,
            $missionDocument,
            $favouriteMission,
            $missionSkill,
            $missionRating,
            $missionApplication,
            $city,
            $missionImpact,
            $organization,
            $missionTab,
            $missionTabLanguage
        );
    }
}

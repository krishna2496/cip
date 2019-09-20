<?php
use Illuminate\Support\Facades\DB;
use App\Helpers\Helpers;

class AppMissionTest extends TestCase
{
    /**
     * @test
     *
     * Get all mission
     *
     * @return void
     */
    public function it_should_return_all_app_missions()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?search=title', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * No mission found
     *
     * @return void
     */
    public function it_should_return_no_mission_found_for_app()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        DB::setDefaultConnection('mysql');
        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        
        $this->get(route('app.missions'), ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "message"
        ]);
        $user->delete();
    }

    /**
     * @test
     *
     * Show error invalid credentials
     *
     * @return void
     */
    public function it_should_return_error_for_invalid_token()
    {
        $this->get(route('app.missions'), ['token' => str_random(100)])
        ->seeStatusCode(400);
    }

    /**
     * @test
     *
     * It should validate data for add mission to favourite
     *
     * @return void
     */
    public function it_should_validate_request_for_add_mission_to_favourite()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        DB::setDefaultConnection('mysql');

        $params = [
                'mission_id' => rand(1000000, 2000000)
            ];
        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->post('app/mission/favourite', $params, ['token' => $token])
          ->seeStatusCode(404)
          ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message",
                    "code"
                ]
            ]
        ]); 
        $user->delete();
    }

    /**
     * @test
     *
     * It should add mission to favourite
     *
     * @return void
     */
    public function it_should_add_mission_to_favourite()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $params = [
                'mission_id' => $mission->mission_id
            ];

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->post('app/mission/favourite', $params, ['token' => $token])
          ->seeStatusCode(201)
          ->seeJsonStructure([
            "status",
            "message"
        ]);
        App\Models\FavouriteMission::where('user_id', $user->user_id)->delete();
        $user->delete();
        $mission->delete();
    }

    /**
     * @test
     *
     * It should remove mission from favourite
     *
     * @return void
     */
    public function it_should_remove_mission_from_favourite()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();

        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();
        
        $params = [
                'mission_id' => $mission->mission_id
            ];

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        //Code for add mission to favourite
        $this->post('app/mission/favourite', $params, ['token' => $token])
          ->seeStatusCode(201)
          ->seeJsonStructure([
            "status",
            "message"
        ]);
        // change database connection to master
        DB::setDefaultConnection('mysql');
        $this->post('app/mission/favourite', $params, ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "message"
        ]);
        $user->delete();
        $mission->delete();
    }

    /**
     * @test
     *
     * Get mission detail by mission id
     *
     * @return void
     */
    public function it_should_return_app_mission_detail_by_id()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();

        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => str_random(10),
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ],
                [
                    "lang" => "fr",
                    "title" => str_random(10),
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);
        $mission = App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->first();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/mission/'.$mission->mission_id, ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "data" => [
                [
                    "mission_id",
                    "theme_id",
                    "city_id",
                    "country_id",
                    "start_date",
                    "end_date",
                    "total_seats",
                    "mission_type",
                    "publication_status",
                    "organisation_id",
                    "organisation_name",
                    "user_application_count",
                    "mission_application_count",
                    "favourite_mission_count",
                    "mission_rating_count",
                    "mission_rating_total_volunteers",
                    "user_application_status",
                    "rating",
                    "is_favourite",
                    "seats_left",
                    "default_media_type",
                    "default_media_path",
                    "title",
                    "short_description",
                    "set_view_detail",
                    "city_name",
                    "mission_theme"=> [
                        "mission_theme_id",
                        "theme_name",
                        "translations"
                    ],
                    "mission_document"=> []
                ]
            ],
            "message"
        ]);
        $user->delete();
    }

    /**
     * @test
     *
     * It should return error for invalid mission id
     *
     * @return void
     */
    public function it_should_return_error_for_invalid_mission_id_for_get_mission_details()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $missionId = rand(1000000,2000000);
        
        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('/app/mission/'.$missionId, ['token' => $token])
        ->seeStatusCode(404)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message",
                    "code"
                ]
            ]
        ]);   
        $user->delete();   
    }

    /**
     * @test
     *
     * Get mission detail by mission id
     *
     * @return void
     */
    public function it_should_return_related_mission_by_id()
    {
        $connection = 'tenant';

        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => str_random(10),
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ],
                [
                    "lang" => "fr",
                    "title" => str_random(10),
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2020-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.TIME"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2020-07-28 11:40:00",
            "application_start_date" => "2019-05-15 10:40:00",
            "application_end_date" => "2020-05-15 10:40:00",
            "application_start_time" => "2019-05-15 10:40:00",
            "application_end_time" => "2020-05-15 10:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);
        $missionRelated = App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->first();

        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('/app/related-missions/'.$mission->mission_id, ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "data" => [
                [
                    "mission_id",
                    "theme_id",
                    "city_id",
                    "country_id",
                    "start_date",
                    "end_date",
                    "total_seats",
                    "mission_type",
                    "publication_status",
                    "organisation_id",
                    "organisation_name",
                    "user_application_count",
                    "mission_application_count",
                    "favourite_mission_count",
                    "mission_rating_count",
                    "user_application_status",
                    "rating",
                    "is_favourite",
                    "seats_left",
                    "default_media_type",
                    "default_media_path",
                    "title",
                    "short_description",
                    "set_view_detail",
                    "city_name",
                    "mission_theme"=> [
                        "mission_theme_id",
                        "theme_name",
                        "translations"
                    ]
                ],
            ],
            "message"
        ]);
        $user->delete();
        $mission->delete();
        $missionRelated->delete();
    }

    /**
     * @test
     *
     * It should return error for invalid mission id
     *
     * @return void
     */
    public function it_should_return_error_for_invalid_mission_id_to_get_related_mission()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $missionId = rand(1000000,2000000);
        
        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('/app/related-missions/'.$missionId, ['token' => $token])
        ->seeStatusCode(404)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message",
                    "code"
                ]
            ]
        ]); 
        $user->delete();          
    }

    /**
     * @test
     *
     * Get mission volunteers by mission id
     *
     * @return void
     */
    public function it_should_return_app_mission_volunteers_by_mission_id()
    {
        $connection = 'tenant';
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/mission/'.$mission->mission_id.'/volunteers', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "message"
        ]);
        $mission->delete();
        $user->delete();
    }

    /**
     * @test
     *
     * It should return error for invalid mission id
     *
     * @return void
     */
    public function it_should_return_error_for_invalid_mission_id_for_get_volunteers()
    {
        $connection = 'tenant';        
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $missionId = rand(1000000,2000000);
        
        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/mission/'.$missionId.'/volunteers', ['token' => $token])
        ->seeStatusCode(404)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message",
                    "code"
                ]
            ]
        ]);  
        $user->delete();    
    }

    /**
     * @test
     *
     * It should validate data for add mission to favourite
     *
     * @return void
     */
    public function it_should_validate_mission_id_for_add_mission_to_favourite()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        DB::setDefaultConnection('mysql');

        $params = [
                'mission_id' => "test"
            ];
        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->post('app/mission/favourite', $params, ['token' => $token])
          ->seeStatusCode(422)
          ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message",
                    "code"
                ]
            ]
        ]); 
        $user->delete();
    }

    /**
     * @test
     *
     * Get all mission
     *
     * @return void
     */
    public function it_should_return_all_top_recommended_app_missions()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=recommended-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();
        $mission->delete();
    }

    /**
     * @test
     *
     * Get all mission
     *
     * @return void
     */
    public function it_should_return_blank_app_missions()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=recommended-missions1', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();
        $mission->delete();
    }

    /**
     * @test
     *
     * Explore mission
     *
     * @return void
     */
    public function it_should_return_explore_missions()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/explore-mission', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "data"
        ]);
        $user->delete();
    }

    /**
     * @test
     *
     * Get all mission expore random
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_random()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=random-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore theme
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_by_theme()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=themes', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore country
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_by_country()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=top_countries&explore_mission_params=united%20states', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore organization
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_by_organization()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=top_organization&explore_mission_params='.$organizationName, ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore top recommended
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_by_top_recommended()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=recommended-missions&explore_mission_params='.$organizationName, ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore top favourite
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_by_top_favourite()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=recommended-missions&explore_mission_params='.$organizationName, ['token' => $token])
          ->seeStatusCode(200);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore most ranked
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_by_most_ranked()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=most-ranked-missions&explore_mission_params='.$organizationName, ['token' => $token])
          ->seeStatusCode(200);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore country
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_country()
    {
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => str_random(10),
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=country&explore_mission_params=united%20states', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission expore organization
     *
     * @return void
     */
    public function it_should_return_all_app_missions_explore_mission_type_organization()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?explore_mission_type=organization&explore_mission_params='.$organizationName, ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission sort by oldest
     *
     * @return void
     */
    public function it_should_return_all_app_missions_sortby_oldest()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?&sort_by=oldest&explore_mission_type=random-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission sort by newest
     *
     * @return void
     */
    public function it_should_return_all_app_missions_sortby_newest()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?&sort_by=newest&explore_mission_type=random-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission sort by lowest_available_seats
     *
     * @return void
     */
    public function it_should_return_all_app_missions_sortby_lowest_available_seats()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?&sort_by=lowest_available_seats&explore_mission_type=random-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission sort by highest_available_seats
     *
     * @return void
     */
    public function it_should_return_all_app_missions_sortby_highest_available_seats()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?&sort_by=highest_available_seats&explore_mission_type=random-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Get all mission sort by deadline
     *
     * @return void
     */
    public function it_should_return_all_app_missions_sortby_deadline()
    {
        $organizationName = str_random(10);
        $connection = 'tenant';
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $params = [
            "organisation" => [
                "organisation_id" => 1,
                "organisation_name" => $organizationName,
                "organisation_detail" => [  
                    [  
                       "lang"=>"en",
                       "detail"=>"Testing organisation description in English"
                    ],
                    [  
                       "lang"=>"fr",
                       "detail"=>"Testing organisation description in French"
                    ]
                ]
            ],
            "location" => [
                "city_id" => 1,
                "country_code" => "US"
            ],
            "mission_detail" => [[
                    "lang" => "en",
                    "title" => 'title',
                    "short_description" => str_random(20),
                    "objective" => str_random(20),
                    "section" => [
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ],
                        [
                            "title" => str_random(10),
                            "description" => str_random(100),
                        ]
                    ]
                ]
            ],
            "media_images" => [[
                    "media_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/default_theme/assets/images/volunteer9.png",
                    "default" => "1"
                ]
            ],
            "documents" => [[
                    "document_path" => "https://optimy-dev-tatvasoft.s3.eu-central-1.amazonaws.com/test/sample.pdf"
                ]
            ],
            "media_videos"=> [[
                "media_name" => "youtube_small",
                "media_path" => "https://www.youtube.com/watch?v=PCwL3-hkKrg"
                ]
            ],
            "start_date" => "2019-05-15 10:40:00",
            "end_date" => "2019-10-15 10:40:00",
            "mission_type" => config("constants.mission_type.GOAL"),
            "goal_objective" => rand(1, 1000),
            "total_seats" => rand(1, 1000),
            "application_deadline" => "2019-07-28 11:40:00",
            "publication_status" => config("constants.publication_status.APPROVED"),
            "theme_id" => 1,
            "availability_id" => 1
        ];

        $this->post("missions", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        DB::setDefaultConnection('mysql');
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();

        $token = Helpers::getJwtToken($user->user_id, env('DEFAULT_TENANT'));
        $this->get('app/missions?&sort_by=deadline&explore_mission_type=random-missions', ['token' => $token])
          ->seeStatusCode(200)
          ->seeJsonStructure([
            "status",
            "meta_data" => [
                "filters" => [
                    "search"
                ]
            ],
            "message"
        ]);
        $user->delete();        
        App\Models\Mission::orderBy("mission_id", "DESC")->take(1)->delete();
    }
}

<?php

class CountryTest extends TestCase
{
    /**
     * @test
     *
     * Get country list
     *
     * @return void
     */
    public function city_test_it_should_return_all_country_list()
    {
        // Get random langauge for country name
        $countryName = str_random(5);
        $params = [
            "countries" => [
                [
                    "iso" => str_random(2),
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> $countryName
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);
        $countryId = json_decode($response->response->getContent())->data->country_ids[0]->country_id;
        
        DB::setDefaultConnection('mysql');

        $this->get('/entities/countries?search='.$countryName, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200);

        DB::setDefaultConnection('mysql');

        $this->get('/entities/countries', ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200)
        ->seeJsonStructure([
            "status",
            "data" => [
                "*" => []
            ],
            "message"
        ]);
        DB::setDefaultConnection('mysql');

        // Delete country and country_language data
        $this->delete("entities/countries/$countryId", [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(204);
    }

    /**
     * @test
     *
     * No data found for Country
     *
     * @return void
     */
    public function city_test_it_should_return_no_country_found()
    {
        $this->get('/entities/countries', ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200);
    }

    /**
     * @test
     *
     * Return error for invalid token
     *
     * @return void
     */
    public function city_test_it_should_return_error_for_invalid_authorization_token_for_get_country()
    {
        $this->get('/app/country', ['Authorization' => ''])
        ->seeStatusCode(401)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message"
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function city_test_it_should_create_and_delete_country()
    {
        // Get random langauge for country name
        $params = [
            "countries" => [
                [
                    "iso" => str_random(2),
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> str_random(5)
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);
        $countryId = json_decode($response->response->getContent())->data->country_ids[0]->country_id;
        
        DB::setDefaultConnection('mysql');

        // Delete country and country_language data
        $this->delete("entities/countries/$countryId", [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(204);
    }    

    /**
     * @test
     */
    public function city_test_it_should_return_validation_error_for_iso_code_on_add_country()
    {
        // Get random langauge for country name
        $params = [
            "countries" => [
                [
                    "iso" => '',
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> str_random(5)
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);

    }

    /**
     * @test
     */
    public function city_test_it_should_return_validation_error_for_language_code_on_add_country()
    {
        // Get random langauge for country name
        $params = [
            "countries" => [
                [
                    "iso" => str_random(2),
                    "translations"=> [
                        [
                            "lang"=> str_random(5),
                            "name"=> str_random(5)
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);

    }
    
    /**
     * @test
     *
     * Update country api
     *
     * @return void
     */
    public function city_test_it_should_update_country()
    {
        $iso = str_random(3);

        $params = [
            "iso"=>$iso,
            "translations"=>[
                [
                    "lang"=>"en",
                    "name"=>str_random(10)
                ]
            ]
        ];

        $connection = 'tenant';
        $country = factory(\App\Models\Country::class)->make();
        $country->setConnection($connection);
        $country->save();
        $countryId = $country->country_id;

        $this->patch("entities/countries/".$countryId, $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200)
        ->seeJsonStructure([
            'message',
            'status',
        ]);
        App\Models\Country::where('ISO', $iso)->delete();
    }

    /**
     * @test
     *
     * Update country api
     *
     * @return void
     */
    public function city_test_it_should_return_error_if_iso_is_invalid_for_update_country()
    {
        $params = [
            "iso"=>"",
            "translations"=>[
                [
                    "lang"=>"en",
                    "name"=>str_random(10)
                ]
            ]
        ];

        $connection = 'tenant';
        $country = factory(\App\Models\Country::class)->make();
        $country->setConnection($connection);
        $country->save();
        $countryId = $country->country_id;

        $this->patch("entities/countries/".$countryId, $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message"
                ]
            ]
        ]);
        $country->delete();
    }

    /**
     * @test
     *
     * Update country api
     *
     * @return void
     */
    public function city_test_it_should_return_error_if_data_is_invalid_for_update_country()
    {
        $params = [
            "iso"=>"",
            "translations"=>[
                [
                    "lang"=>"test",
                    "name"=>str_random(10)
                ]
            ]
        ];

        $connection = 'tenant';
        $country = factory(\App\Models\Country::class)->make();
        $country->setConnection($connection);
        $country->save();
        $countryId = $country->country_id;

        $this->patch("entities/countries/".$countryId, $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message"
                ]
            ]
        ]);
        $country->delete();
    }

    /**
     * @test
     *
     * Update country api
     *
     * @return void
     */
    public function city_test_it_should_return_error_if_id_is_invalid_for_update_country()
    {
        $params = [
            "iso"=>"",
            "translations"=>[
                [
                    "lang"=>"test",
                    "name"=>str_random(10)
                ]
            ]
        ];

        $this->patch("entities/countries/".rand(5000000, 9000000), $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(404)
        ->seeJsonStructure([
            "errors" => [
                [
                    "status",
                    "type",
                    "message"
                ]
            ]
        ]);
    }

    /**
     * @test
     *
     * Delete country api
     *
     * @return void
     */
    public function city_test_it_should_return_delete_country()
    {
        $connection = 'tenant';
        $country = factory(\App\Models\Country::class)->make();
        $country->setConnection($connection);
        $country->save();
        $countryId = $country->country_id;

        $this->delete("entities/countries/".$countryId, [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(204);
    }

    /**
     * @test
     *
     * Return error for delete country api
     *
     * @return void
     */
    public function city_test_it_should_return_error_for_delete_country()
    {
        $this->delete("entities/countries/".rand(1000000, 5000000), [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(404);
    }
    
    /**
     * @test
     */
    public function city_test_it_should_return_validation_error_on_iso_exist_on_add_country()
    {
        $countryISO = str_random(2);
        
        // Get random langauge for country name
        $params = [
            "countries" => [
                [
                    "iso" => $countryISO,
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> str_random(5)
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        $countryId = json_decode($response->response->getContent())->data->country_ids[0]->country_id;
        
        DB::setDefaultConnection('mysql');

        // Add another country with same ISO code        
        $params = [
            "countries" => [
                [
                    "iso" => $countryISO,
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> str_random(5)
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);

        DB::setDefaultConnection('mysql');

        // Delete country and country_language data
        $this->delete("entities/countries/$countryId", [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(204);
    } 

    /**
     * @test
     */
    public function city_test_it_should_return_validation_error_on_country_name_exist_on_add_country()
    {
        $countryName = str_random(5);
        $countryISO = str_random(2);

        // Get random langauge for country name
        $params = [
            "countries" => [
                [
                    "iso" => $countryISO,
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> $countryName
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);

        $countryId = json_decode($response->response->getContent())->data->country_ids[0]->country_id;
        
        DB::setDefaultConnection('mysql');

        // Add another country with same ISO code        
        $params = [
            "countries" => [
                [
                    "iso" => $countryISO,
                    "translations"=> [
                        [
                            "lang"=> "en",
                            "name"=> $countryName
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->post("entities/countries", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);

        DB::setDefaultConnection('mysql');
        
        // Delete country and country_language data
        $this->delete("entities/countries/$countryId", [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(204);
    }

    /**
     * @test
     *
     * Delete country api, will return error. If country belongs to mission
     *
     * @return void
     */
    public function city_test_it_return_error_not_able_to_delete_country_it_belongs_to_mission()
    {
        $connection = 'tenant';
        $country = factory(\App\Models\Country::class)->make();
        $country->setConnection($connection);
        $country->save();
        $countryId = $country->country_id;

        $city = factory(\App\Models\City::class)->make();
        $city->setConnection($connection);
        $city->save();
        $city->country_id = $countryId;
        $city->update();
        
        DB::setDefaultConnection('mysql');

        // Add user for this country and city
        $mission = factory(\App\Models\Mission::class)->make();
        $mission->setConnection($connection);
        $mission->save();
        $mission->city_id = $city->city_id;
        $mission->country_id = $countryId;
        $mission->update();

        $res = $this->delete("entities/countries/".$countryId, [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);        

        App\Models\Mission::where('mission_id', $mission->mission_id)->delete();
        App\Models\City::where('city_id', $city->city_id)->delete();
        App\Models\Country::where('country_id', $countryId)->delete();
    }

    /**
     * @test
     *
     * Delete country api, will return error. If country belongs to user
     *
     * @return void
     */
    public function city_test_it_return_error_not_able_to_delete_country_it_belongs_to_user()
    {
        $connection = 'tenant';
        $country = factory(\App\Models\Country::class)->make();
        $country->setConnection($connection);
        $country->save();
        $countryId = $country->country_id;

        $city = factory(\App\Models\City::class)->make();
        $city->setConnection($connection);
        $city->save();
        $city->country_id = $countryId;
        $city->update();
        
        DB::setDefaultConnection('mysql');

        // Add user for this country and city
        $user = factory(\App\User::class)->make();
        $user->setConnection($connection);
        $user->save();
        $user->city_id = $city->city_id;
        $user->country_id = $countryId;
        $user->update();

        $this->delete("entities/countries/".$countryId, [], ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);

        App\User::where('user_id', $user->user_id)->delete();
        App\Models\City::where('city_id', $city->city_id)->delete();
        App\Models\Country::where('country_id', $countryId)->delete();
    }
}

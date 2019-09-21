<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TenantOptionsTest extends TestCase
{
    /**
     * @test
     *
     * Reset style to default
     *
     * @return void
     */
    public function it_should_reset_style_to_default()
    {
        $this->get('style/reset-style', ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200);
    }
    
    /**
     * @test
     *
     * Download style and images from s3
     *
     * @return void
     */
    public function it_should_download_style_from_s3()
    {
        $this->get('style/download-style', ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200)
        ->seeJsonStructure([
            'status',
            'message',
            ]);
    }

    /**
    * @test
    *
    * Update style
    *
    * @return void
    */
    public function it_should_update_primary_color()
    {
        $params = [
            'primary_color' => "#ccc"
        ];

        $this->post('style/update-style', $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200);
    }

    /**
    * @test
    *
    * Get custom styling css
    *
    * @return void
    */
    public function it_should_return_custom_css()
    {
        $this->get('app/custom-css', ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200);
    }

    /**
     * @test
     *
     * Create tenant option
     *
     * @return void
     */
    public function it_should_create_tenant_option()
    {
        $optionName = str_random(20);
        $params = [
            'option_name' => $optionName,
            'option_value' =>
                [
                'translations' =>  [
                    [
                        'lang' => 'en',
                        'message' => str_random(20)
                    ]
                ],
            ],
        ];

        $this->post("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201)
        ->seeJsonStructure([
            'status',
            'message',
        ]);
        App\Models\TenantOption::where("option_name", $optionName)->orderBy("tenant_option_id", "DESC")->take(1)->delete();
    }

    /**
     * @test
     *
     * Return error if data is invalid
     *
     * @return void
     */
    public function it_should_return_error_for_invalid_data_for_tenant_option()
    {
        $optionName = '';
        $params = [
            'option_name' => $optionName,
            'option_value' =>
                [
                'translations' =>  [
                    [
                        'lang' => 'en',
                        'message' => str_random(20)
                    ]
                ],
            ],
        ];

        $this->post("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422)
        ->seeJsonStructure([
            'errors' => [
                [
                    'status',
                    'type',
                    'code',
                    'message'
                ]
            ]
        ]);
    }

    /**
     * @test
     *
     * Update tenant option
     *
     * @return void
     */
    public function it_should_update_tenant_option()
    {
        $optionName = str_random(20);
        $params = [
            'option_name' => $optionName,
            'option_value' =>
                [
                'translations' =>  [
                    [
                        'lang' => 'en',
                        'message' => str_random(20)
                    ]
                ],
            ],
        ];

        $this->post("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);
        DB::setDefaultConnection('mysql');
        $this->patch("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200)
        ->seeJsonStructure([
            'status',
            'message',
        ]);
        App\Models\TenantOption::where("option_name", $optionName)->orderBy("tenant_option_id", "DESC")->take(1)->delete();
    }

        /**
     * @test
     *
     * Return error if data is invalid
     *
     * @return void
     */
    public function it_should_return_error_for_invalid_data_for_update_tenant_option()
    {
        $optionName = str_random(20);
        $params = [
            'option_name' => $optionName,
            'option_value' =>
                [
                'translations' =>  [
                    [
                        'lang' => 'en',
                        'message' => str_random(20)
                    ]
                ],
            ],
        ];

        $this->post("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201);
        DB::setDefaultConnection('mysql');

        $optionName = str_random(20);
        $params = [
            'option_name' => $optionName,
            'option_value' =>
                [
                'translations' =>  [
                    [
                        'lang' => str_random('3'),
                        'message' => str_random(20)
                    ]
                ],
            ],
        ];
        
        $this->patch("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422)
        ->seeJsonStructure([
            'errors' => [
                [
                    'status',
                    'type',
                    'code',
                    'message'
                ]
            ]
        ]);
    }
    
    /**
    * @test
    *
    * Update style
    *
    * @return void
    */
    public function it_should_return_error_for_required_field_while_update_style()
    {
        $params = [];

        $this->post('style/update-style', $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(422);
    }

    /**
     * @test
     *
     * It should return error, while trying to update unavailable option
     *
     * @return void
     */
    public function it_should_update_tenant_option_with_unavailable_option()
    {
        $optionName = str_random(20);
        $params = [
            'option_name' => $optionName,
            'option_value' =>
                [
                'translations' =>  [
                    [
                        'lang' => 'en',
                        'message' => str_random(20)
                    ]
                ],
            ],
        ];
        
        $this->patch("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(404);

    }

    /**
     * @test
     *
     * Reset assets images to default
     *
     * @return void
     */
    public function it_should_reset_assets_images_to_default()
    {
        $this->get('style/reset-asset-images', ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(200);
    }

    /**
     * @test
     *
     * Create tenant option
     *
     * @return void
     */
    public function it_should_create_tenant_option_value()
    {
        $optionName = str_random(20);
        $params = [
            'option_name' => $optionName,
            'option_value' => 1            
        ];

        $this->post("tenant-option/", $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))])
        ->seeStatusCode(201)
        ->seeJsonStructure([
            'status',
            'message',
        ]);
        App\Models\TenantOption::where("option_name", $optionName)->orderBy("tenant_option_id", "DESC")->take(1)->delete();
    }

    /**
     *
     */
    public function it_should_update_scss_file()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create(storage_path('app/testing/assets/scss/modal.scss'));
        $params = [
            'custom_scss_file' => $file,
            'custom_scss_file_name' => 'modal.scss'
        ];
        $response = $this->post('style/update-style', $params, ['Authorization' => 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'))]);
        dd($response->response->getContent());
    }
}

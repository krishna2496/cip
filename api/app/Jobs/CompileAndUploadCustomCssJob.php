<?php

namespace App\Jobs;

use App\Helpers\S3Helper;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Support\Facades\Storage;
use ScssPhp\ScssPhp\Compiler;

class CompileAndUploadCustomCssJob extends Job
{
    use RestExceptionHandlerTrait;

    private const SCSS_PATH = '/assets/scss';
    private const COMPILED_STYLES_CSS_PATH = '/assets/css/style.css';

    /**
     * @var String
     */
    private $tenantName;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var array
     */
    private $options;

    /**
     * Create a new job instance.
     * @param String $tenantName
     * @param array $options
     * @return void
     */
    public function __construct(String $tenantName, array $options = [])
    {
        $this->tenantName = $tenantName;
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scss = new Compiler();
        $scssToCompilePath = realpath(storage_path() . '/app/' . $this->tenantName . self::SCSS_PATH);
        $scss->addImportPath($scssToCompilePath);

        $assetUrl = S3Helper::makeTenantS3BaseUrl($this->tenantName) . 'assets/images';

        $importScss = '@import "_variables";';

        // Check if a custom variables file as been used
        $tenantScssFolderName = $this->tenantName . self::SCSS_PATH;
        $hasNoCustomVariables = !Storage::disk('s3')->exists($tenantScssFolderName . '/_variables.scss');

        // Color set & other file || Color set & no file
        if ((isset($this->options['primary_color']) && $hasNoCustomVariables)) {
            $importScss .= '$primary: ' . $this->options['primary_color'] . ';';
        }

        $importScss .= '@import "_assets";
        $assetUrl: "'.$assetUrl.'";
        @import "' . base_path() . '/node_modules/bootstrap/scss/bootstrap";
        @import "' . base_path() . '/node_modules/bootstrap-vue/src/index";
        @import "custom";';

        $compiledCss = $scss->compile($importScss);

        // Push compiled styles to S3
        $tenantCompiledCssFolderName = $this->tenantName . self::COMPILED_STYLES_CSS_PATH;
        Storage::disk('s3')->put($tenantCompiledCssFolderName, $compiledCss);
    }
}

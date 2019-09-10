<?php
namespace App\Helpers;

class ExportCSV
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var array
     */
    protected $headLines = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * Create a new class instance.
     *
     * @param string $fileName
     * @return void
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }
    
    /**
     * Set headLine variable
     *
     * @param array $headLines
     * @return void
     */
    public function setHeadlines(array $headLines)
    {
        $this->headLines = $headLines;
    }

    /**
     * Push row into, data variable
     *
     * @param array $row
     * @return void
     */
    public function appendRow(array $row)
    {
        array_push($this->data, $row);
    }

    /**
     * Set data variable
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Write and store file on given path
     *
     * @param string $path
     * @return string
     */
    public function export(string $path): string
    {
        $this->path = \storage_path($path);

        // Make directory
        exec('mkdir '.\storage_path($this->path));

        // Create and open file from location
        $csv = fopen($this->path.'/'.$this->fileName, 'w');

        // Add Headings in file
        fputcsv($csv, $this->headLines);

        // Write rows into file
        fputcsv($csv, $this->data);
        
        fclose($csv);

        if (\file_exists($this->path)) {
            return $this->path.'/'.$this->fileName;
        }
        return '';
    }
}

<?php namespace App\Traits;

trait parsesCsv
{

    /**
     * @param $file_path
     * @return \Generator
     */
    public function readFile($file_path)
    {
        $handle = fopen($file_path, "r");
        while (!feof($handle)) {
            yield fgets($handle);
        }
        fclose($handle);
    }
}

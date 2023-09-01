<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow, WithCustomCsvSettings};

class LinioThirdImport implements ToArray,WithStartRow,WithCustomCsvSettings
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ";"
        ];
    }

    public function array(array $rows)
    {
        foreach ($rows as $row) {
            if(isset($row[10])){
                if($row[51] != 'failed' || $row[51] != 'canceled'){
                    $this->data[] = array('name' => $row[10], 'mail' => $row[11], 
                    'identification2' => $row[12], 'address' => $row[14],
                    'phone' => $row[19], 'phone2' => $row[20], 'city' => $row[21], 
                    'identification' => $row[64]);
                }
            }
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}

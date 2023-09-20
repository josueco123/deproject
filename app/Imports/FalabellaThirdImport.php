<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow,WithCustomCsvSettings};

class FalabellaThirdImport implements ToArray,WithStartRow,WithCustomCsvSettings
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
            if(isset($row[11])){
                $this->data[] = array('name' => $row[11], 'identification' => $row[12],
                'address' => $row[14], 'city' => $row[24], 'state' => $row[25],
                 'phone' => $row[32]);
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}

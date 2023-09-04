<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow};

class ExitoThirdImport implements ToArray,WithStartRow
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

    public function array(array $rows)
    {
        foreach ($rows as $row) {
            if(isset($row[6])){
                $this->data[] = array('name' => $row[6], 'identification' => $row[7],
                'address' => $row[8], 'city' => $row[9], 'phone' => $row[10], 'mail' => $row[11]);
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
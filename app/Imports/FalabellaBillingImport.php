<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow,WithCustomCsvSettings};

class FalabellaBillingImport implements ToArray,WithStartRow,WithCustomCsvSettings
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
            if(isset($row[6])){
                $this->data[] = array(
                    'code_orden' => strval($row[0].' '),
                    'sku' => $row[2], 
                    'created' => $row[4],
                    'updated' => $row[5],
                    'identification' => $row[12],
                    'quantity' => 1,
                    'unit_price'=> $row[41],
                    'type' => $row[49],
                    'status' => $row[59]);
            
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
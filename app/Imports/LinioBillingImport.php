<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow, WithCustomCsvSettings};

class LinioBillingImport implements ToArray,WithStartRow, WithCustomCsvSettings
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
                    $this->data[] = array(
                        'code_orden' => strval($row[6].' '),
                        'sku' => $row[2], 
                        'created' => $row[4],
                        'updated' => $row[5],
                        'identification2' => $row[12],
                        'identification' => $row[64],
                        'quantity' => 1,
                        'unit_price'=> $row[37],
                        'type' => $row[44],
                        'status' => $row[51]);
                }
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}

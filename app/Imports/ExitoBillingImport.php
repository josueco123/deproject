<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow};

class ExitoBillingImport implements ToArray,WithStartRow
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
                $this->data[] = array(
                    'code_orden' => strval($row[0].' '),
                    'sku' => $row[14], 
                    'created' => $row[4],
                    'updated' => $row[5],
                    'identification' => $row[8],
                    'quantity' => $row[18],
                    'unit_price'=> $row[16],
                    'type' => $row[23],
                    'status' => $row[19]);
            
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
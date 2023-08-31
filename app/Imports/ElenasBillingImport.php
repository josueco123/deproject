<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow};

class ElenasBillingImport implements ToArray,WithStartRow
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
            if(isset($row[2])){
                $this->data[] = array('code_orden' => strval($row[0].' '), 'quantity' => $row[2], 
                'date' => $row[3],'sku' => $row[4], 'unit_price'=> $row[5], 'name' => $row[6],
                'identification' => $row[7]);
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}

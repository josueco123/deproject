<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow,WithValidation};

class MercadoLibreBillingImport implements ToArray,WithStartRow,WithValidation
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function startRow(): int
    {
        return 4;
    }

    public function array(array $rows)
    {
        foreach ($rows as $row) {
            if (intval($row[11]) > 0){
                $this->data[] = array( 'code_orden' => strval($row[0].' '),
                'unities' => $row[5],'unit_price' => $row[18], 'total' => intval($row[11]),
                'sku' => $row[13], 'title' => $row[16] ,'name' => $row[25],'identification' => $row[26]);
            }
            
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }

    public function rules(): array{
        return [
          '5' => 'required',
          '13' => 'required',
          '18' => 'required',
          '16' => 'required',
          '26' => 'required'
        ];
      }
}

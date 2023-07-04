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
            $this->data[] = array('unities' => $row[5], 'total' => $row[18] ,'sku' => $row[13],
             'title' => $row[16] ,'identification' => $row[26]);
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

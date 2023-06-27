<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToArray,WithStartRow,WithValidation,WithProgressBar};

class MercadoLibreImport implements ToArray,WithStartRow,WithValidation
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
            $this->data[] = array('name' => $row[25], 'identification' => $row[26],
             'address' => $row[27], 'city' => $row[28], 'estate' => $row[29]);
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }

    public function rules(): array{
        return [
          '25' => 'required',
          '26' => 'required',
          '27' => 'required',
          '28' => 'required',
          '29' => 'required'
        ];
      }
}

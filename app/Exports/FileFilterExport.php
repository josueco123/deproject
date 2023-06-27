<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class FileFilterExport implements FromArray
{
  
  protected $file_list;

  public function __construct(array $file_list)
  {
    $this->file_list = $file_list;
  }

  public function array(): array
  {
      return $this->file_list;
  }
}
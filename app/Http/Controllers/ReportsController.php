<?php

namespace App\Http\Controllers;


use App\Models\Cities;
use App\Models\Departments;
use Illuminate\Http\Request;
use App\Exports\FileFilterExport;
use App\Imports\MercadoLibreImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReportsController extends Controller
{
    //
    public function loadUploadThirdsView()
    {
        return view('reports.uploadthirds');
    }

    public function getDataToImportML(Request $request)
    {
        $request->validate([
            'file_input' => 'required'
          ]);

        $file = Storage::putFile('mr_import', $request->file('file_input'));
        $import = new MercadoLibreImport();
        Excel::import($import, $file);
  
        $data = $import->getArray();

        $result = $this->filterDataMl($data);
        return Excel::download(new FileFilterExport($result), "Mercado Libre Terceros".date("Y-m-d H:i:s").'.xlsx');
    }

    private function separteName($fullname)
    {
        $arrayName = [];

        if(substr_count($fullname,' ') == 3)
        {
            $arrayPre = explode(" ", $fullname);
            $arrayName = [$arrayPre[0]." ". $arrayPre[1], $arrayPre[2]." ". $arrayPre[3]];

        } else{
            $arrayName = explode(" ", $fullname, 2);
        }
        
        
        return $arrayName;
    }

    private function filterDataMl($data)
    {
        $arrayMl = [];
        $headers = [
        "Identificacion", 
        "Dígito de verificación", 
        "Código Sucursal", 
        "Tipo identificación", 
        "Tipo", "Razón social", 
        "Nombres del tercero", 
        "Apellidos del tercero", 
        "Nombre Comercial",
        "Dirección",
        "Código pais", 
        "Código departamento/estado", 
        "Código ciudad",
        "Indicativo teléfono principal",
        "Teléfono principal", 
        "Extensión teléfono principal", 
        "Tipo de régimen IVA", 
        "Código Responsabilidad fiscal",
        "Código Postal", 
        "Nombres contacto principal",
        "Apellidos contacto principal",
        "Indicativo teléfono contacto principal",
        "Teléfono contacto principal",
        "Extensión teléfono contacto principal",
        "Correo electrónico contacto principal",
        "Identificación del cobrador",
        "Identificación del vendedor",
        "Otros",
        "Clientes",
        "Proveedor",
        "Estado",
        ];
        array_push($arrayMl, $headers);

        $arrayTem = [];
        foreach ($data as $third){

            array_push($arrayTem, $third['identification']);
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "13");
            array_push($arrayTem, "Es persona");
            array_push($arrayTem, "");
            $arrayName = $this->separteName($third['name']);
            array_push($arrayTem, $arrayName[0] );
            array_push($arrayTem, $arrayName[1]);
            array_push($arrayTem, "");
            array_push($arrayTem, $third['address']);
            array_push($arrayTem, "CO");
            $department_id = Departments::getDepartmentCode($third['estate']);
            array_push($arrayTem, $department_id);
            if($department_id == 11){
                array_push($arrayTem, 11001);

            }else {
                $city = Cities::getCity($third['city'],$department_id);
                if(empty($city)){
                    array_push($arrayTem, $department_id.'001');
                }else{
                    array_push($arrayTem, $city->codigo);
                }
                
            }
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "0 - No responsable de IVA");
            array_push($arrayTem, "R-99-PN");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "6023798287");
            array_push($arrayTem, "NOAPLICAFAC@GMAIL.COM");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "");
            array_push($arrayTem, "NO");
            array_push($arrayTem, "Activo");
            
            if(!in_array($arrayTem, $arrayMl)){
                array_push($arrayMl, $arrayTem);
            }
            
            $arrayTem = [];
        }

        return $arrayMl;
    }
}

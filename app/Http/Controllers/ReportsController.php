<?php

namespace App\Http\Controllers;


use App\Models\Cities;
use App\Models\Departments;
use Illuminate\Http\Request;
use App\Exports\FileFilterExport;
use App\Imports\MercadoLibreImport;
use App\Imports\MercadoLibreBillingImport;
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

    public function loadUploadBillingView()
    {
        return view('reports.uploadfact');
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

    public function getDataToImportBillML(Request $request)
    {
        $request->validate([
            'file_input' => 'required'
          ]);

        $file = Storage::putFile('mr_import', $request->file('file_input'));
        $import = new MercadoLibreBillingImport();
        Excel::import($import, $file);
  
        $data = $import->getArray();

        $result = $this->filerDataFact($data);
        return Excel::download(new FileFilterExport($result), "Mercado Libre Facturacion".date("Y-m-d H:i:s").'.xlsx');
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

        $arrayTemp = [];
        $arrayEmp = [];
        $arrayPers = [];
        foreach ($data as $third){
            $company = $this->isCompany($third['name']);
            
            if($company){
                array_push($arrayTemp, $third['identification']);
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "31");
                array_push($arrayTemp, "Es empresa");
                array_push($arrayTemp, $third['name']);
                array_push($arrayTemp, "" );
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                $arrayAddress = explode("/",$third['address']);
                array_push($arrayTemp, $arrayAddress[0] );
                array_push($arrayTemp, "CO");
                $department_id = Departments::getDepartmentCode($third['estate']);
                array_push($arrayTemp, $department_id);
                if($department_id == 11){
                    array_push($arrayTemp, 11001);

                }else {
                    $city = Cities::getCity($third['city'],$department_id);
                    if(empty($city)){
                        array_push($arrayTemp, $department_id.'001');
                    }else{
                        array_push($arrayTemp, $city->codigo);
                    }
                    
                }
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "2 - Responsable de IVA");
                array_push($arrayTemp, "R-99-PN");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "6023798287");
                array_push($arrayTemp, "NOAPLICAFAC@GMAIL.COM");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "NO");
                array_push($arrayTemp, "Activo");
                
                if(!in_array($arrayTemp, $arrayEmp)){
                    array_push($arrayEmp, $arrayTemp);
                }
                
                $arrayTemp = [];

            }else{
                array_push($arrayTemp, $third['identification']);
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "13");
                array_push($arrayTemp, "Es persona");
                array_push($arrayTemp, "");
                $arrayName = $this->separteName($third['name']);
                array_push($arrayTemp, $arrayName[0] );
                array_push($arrayTemp, $arrayName[1]);
                array_push($arrayTemp, "");
                $arrayAddress = explode("/",$third['address']);
                array_push($arrayTemp, $arrayAddress[0] );
                array_push($arrayTemp, "CO");
                $department_id = Departments::getDepartmentCode($third['estate']);
                array_push($arrayTemp, $department_id);
                if($department_id == 11){
                    array_push($arrayTemp, 11001);

                }else {
                    $city = Cities::getCity($third['city'],$department_id);
                    if(empty($city)){
                        array_push($arrayTemp, $department_id.'001');
                    }else{
                        array_push($arrayTemp, $city->codigo);
                    }
                    
                }
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "0 - No responsable de IVA");
                array_push($arrayTemp, "R-99-PN");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "6023798287");
                array_push($arrayTemp, "NOAPLICAFAC@GMAIL.COM");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "");
                array_push($arrayTemp, "NO");
                array_push($arrayTemp, "Activo");
                
                if(!in_array($arrayTemp, $arrayPers)){
                    array_push($arrayPers, $arrayTemp);
                }
                
                $arrayTemp = [];

                }
            
        }
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;
    }

    public function isCompany($name){
        $pattern = "/\b(SAS|LTDA|GRUPO|INVERSIONES|EDIFICIO|SISTEMAS|CLINICA|SERVICIOS|.COM|EQUIPOS|S.A.S.|L.T.D.A.|GROUP|EQUIPO|\d+)/i";
        $result = preg_match($pattern,$name) == 1 ? true : false;
        return $result;
        
    }

    public function filerDataFact($data)
    {
        $arrayMl = [];
        $headers = [
        "Tipo de comprobante", 
        "Consecutivo", 
        "Identificación tercero",
        "Sucursal", 
        "Código centro/subcentro de costos", 
        "Fecha de elaboración", 
        "Sigla Moneda",
        "Tasa de cambio",
        "Nombre contacto", 
        "Email Contacto", 
        "Orden de compra",
        "Orden de entrega",
        "Fecha orden de entrega", 
        "Código producto", 
        "Descripción producto", 
        "Identificación vendedor",
        "Código de Bodega", 
        "Cantidad producto",
        "Valor unitario",
        "Valor Descuento",
        "Base AIU",
        "Identificación ingreso para terceros",
        "Código impuesto cargo",
        "Código impuesto cargo dos",
        "Código impuesto retención",
        "Código ReteICA",
        "Código ReteIVA",
        "Código forma de pago",
        "Valor Forma de Pago",
        "Fecha Vencimiento",
        "Observaciones"
        ];
        array_push($arrayMl, $headers);

        $arrayTemp = [];
        foreach ($data as $bill){
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, $bill['identification']);
            array_push($arrayTemp,"");
            array_push($arrayTemp,"");
            array_push($arrayTemp,date("d/m/Y"));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, $bill['title']. " ".$bill['sku']);
            array_push($arrayTemp, "901284706");
            array_push($arrayTemp, "02");
            array_push($arrayTemp, $bill['unities']);
            array_push($arrayTemp, $this->getUnitValue($bill['total']));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "10");
            $total = intval($bill['unities']) * intval($bill['total']);
            array_push($arrayTemp,  $total);
            $d=strtotime("+30 Days");
            array_push($arrayTemp,date("d/m/Y", $d));
            array_push($arrayTemp, "");

            array_push($arrayMl, $arrayTemp);

            $arrayTemp = [];
        }
        return $arrayMl;
    }

    public function getUnitValue($value)
    {
        $number = intval($value);
        $result = $number - ($number * 0.19);
        return $result;
    }

}

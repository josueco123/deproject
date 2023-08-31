<?php

namespace App\Http\Controllers;


use App\Models\Cities;
use App\Models\Products;
use App\Models\Departments;
use Illuminate\Http\Request;
use App\Exports\FileFilterExport;
use App\Imports\ElenasThirdImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ElenasReportController extends Controller
{
    private function filterDataElenas($data)
    {
        $arrayMl = [];
        $headers = [
        "Identificación", 
        "Dígito de verificación", 
        "Código Sucursal", 
        "Tipo identificación", 
        "Tipo", "Razón social", 
        "Nombres del tercero", 
        "Apellidos del tercero", 
        "Nombre Comercial",
        "Dirección",
        "Código país", 
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

            $embajador = explode("|",$third['name']);
            $name = $embajador[1];
           
                $company = $this->isCompany($name);

                if($company){
                    if(!$this->idInData($arrayEmp, $third['identification']))
                    {
                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "31");
                        array_push($arrayTemp, "Empresa");
                        array_push($arrayTemp, strtoupper($name));
                        array_push($arrayTemp, "" );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        $adress = $this->setAdress($third['address']);
                        array_push($arrayTemp, $addres);
                        array_push($arrayTemp, "Co");
                        $city = Cities::getCityByName($third['city']);
                        $deparmentCode = Departments::getDepartmentCodeById($city->departamento_id);
                        array_push($arrayTemp, $deparmentCode);
                        array_push($arrayTemp, $city->codigo);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "2 - Responsable de IVA");
                        array_push($arrayTemp, "R-99-PN");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['phone']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['mail']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "NO");
                        array_push($arrayTemp, "Activo");

                        array_push($arrayEmp, $arrayTemp);
                    }
                    
                    $arrayTemp = [];
    
                }else{

                    if(!$this->idInData($arrayPers, $third['identification'])){

                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "13");
                        array_push($arrayTemp, "Es persona");
                        array_push($arrayTemp, "");
                        $arrayName = $this->separteName($name);
                        array_push($arrayTemp, strtoupper($arrayName[0]));
                        array_push($arrayTemp, strtoupper($arrayName[1]));
                        array_push($arrayTemp, "");
                        $adress = $this->setAdress($third['address']);
                        array_push($arrayTemp, $adress);
                        array_push($arrayTemp, "Co");
                        $city = Cities::getCityByName($third['city']);
                        $deparmentCode = Departments::getDepartmentCodeById($city->departamento_id);
                        array_push($arrayTemp, $deparmentCode);
                        array_push($arrayTemp, $city->codigo);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "0 - No responsable de IVA");
                        array_push($arrayTemp, "R-99-PN");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['phone']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['mail']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "NO");
                        array_push($arrayTemp, "Activo");

                        array_push($arrayPers, $arrayTemp);
                    }
                    
                    $arrayTemp = [];
    
                }
            
            
        }
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;
    }

    public function isCompany($name){
        $pattern = "/\b(SAS|LTDA|GRUPO|INVERSIONES|EDIFICIO|SISTEMAS|CLINICA|SERVICIOS|.COM|EQUIPOS|S.A.S.|L.T.D.A.|GROUP|EQUIPO|ESTUDIO|CONSTRUCTORA|COMUNICACIONES|DISTRIBUCIONES|INGENIERIA|CLUB|PARROQUIA|\d+)/i";
        $result = preg_match($pattern,$name) == 1 ? true : false;

        if(str_word_count($name) == 1){
            $result = true;
        }
        return $result;
        
    }

    public function separteName($fullname)
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

    public function setAdress($adress)
    {
        if(Empty($adress) || !preg_match('/\d/',$adress)){
            return 'Carrera 56 #18A-80';
        } else {
            return $adress;
        }
    }

}
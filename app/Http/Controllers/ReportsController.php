<?php

namespace App\Http\Controllers;


use App\Models\Cities;
use App\Models\Products;
use App\Models\Departments;
use Illuminate\Http\Request;
use App\Exports\FileFilterExport;
use App\Imports\MercadoLibreImport;
use App\Imports\MercadoLibreBillingImport;
use App\Imports\ElenasThirdImport;
use App\Imports\ElenasBillingImport;
use App\Imports\LinioThirdImport;
use App\Imports\LinioBillingImport;
use App\Imports\FalabellaThirdImport;
use App\Imports\FalabellaBillingImport;
use App\Imports\ExitoThirdImport;
use App\Imports\ExitoBillingImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;

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
            'file_input' => 'required',
            'selectstore' => 'required'
          ]);
       
            $file = Storage::putFile('mr_import', $request->file('file_input'));//
            $selectStore = $request->input('selectstore');
            $fileName = "";

            switch ($selectStore) {
                case '1':
                    $import = new MercadoLibreImport();
                    break;
                case '2':
                    $import = new ElenasThirdImport();
                    break;  
                case '3':
                    $import = new LinioThirdImport();
                    break; 
                case '4':
                    $import = new FalabellaThirdImport();
                    break; 
                case '5':
                    $import = new ExitoThirdImport();
                    break; 
            }
           
            
           try{
                Excel::import($import, $file);
                $data = $import->getArray();

                switch ($selectStore) {
                    case '1':
                        $result = $this->filterDataMl($data);
                        $fileName = "Mercado Libre";
                        break;
                    case '2':
                        $result = $this->filterDataElenas($data);
                        $fileName = "Elenas";
                        break; 
                    case '3':
                        $result = $this->filterDataLinio($data);
                        $fileName = "Linio";
                        break;
                    case '4':
                        $result = $this->filterDataFallabela($data);
                        $fileName = "Fallabela";
                        break;
                    case '5':
                        $result = $this->filterDataExito($data);
                        $fileName = "Exito";
                        break;   
                }

            } catch(ValidationException $e){
                return redirect()->back()->withErrors($e->errors())->withInput();
            } catch (NoTypeDetectedException $e) {
                return redirect()->back()->with('error', '¡Ocurrió un error! No subas archivos comprimidos, usa solo formatos de excel (.xls, .xlsx, .csv) ');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', '¡Ocurrió un error durante la importación! Por favor verifica que subiste el archivo de la tienda selecionada msg: '. $e->getMessage());
            }
    
            return Excel::download(new FileFilterExport($result), $fileName. " Terceros ".date("Y-m-d H:i:s").'.xlsx');  
        
    }

    public function getDataToImportBillML(Request $request)
    {
        $request->validate([
            'file_input' => 'required',
            'selectstore' => 'required'
          ]);

        $codbodega = $request->input('codbodega');
        $file = Storage::putFile('mr_import', $request->file('file_input'));

        $selectStore = $request->input('selectstore');

        switch ($selectStore) {
            case '1':
                $import = new MercadoLibreBillingImport();
                break;
            case '2':
                $import = new ElenasBillingImport();
                break; 
            case '3':
                $import = new LinioBillingImport();
                break;  
            case '4':
                $import = new FalabellaBillingImport();
                break;
            case '5':
                $import = new ExitoBillingImport();
                break; 
        }
        try{
            Excel::import($import, $file);
            $data = $import->getArray();

            switch ($selectStore) {
                case '1':
                    $result = $this->filerDataFact($data,$codbodega);
                    $fileName = "Mercado Libre";
                    break;
                case '2':
                    $result = $this->filerDataFactElenas($data);
                    $fileName = "Elenas";
                    break;
                case '3':
                    $result = $this->filerDataFactLinio($data);
                    $fileName = "Linio";
                    break;  
                case '4':
                    $result = $this->filerDataFactFallabela($data);
                    $fileName = "Fallabela";
                    break;
                case '5':
                    $result = $this->filerDataFactExito($data);
                    $fileName = "Exito";
                    break;  
                    
            }
            
        } catch(ValidationException $e){
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (NoTypeDetectedException $e) {
            return redirect()->back()->with('error', '¡Ocurrió un error! No subas archivos comprimidos, usa solo formatos de excel (.xls, .xlsx, .csv) ');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '¡Ocurrió un error durante la importación! Por favor verifica que subiste el archivo de la tienda selecionada msg: '. $e->getMessage());
        }

        return Excel::download(new FileFilterExport($result), $fileName. " Facturacion ".date("Y-m-d H:i:s").'.xlsx');
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

    public function isCompany($name){

        if(str_word_count($name) == 1){
            return true;
        }

        $pattern = "/\b(SAS|LTDA|GRUPO|INVERSIONES|EDIFICIO|SISTEMAS|CLINICA|SERVICIOS|.COM|EQUIPOS|S.A.S.|L.T.D.A.|GROUP|EQUIPO|ESTUDIO|CONSTRUCTORA|COMUNICACIONES|DISTRIBUCIONES|INGENIERIA|CLUB|PARROQUIA|S.A.S|CORPORACIÓN|\d+)/i";
        $result = preg_match($pattern,$name) == 1 ? true : false;

        return $result;
    }

    public function getUnitValue($value)
    {
        $number = floatval($value) / 1.19;
        $result = round($number,2);
        return $result;
    }

    public function calulateTotal($unit,$cant)
    {
        $quantity = intval($cant);
        $value = $unit * 1.19 * $quantity;
        $total = round($value, 2);
        return $total;
    }

    public function idInData($array,$identification)
    {
        $inArray = false;
        foreach ($array as $data) {
            if ($data['0'] == $identification) {
                $inArray = true;
            }
        }

        return $inArray;
    }

    public function setAdress($adress)
    {
        if(Empty($adress) || !preg_match('/\d/',$adress)){
            return 'Carrera 56 #18A-80';
        } else {
            return $adress;
        }
    }

    public function getCityExito($addres)
    {
        $addres = str_replace("|", "",$addres);
        $addres = str_replace("D.C.", "",$addres);
        $addres = str_replace("  ", " ",$addres);
        $palabras = explode(' ', $addres); 
        $palabrasInvertidas = array_reverse($palabras); 
        return $palabrasInvertidas;
    }

    public function setAddresExito($addres)
    {
        $addres = str_replace("|", "",$addres);
        $addres = str_replace("D.C.", "",$addres);
        $addres = str_replace("  ", " ",$addres);
        $palabras = explode(' ', $addres); 
       
        $miArraySinUltimasDos = array_slice($palabras, 0, -4);
        $restul = implode(" ", $miArraySinUltimasDos);

        $restul = strlen($restul) > 50 ? substr($restul, 0, 49) : $restul;

        return $restul;
    }


    private function filterDataMl($data)
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

            if ($third['name'] != " ")
            {
                $company = $this->isCompany($third['name']);

                if($company){
                    if(!$this->idInData($arrayEmp, $third['identification']))
                    {
                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "31");
                        array_push($arrayTemp, "Empresa");
                        array_push($arrayTemp, strtoupper($third['name']));
                        array_push($arrayTemp, "" );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        $arrayAddress = explode("/",$third['address']);
                        array_push($arrayTemp, $arrayAddress[0] );
                        array_push($arrayTemp, "Co");
                        $department_id = Departments::getDepartmentCode($third['estate']);
                        array_push($arrayTemp, strval($department_id));
                        if($department_id == "11"){
                            array_push($arrayTemp, 11001);
        
                        }else {
                            $city = Cities::getCity($third['city'],$department_id);
                            if(empty($city)){
                                array_push($arrayTemp, $department_id.'001');
                            }else{
                                if($department_id == "05" || $department_id == "08"){
                                    array_push($arrayTemp, '0'.$city->codigo);
                                }else {
                                    array_push($arrayTemp, strval($city->codigo));
                                }
                                
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
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "NOAPLICAFAC@GMAIL.COM");
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
                        $arrayName = $this->separteName($third['name']);
                        array_push($arrayTemp, strtoupper($arrayName[0]));
                        $lastName = isset($arrayName[1]) ?strtoupper($arrayName[1]) : '';
                        array_push($arrayTemp, strtoupper($lastName));
                        array_push($arrayTemp, "");
                        $arrayAddress = explode("/",$third['address']);
                        array_push($arrayTemp, $arrayAddress[0] );
                        array_push($arrayTemp, "Co");
                        $department_id = Departments::getDepartmentCode($third['estate']);
                        array_push($arrayTemp, strval($department_id));
                        if($department_id == "11"){
                            array_push($arrayTemp, 11001);
        
                        }else {
                            $city = Cities::getCity($third['city'],$department_id);
                            if(empty($city)){
                                array_push($arrayTemp, $department_id.'001');
                            }else{
                                if($department_id == "05" || $department_id == "08"){
                                    array_push($arrayTemp, '0'.$city->codigo);
                                }else {
                                    array_push($arrayTemp, strval($city->codigo));
                                }
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
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "NOAPLICAFAC@GMAIL.COM");
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
            
        }
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;
    }

   
    public function filerDataFact($data,$codbodega)
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
            $cantidad = 0;

            if(str_contains($bill['sku'],'SILLAEAMES')){
                    $arraySilla = explode("X",$bill['sku']);
                    $cantidad = intval($arraySilla[1]);
            }elseif(str_contains($bill['sku'],'SILLAS 4005 X')){
                    $arraySilla = explode("X",$bill['sku']);
                    $cantidad = intval($arraySilla[1]);
            }
            
            $unities = $cantidad > 0 ? $cantidad : $bill['unities'];
            $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
            $priceUnit = $this->getUnitValue($priceUnity);

            $total = $this->calulateTotal($priceUnit,$unities);
            

            $comprobante = $total > 212000 ? 2 : 1;
            array_push($arrayTemp, $comprobante);
            array_push($arrayTemp, "");
            array_push($arrayTemp,  $bill['identification']);
            array_push($arrayTemp,"");
            array_push($arrayTemp,"5-1");
            array_push($arrayTemp,date("d/m/Y"));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");

            if(($bill['unities'] != '')){

                if(str_contains($bill['sku'],'SILLAEAMES')){

                    $product = Products::getProduct('SILLAEAMES');
                    array_push($arrayTemp, $product->code);
    
                }elseif(str_contains($bill['sku'],'SILLAS 4005 X')){
                    $product = Products::getProduct('SILLA4005ENSAMBLADA');
                    array_push($arrayTemp, $product->code);

                }else{
                    $product = Products::getProduct($bill['sku']);
                    if($product == false){
                        array_push($arrayTemp, 'No encontrado');
                    }else{
                        array_push($arrayTemp, $product->code);
                    }
                   
                }
                $productName = $product != false ? $product->name : $bill['sku'] ;
                array_push($arrayTemp, $productName);
            }else{
                array_push($arrayTemp, "");
                array_push($arrayTemp, "Factura Agrupada");
            }
            
            
   
           $unities = $cantidad > 0 ? $cantidad : $bill['unities'];
            $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
            $priceUnit = $this->getUnitValue($priceUnity);

            $total = $this->calulateTotal($priceUnit,$unities);
            
            array_push($arrayTemp, "1144105658");
            array_push($arrayTemp, $codbodega);
            
            array_push($arrayTemp, $unities);
           
            array_push($arrayTemp, $priceUnit);
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "05");
            
            array_push($arrayTemp,  $total);
            $d=strtotime("+30 Days");
            array_push($arrayTemp,date("d/m/Y", $d));
            if($bill['total'] == 0){
                array_push($arrayTemp, $bill['code_orden']. " Pertenece a compra anterior");
            }else {
                array_push($arrayTemp, $bill['code_orden']);
            }
            

            array_push($arrayMl, $arrayTemp);

            $arrayTemp = [];
        }
        return $arrayMl;
    } 

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

            $embajador = explode("| ",$third['name']);
            $name = str_replace("  ", " ",$embajador[1]);
           
                $company = $this->isCompany($name);

                if($company){
                    if(!$this->idInData($arrayEmp, $third['identification']))
                    {
                        if($third['identification'] == '0' || $third['identification'] == ""){
                            $ident = $embajador[0];
                        }else {
                            $ident = $third['identification'];
                        }
                        
                        array_push($arrayTemp, $ident);
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
                        if($city->department_id == "05" || $city->department_id == "08"){
                            array_push($arrayTemp, '0'.$city->codigo);
                        }else {
                            array_push($arrayTemp, strval($city->codigo));
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
                        $phone = $third['phone'] != '' ? $third['phone'] : '6023798287';
                        array_push($arrayTemp, $phone);
                        array_push($arrayTemp, "");
                        $mail = $third['mail'] != '' ? $third['mail'] : 'NOAPLICAFAC@GMAIL.COM';
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

                        if($third['identification'] == '0' || $third['identification'] == ""){
                            $ident = $embajador[0];
                        }else {
                            $ident = $third['identification'];
                        }

                        array_push($arrayTemp, $ident);
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
                        if($city == false){
                            array_push($arrayTemp, 'departamaneto no encontrado');
                            array_push($arrayTemp, 'ciudad no encontrada');
                        }else {
                            $deparmentCode = Departments::getDepartmentByCityCode($city->departamento_id);
                            array_push($arrayTemp, $deparmentCode);
                            if($city->department_id == "05" || $city->department_id == "08"){
                                array_push($arrayTemp, '0'.$city->codigo);
                            }else {
                                array_push($arrayTemp, strval($city->codigo));
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

    public function filerDataFactElenas($data)
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
            $cantidad = 0;
            $product = false;
            
            if(str_contains($bill['sku'],'SILLAEAMES')){

                $product = Products::getProduct('SILLAEAMES');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }elseif(str_contains($bill['sku'],'4005X')){
                $product = Products::getProduct('SILLA4005ENSAMBLADA');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }else{
                $product = Products::getProduct($bill['sku']);  
            }

            
           $unities = $cantidad > 0 ? $cantidad : $bill['quantity'];
           $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
           $priceUnit = $this->getUnitValue($priceUnity);

           $total = $this->calulateTotal($priceUnit,$unities);

            $comprobante = $total > 212000 ? 2 : 1;
            array_push($arrayTemp, $comprobante);
            array_push($arrayTemp, "");

            $embajador = explode("| ",$bill['name']);

            if($bill['identification'] == '0' || $bill['identification'] == ""){
                $ident = $embajador[0];
            }else {
                $ident = $bill['identification'];
            }
            array_push($arrayTemp,  $ident);
            array_push($arrayTemp,"");
            array_push($arrayTemp,"9-1");
            array_push($arrayTemp,date("d/m/Y"));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");

            if($product == false){
                array_push($arrayTemp, 'No encontrado');
                array_push($arrayTemp, $bill['sku']);
            }else {
                array_push($arrayTemp, $product->code);
                array_push($arrayTemp, $product->name);
            }

            
            array_push($arrayTemp, "1144105658");
            array_push($arrayTemp, "01");
            
            array_push($arrayTemp, $unities);
           
            array_push($arrayTemp, $priceUnit);
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "09");
            
            array_push($arrayTemp,  $total);
            $d=strtotime("+30 Days");
            array_push($arrayTemp,date("d/m/Y", $d));
            array_push($arrayTemp, $bill['code_orden']);

            array_push($arrayMl, $arrayTemp);

            $arrayTemp = [];
        }
        return $arrayMl;
    }

    private function filterDataLinio($data)
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

            if ($third['name'] != " ")
            {
                $company = $this->isCompany($third['name']);
                $ident = $third['identification'] != '' ? $third['identification'] : $third['identification2'];
                if($company){
                    if(!$this->idInData($arrayEmp, $ident ))
                    {
                        
                        array_push($arrayTemp, $ident);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "31");
                        array_push($arrayTemp, "Empresa");
                        array_push($arrayTemp, strtoupper($third['name']));
                        array_push($arrayTemp, "" );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['address'] );
                        array_push($arrayTemp, "Co");
                        $location = explode(",",$third['city']);
                        $department_id = Departments::getDepartmentCode(trim($location[1]));
                        if($department_id != false){
                            array_push($arrayTemp, strval($department_id));
                            if($department_id == "11"){
                                array_push($arrayTemp, 11001);
            
                            }else {
                                $city = Cities::getCity(trim($location[0]),$department_id);
                                if(empty($city)){
                                    array_push($arrayTemp, $department_id.'001');
                                }else{
                                    if($department_id == "05" || $department_id == "08"){
                                        array_push($arrayTemp, '0'.$city->codigo);
                                    }else {
                                        array_push($arrayTemp, strval($city->codigo));
                                    }
                                }
                                
                            }
                        }else{
                            array_push($arrayTemp, $location[1]);
                            array_push($arrayTemp, $location[0]);
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
                        $phone = $third['phone'] != '' ? $third['phone'] : $third['phone2'];
                        array_push($arrayTemp, $phone);
                        array_push($arrayTemp, "");
                        $mail = $third['mail'] != '' ? $third['mail'] : 'NOAPLICAFAC@GMAIL.COM';
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

                    if(!$this->idInData($arrayPers, $ident )){

                        array_push($arrayTemp, $ident );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "13");
                        array_push($arrayTemp, "Es persona");
                        array_push($arrayTemp, "");
                        $arrayName = $this->separteName($third['name']);
                        array_push($arrayTemp, strtoupper($arrayName[0]));
                        array_push($arrayTemp, strtoupper($arrayName[1]));
                        array_push($arrayTemp, "");
                        array_push($arrayTemp,$third['address'] );
                        array_push($arrayTemp, "Co");
                        $location = explode(",",$third['city']);
                        $department_id = Departments::getDepartmentCode(trim($location[1]));
                        if($department_id != false){
                            array_push($arrayTemp, strval($department_id));
                            if($department_id == "11"){
                                array_push($arrayTemp, 11001);
            
                            }else {
                                $city = Cities::getCity(trim($location[0]),$department_id);
                                if(empty($city)){
                                    array_push($arrayTemp, $department_id.'001');
                                }else{
                                    if($department_id == "05" || $department_id == "08"){
                                        array_push($arrayTemp, '0'.$city->codigo);
                                    }else {
                                        array_push($arrayTemp, strval($city->codigo));
                                    }
                                }
                                
                            }
                        }else{
                            array_push($arrayTemp, $location[1]);
                            array_push($arrayTemp, $location[0]);
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
                        $phone = $third['phone'] != '' ? $third['phone'] : $third['phone2'];
                        array_push($arrayTemp, $phone);
                        array_push($arrayTemp, "");
                        $mail = $third['mail'] != '' ? $third['mail'] : 'NOAPLICAFAC@GMAIL.COM';
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
            
        }
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;
    }

    public function filerDataFactLinio($data)
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
            $cantidad = 0;
            $product = false;

            $ident = $bill['identification'] != '' ? $bill['identification'] : $bill['identification2'];
            
            if(str_contains($bill['sku'],'SILLAEAMES')){

                $product = Products::getProduct('SILLAEAMES');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }elseif(str_contains($bill['sku'],'4005X')){
                $product = Products::getProduct('SILLA4005ENSAMBLADA');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }else{
                $product = Products::getProduct($bill['sku']);  
            }

            
           $unities = $cantidad > 0 ? $cantidad : 1;
           $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
           $priceUnit = $this->getUnitValue($priceUnity);

           $total = $this->calulateTotal($priceUnit,$unities);

            $comprobante = $total > 212000 ? 2 : 1;
            array_push($arrayTemp, $comprobante);
            array_push($arrayTemp, "");

            array_push($arrayTemp,  $ident);
            array_push($arrayTemp,"");
            array_push($arrayTemp,"2-1");
            array_push($arrayTemp,date("d/m/Y"));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");

            if($product == false){
                array_push($arrayTemp, 'No encontrado');
                array_push($arrayTemp, $bill['sku']);
            }else {
                array_push($arrayTemp, $product->code);
                array_push($arrayTemp, $product->name);
            }

            
            array_push($arrayTemp, "1144105658");

            $codbodega = $bill['type'] == "Dropshipping" ? "03" : "01";
            array_push($arrayTemp, $codbodega);
            
            array_push($arrayTemp, $unities);
           
            array_push($arrayTemp, $priceUnit);
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "02");
            
            array_push($arrayTemp,  $total);
            $d=strtotime("+30 Days");
            array_push($arrayTemp,date("d/m/Y", $d));
            array_push($arrayTemp, $bill['code_orden']);
            array_push($arrayTemp, $bill['status'] . ' - ' . $bill['created']  . ' - ' . $bill['updated']);

            array_push($arrayMl, $arrayTemp);

            $arrayTemp = [];
        }
        return $arrayMl;
    }

    private function filterDataFallabela($data)
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

            if ($third['name'] != " ")
            {
                $company = $this->isCompany($third['name']);
              
                if($company){
                    if(!$this->idInData($arrayEmp, $third['identification'] ))
                    {
                        
                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "31");
                        array_push($arrayTemp, "Empresa");
                        array_push($arrayTemp, strtoupper($third['name']));
                        array_push($arrayTemp, "" );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['address'] );
                        array_push($arrayTemp, "Co");
                        $department_id = Departments::getDepartmentCode($third['state']);
                        if($department_id != false){
                            array_push($arrayTemp, strval($department_id));
                            if($department_id == "11"){
                                array_push($arrayTemp, 11001);
            
                            }else {
                                $city = Cities::getCity($third['city'],$department_id);
                                if(empty($city)){
                                    array_push($arrayTemp, $department_id.'001');
                                }else{
                                    if($department_id == "05" || $department_id == "08"){
                                        array_push($arrayTemp, '0'.$city->codigo);
                                    }else {
                                        array_push($arrayTemp, strval($city->codigo));
                                    }
                                }
                                
                            }
                        }else{
                            array_push($arrayTemp, $third['state']);
                            array_push($arrayTemp, $third['city']);
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
                        $phone = $third['phone'] != '' ? $third['phone'] : "6023798287";
                        array_push($arrayTemp, $phone);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, 'NOAPLICAFAC@GMAIL.COM');
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

                    if(!$this->idInData($arrayPers, $third['identification'] )){

                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "13");
                        array_push($arrayTemp, "Es persona");
                        array_push($arrayTemp, "");
                        $arrayName = $this->separteName($third['name']);
                        array_push($arrayTemp, strtoupper($arrayName[0]));
                        array_push($arrayTemp, strtoupper($arrayName[1]));
                        array_push($arrayTemp, "");
                        array_push($arrayTemp,$third['address'] );
                        array_push($arrayTemp, "Co");
                        $department_id = Departments::getDepartmentCode($third['state']);
                        if($department_id != false){
                            array_push($arrayTemp, strval($department_id));
                            if($department_id == "11"){
                                array_push($arrayTemp, 11001);
            
                            }else {
                                $city = Cities::getCity($third['city'],$department_id);
                                if(empty($city)){
                                    array_push($arrayTemp, $department_id.'001');
                                }else{
                                    if($department_id == "05" || $department_id == "08"){
                                        array_push($arrayTemp, '0'.$city->codigo);
                                    }else {
                                        array_push($arrayTemp, strval($city->codigo));
                                    }
                                }
                                
                            }
                        }else{
                            array_push($arrayTemp, $third['state']);
                            array_push($arrayTemp, $third['city']);
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
                        $phone = $third['phone'] != '' ? $third['phone'] : "6023798287";
                        array_push($arrayTemp,substr($phone, 2));
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "NOAPLICAFAC@GMAIL.COM");
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
        }
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;
    }

    public function filerDataFactFallabela($data)
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
            $cantidad = 0;
            $product = false;
            
            if(str_contains($bill['sku'],'SILLAEAMES')){

                $product = Products::getProduct('SILLAEAMES');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }elseif(str_contains($bill['sku'],'4005X')){
                $product = Products::getProduct('SILLA4005ENSAMBLADA');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }else{
                $product = Products::getProduct($bill['sku']);  
            }

            
           $unities = $cantidad > 0 ? $cantidad : $bill['quantity'];
           $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
           $priceUnit = $this->getUnitValue($priceUnity);

           $total = $this->calulateTotal($priceUnit,$unities);

            $comprobante = $total > 212000 ? 2 : 1;
            array_push($arrayTemp, $comprobante);
            array_push($arrayTemp, "");

            array_push($arrayTemp,  $bill['identification']);
            array_push($arrayTemp,"");
            array_push($arrayTemp,"14-1");
            array_push($arrayTemp,date("d/m/Y"));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");

            if($product == false){
                array_push($arrayTemp, 'No encontrado');
                array_push($arrayTemp, $bill['sku']);
            }else {
                array_push($arrayTemp, $product->code);
                array_push($arrayTemp, $product->name);
            }

            
            array_push($arrayTemp, "1144105658");

            $codbodega = $bill['type'] == "Dropshipping" ? "01" : "08";
            array_push($arrayTemp, $codbodega);
            
            array_push($arrayTemp, $unities);
           
            array_push($arrayTemp, $priceUnit);
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "014");
            
            array_push($arrayTemp,  $total);
            $d=strtotime("+30 Days");
            array_push($arrayTemp,date("d/m/Y", $d));
            array_push($arrayTemp, $bill['code_orden']);
            array_push($arrayTemp, $bill['status'] . ' - ' . $bill['created']  . ' - ' . $bill['updated']);

            array_push($arrayMl, $arrayTemp);

            $arrayTemp = [];
        }
        return $arrayMl;
    }

    private function filterDataExito($data)
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

            if ($third['name'] != " ")
            {
                $company = $this->isCompany($third['name']);
              
                if($company){
                    if(!$this->idInData($arrayEmp, $third['identification'] ))
                    {
                        
                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "31");
                        array_push($arrayTemp, "Empresa");
                        array_push($arrayTemp, strtoupper($third['name']));
                        array_push($arrayTemp, "" );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        $addres = $this->setAddresExito($third['address']);
                        array_push($arrayTemp, $addres);
                        array_push($arrayTemp, "Co");
                        $city = $this->getCityExito($third['address']);
                        $department_id = Departments::getDepartmentCode($city[1]);
                        if($department_id != false){
                            array_push($arrayTemp, strval($department_id));
                            if($department_id == "11"){
                                array_push($arrayTemp, 11001);
            
                            }else {
                                $city = Cities::getCity($city[2],$department_id);
                                if(empty($city)){
                                    array_push($arrayTemp, $department_id.'001');
                                }else{
                                    if($department_id == "05" || $department_id == "08"){
                                        array_push($arrayTemp, '0'.$city->codigo);
                                    }else {
                                        array_push($arrayTemp, strval($city->codigo));
                                    }
                                }
                                
                            }
                        }else{
                            array_push($arrayTemp, $city[1]);
                            array_push($arrayTemp, $city[2]);
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
                        $phone = str_contains($third['phone'],"+")  ? substr($third['phone'], 3) : $third['phone'];
                        array_push($arrayTemp,$phone);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, 'NOAPLICAFAC@GMAIL.COM');
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

                    if(!$this->idInData($arrayPers, $third['identification'] )){

                        array_push($arrayTemp, $third['identification']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "13");
                        array_push($arrayTemp, "Es persona");
                        array_push($arrayTemp, "");
                        $arrayName = $this->separteName($third['name']);
                        array_push($arrayTemp, strtoupper($arrayName[0]));
                        array_push($arrayTemp, strtoupper($arrayName[1]));
                        array_push($arrayTemp, "");
                        $addres = $this->setAddresExito($third['address']);
                        array_push($arrayTemp, $addres);
                        array_push($arrayTemp, "Co");
                        $city = $this->getCityExito($third['address']);
                        $department_id = Departments::getDepartmentCode($city[1]);
                        if($department_id != false){
                            array_push($arrayTemp, strval($department_id));
                            if($department_id == "11"){
                                array_push($arrayTemp, 11001);
            
                            }else {
                                $city = Cities::getCity($city[2],$department_id);
                                if(empty($city)){
                                    array_push($arrayTemp, $department_id.'001');
                                }else{
                                    if($department_id == "05" || $department_id == "08"){
                                        array_push($arrayTemp, '0'.$city->codigo);
                                    }else {
                                        array_push($arrayTemp, strval($city->codigo));
                                    }
                                }
                                
                            }
                        }else{
                            array_push($arrayTemp, $city[1]);
                            array_push($arrayTemp, $city[2]);
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
                        $phone = str_contains($third['phone'],"+")  ? substr($third['phone'], 3) : $third['phone'];
                        array_push($arrayTemp,$phone);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "NOAPLICAFAC@GMAIL.COM");
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
        }
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;
    }

    public function filerDataFactExito($data)
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
            $cantidad = 0;
            $product = false;
            
            if(str_contains($bill['sku'],'SILLAEAMES')){

                $product = Products::getProduct('SILLAEAMES');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }elseif(str_contains($bill['sku'],'4005X')){
                $product = Products::getProduct('SILLA4005ENSAMBLADA');
                $arraySilla = explode("X",$bill['sku']);
                $cantidad = intval($arraySilla[1]);

            }else{
                $product = Products::getProduct($bill['sku']);  
            }

            
           $unities = $cantidad > 0 ? $cantidad : $bill['quantity'];
           $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
           $priceUnit = $this->getUnitValue($priceUnity);

           $total = $this->calulateTotal($priceUnit,$unities);

            $comprobante = $total > 212000 ? 2 : 1;
            array_push($arrayTemp, $comprobante);
            array_push($arrayTemp, "");

            array_push($arrayTemp,  $bill['identification']);
            array_push($arrayTemp,"");
            array_push($arrayTemp,"4-1");
            array_push($arrayTemp,date("d/m/Y"));
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");

            if($product == false){
                array_push($arrayTemp, 'No encontrado');
                array_push($arrayTemp, $bill['sku']);
            }else {
                array_push($arrayTemp, $product->code);
                array_push($arrayTemp, $product->name);
            }

            
            array_push($arrayTemp, "1144105658");

            $codbodega = $bill['type'] == "True" ? "06" : "01";
            array_push($arrayTemp, $codbodega);
            
            array_push($arrayTemp, $unities);
           
            array_push($arrayTemp, $priceUnit);
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "1");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "");
            array_push($arrayTemp, "04");
            
            array_push($arrayTemp,  $total);
            $d=strtotime("+30 Days");
            array_push($arrayTemp,date("d/m/Y", $d));
            array_push($arrayTemp, $bill['code_orden']);
            array_push($arrayTemp, $bill['status'] );

            array_push($arrayMl, $arrayTemp);

            $arrayTemp = [];
        }
        return $arrayMl;
    }

}

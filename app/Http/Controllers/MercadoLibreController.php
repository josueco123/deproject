<?php

namespace App\Http\Controllers;

use App\Models\Cities;
use App\Models\Products;
use App\Models\Departments;
use Illuminate\Http\Request;
use App\Exports\FileFilterExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MercadoLibreController extends Controller
{
    public function loadMLLoginView()
    {
        return view('config.mercadolibrelogin');
    }

    public function loadMLView()
    {
        return view('reports.mercadolibre');
    }
    
    public function redirectToMercadoLibre()
    {
        $url = 'https://auth.mercadolibre.com.co/authorization?response_type=code&client_id=' . config('services.mercadolibre.client_id') . '&redirect_uri=' . urlencode(config('services.mercadolibre.redirect'));

        return redirect($url);
    }

    public function handleMercadoLibreCallback(Request $request)
    {
        $code = $request->input('code');

        $response = Http::post('https://api.mercadolibre.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.mercadolibre.client_id'),
            'client_secret' => config('services.mercadolibre.client_secret'),
            'code' => $code,
            'redirect_uri' => config('services.mercadolibre.redirect'),
        ]);

        $accessToken = $response->json()['access_token'];
        // Guarda el token de acceso en una variable de session
        session()->put('ml_token', $accessToken);
        // Redirige al usuario a la página deseada después de la autenticación 
        return redirect('home')->with('status', 'Aplicacion conectada correctamente!');
    }

    public function refreshToken()
    {
        $response = Http::post('https://api.mercadolibre.com/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.mercadolibre.client_id'),
            'client_secret' => config('services.mercadolibre.client_secret'),
            'refresh_token' => 'TG-64b69ce22e08070001b56932-212962423',
        ]);
        
        if($response->status() == 200){
            $accessToken = $response->json()['access_token'];
            session(['ml_token' => $accessToken]);
            //return $accessToken;
        }else {
            return redirect('mercadolibreapi')->with('status', $response->json()['message']);
        }
         
    }

    public function getSelectMethod(Request $request){
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'reportoption' => 'required',
          ]);

          $typereport = $request->input('reportoption');

          if($typereport == '1'){
            $result = $this->getOrdersByDate($request);
            return Excel::download(new FileFilterExport($result), "Mercado Libre Terceros".date("Y-m-d H:i:s").'.xlsx');
          }else{
            $result = $this->getOrdersForBilling($request);
            return Excel::download(new FileFilterExport($result), "Mercado Libre Facturacion".date("Y-m-d H:i:s").'.xlsx');
          }
    }

    public function getOrdersByDate(Request $request)
    {
        $start_date = $request->input('start_date').':00.000-00:00';
        $end_date = $request->input('end_date').':00.000-00:00';

        $total = 0;
        $offset = 0;

        $response = Http::withToken($request->session()->get('ml_token'))
        ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date.'&order.status=paid&offset='.$offset);

        if($response->status() == 403 || $response->status() == 401){
           $token = $this->refreshToken();
            $response = Http::withToken($request->session()->get('ml_token'))
            ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date.'&order.status=paid&offset='.$offset);
        }
        
        if($response->status() == 200){
            $total = $response->json()['paging']['total'];
            $arrayData = [];

            while($total > $offset){

                $data = $response->json()['results'];
            
                
                foreach($data as $result){
                    
                    $billing = $this->getBillingInfo($request,$result['id']);
                    $location = $this->getShipmentsData($request, $result['shipping']['id']);
                    $resultado = array_merge($billing, $location);
                    array_push($arrayData, $resultado);
                }
                
                $offset += 51;

                $response = Http::withToken($request->session()->get('ml_token'))
            ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date.'&order.status=paid&offset='.$offset);
                
            }
            
             $result = $this->transformDataThirds($arrayData);
             return $result;
            

        }else{
            return redirect('mercadolibreapi')->with('status', $response->json()['message']);
        } 
  
    }


    public function getBillingInfo(Request $request,$order)
    {
        $response = Http::withToken($request->session()->get('ml_token'))
        ->get('https://api.mercadolibre.com/orders/'.$order .'/billing_info');

        if($response->status() == 200){

            $data = $response->json()['billing_info'];

            $additional_info = $data['additional_info'];
            $arrayResponse = [];
            $arrayTemp = [];
            foreach($additional_info as $info){
                if($info['type'] == 'DOC_NUMBER'){
                $arrayTemp['doc'] = $info['value'];
                }

                if($info['type'] == 'DOC_TYPE'){
                    $arrayTemp['type'] = $info['value'];
                }

                if($info['type'] == 'BUSINESS_NAME'){
                    $arrayTemp['razon_social'] = $info['value'];
            }

                if($info['type'] == 'LAST_NAME'){
                    $arrayTemp['last_name'] = $info['value'];
                }

                if($info['type'] == 'FIRST_NAME'){
                    $arrayTemp['first_name'] = $info['value'];
                }
                
            }
            
            return $arrayTemp;
        }else{
            return redirect('mercadolibreapi')->with('status', $response->json()['message']);
        }
    }

    public function getShipmentsData(Request $request,$shipments)
    {
        $response = Http::withToken($request->session()->get('ml_token'))
        ->get('https://api.mercadolibre.com/shipments/'.$shipments);
        
        if($response->status() == 200){
            $data = $response->json()['receiver_address'];

            $info = [
                'addres' => $data['address_line'],
                'state' => $data['state']['name'],
                'city'=> $data['city']['name']
            ];

            return $info;
        }else{
            return redirect('mercadolibreapi')->with('status', $response->json()['message']);
        }
    }

    public function transformDataThirds($data)
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

                if($third['type'] == "NIT"){
                    if(!$this->idInData($arrayEmp, $third['doc']))
                    {
                        array_push($arrayTemp, $third['doc']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "31");
                        array_push($arrayTemp, "Empresa");
                        array_push($arrayTemp, strtoupper($third['razon_social']));
                        array_push($arrayTemp, "" );
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['addres'] );
                        array_push($arrayTemp, "Co");
                        $department_id = Departments::getDepartmentCode($third['state']);
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

                    if(!$this->idInData($arrayPers, $third['doc'])){

                        array_push($arrayTemp, $third['doc']);
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, "13");
                        array_push($arrayTemp, "Es persona");
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, strtoupper($third['first_name']));
                        array_push($arrayTemp, strtoupper($third['last_name']));
                        array_push($arrayTemp, "");
                        array_push($arrayTemp, $third['addres']);
                        array_push($arrayTemp, "Co");
                        $department_id = Departments::getDepartmentCode($third['state']);
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
        $arrayResult = array_merge($arrayMl,$arrayPers,$arrayEmp);
        return $arrayResult;

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

    public function getOrdersForBilling(Request $request)
    {
        $start_date = $request->input('start_date').':00.000-00:00';
        $end_date = $request->input('end_date').':00.000-00:00';

        $total = 0;
        $offset = 0;

        $response = Http::withToken($request->session()->get('ml_token'))
        ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date.'&order.status=paid&offset='.$offset);

        if($response->status() == 403 || $response->status() == 401){
           $token = $this->refreshToken();
            $response = Http::withToken($request->session()->get('ml_token'))
            ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date.'&order.status=paid&offset='.$offset);
        }
        
        if($response->status() == 200){
            $total = $response->json()['paging']['total'];
            $arrayData = [];

            while($total > $offset){

                $data = $response->json()['results'];
            
                
                foreach($data as $result){
                    $$totalpaid = $result['payments'][0]['total_paid_amount'];
                    if($$totalpaid > 0){
                        $orderArray = array(
                            'code_orden' => $result['id'].' ',
                            'total' => $$totalpaid,
                            'title' => $result['order_items'][0]['item']['title'],
                            'unities' => $result['order_items'][0]['quantity'],
                            'unit_price' => $result['order_items'][0]['unit_price'],
                            'sku' => $result['order_items'][0]['item']['seller_sku'],
                        );
                        $billing = $this->getBillingInfo($request,$result['id']);
                        $resultado = array_merge($billing,$orderArray);
                        array_push($arrayData, $resultado);
                    }
                    
                }
                
                $offset += 51;

                $response = Http::withToken($request->session()->get('ml_token'))
            ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date.'&order.status=paid&offset='.$offset);
                
            }
            
            $result = $this->filerDataFact($arrayData);
            return $result;

        }else{
            return redirect('mercadolibreapi')->with('status', $response->json()['message']);
        } 
  
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
            $cantidad = 0;
            $unities = $cantidad > 0 ? $cantidad : $bill['unities'];
            $priceUnity = $cantidad > 0 ? (intval($bill['unit_price'])/$cantidad) : $bill['unit_price'];
            $priceUnit = $this->getUnitValue($priceUnity);

            $total = $this->calulateTotal($priceUnit,$unities);

            $comprobante = $total  > 212000 ? 2 : 1;
            array_push($arrayTemp, $comprobante);
            array_push($arrayTemp, "");
            $companyId = $bill['type'] == 'NIT' ? '222222222' : $bill['doc'];
            array_push($arrayTemp, $companyId);
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

            if(($bill['unities'] != '')){

                if(str_contains($bill['sku'],'SILLAEAMES')){

                    $product = Products::getProduct('SILLAEAMES');
                    $arraySilla = explode("X",$bill['sku']);
                    $cantidad = intval($arraySilla[1]);
                    array_push($arrayTemp, $product->code);
    
                }elseif(str_contains($bill['sku'],'4005X')){
                    $product = Products::getProduct('SILLA4005ENSAMBLADA');
                    $arraySilla = explode("X",$bill['sku']);
                    $cantidad = intval($arraySilla[1]);
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
            
            
   
           
            array_push($arrayTemp, "901284706");
            array_push($arrayTemp, "");
           
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
            array_push($arrayTemp, $bill['code_orden']);

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

    public function calulateTotal($unit,$cant)
    {
        $priceUnity = intval($unit);
        $quantity = intval($cant);

        $total = ($priceUnity * $quantity ) + ($priceUnity * 0.19);
        return $total;
    }


}

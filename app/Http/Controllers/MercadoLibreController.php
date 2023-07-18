<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
       
    }

    public function getOrdersByDate(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

         $response = Http::withToken('APP_USR-8440252452716833-071810-aee6d7708a87ae36853119cc1afbe618-212962423')
        ->get('https://api.mercadolibre.com/orders/search?seller=212962423&order.date_created.from='. $start_date .'&order.date_created.to='.$end_date);

        return $response->json();
    }


}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MercadoLibreController extends Controller
{
    public function loadMLView()
    {
        return view('reports.mercadolibre');
    }
    //
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
        // Guarda el token de acceso en tu base de datos o utiliza según tus necesidades

        // Redirige al usuario a la página deseada después de la autenticación
    }

}

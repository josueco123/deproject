@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach 
            </div>
            @endif   
            <div class="card">
                <div class="card-header">Conectar aplicativo con Mercado Libre</div>

                <div class="card-body">
                    <p class="card-text">Inicia session para obtener datos de Mercado Libre desde este aplicativo.</p>
                    <a href="{{ route('mercadolibreredirect') }}" class="btn btn-primary">Iniciar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
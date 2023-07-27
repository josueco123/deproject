@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
        @if (session('status'))
                <div class="alert alert-warning">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach 
            </div>
            @endif   
            <div class="card">
                <div class="card-header">Generar Archivo con Reporte de Facturacion</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('sendUploadBilling') }}" enctype="multipart/form-data">
                        @csrf  
                        <div class="form-group mx-sm-3 mb-2">   
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file_input" name="file_input" lang="es">
                                <label class="custom-file-label" for="file_input">Seleccionar Archivo</label>
                                <p class="mt-1" id="file_input_help">Sube el archivo de exel de Mercado Libre para generar de facturacion.</p>
                            </div>
                        </div>
                        <div class="form-group mb-2">  
                            <label for="radio_grup" class="col-sm-5 col-form-label">Selecciona La Bodega de despacho</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="inlineRadio1" name="codbodega" value="01">
                                <label class="form-check-label" for="inlineRadio1">Armenia</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="inlineRadio2" name="codbodega" value="02">
                                <label class="form-check-label" for="inlineRadio2">Full Bogota</label>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif 
            <div class="card">
                <div class="card-header">Generar Archivo con Reporte de Terceros</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('sendUploadThirds') }}" enctype="multipart/form-data">
                        @csrf  
                        <div class="form-group mx-sm-3 mb-2">   
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file_input" name="file_input" lang="es" required>
                                <label class="custom-file-label" for="file_input">Adjuntar Archivo de Tienda</label>
                                <p class="mt-1" id="file_input_help">Sube el archivo de Excel para generar archivo de terceros.</p>
                            </div>
                        </div>
                        <div class="form-group  mx-sm-3 mb-2">
                            <label for="selectstore">Selecione la Tienda</label>
                            <select class="form-control" id="selectstore" name="selectstore" required>
                            <option value="">-- Tiendas --</option>
                            <option value="1" >Mercado Libre</option>
                            <option value="2">Elenas</option>
                            <option value="3">Linio</option>
                            <option value="4" disabled>Fallabela</option>
                            <option value="5" disabled>Exito</option>
                            <option value="6" disabled>FBL</option>
                            </select>
                        </div>
                        <div class="form-group mt-4">
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

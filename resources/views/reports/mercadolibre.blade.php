@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-primary hidden mlAlert" role="alert">
                Espera mientras genera y descarga el archivo
            </div>
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
                <div class="card-header">Generar Archivo Reporte desde Mercado Libre</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('mercadolibreorders') }}">
                        @csrf  
                        <div class="form-group row">   
                            <label for="start_date" class="col-sm-4 col-form-label">Selecciona la fecha de inicio</label>
                            <div class="col-sm-6">
                                <input type="datetime-local" id="start_date" name="start_date" class="form-control"> 
                            </div>
                        </div>
                        <div class="form-group row">   
                            <label for="end_date" class="col-sm-4 col-form-label">Selecciona la fecha de corte</label>
                            <div class="col-sm-6">
                                <input type="datetime-local" id="end_date" name="end_date" class="form-control">
                                <small id="endDateHelpBlock" class="form-text text-muted">
                                    Mercado Libre solo trabaja con la hora, no hay necesidad de que llenes los minuntos
                                </small>
                            </div> 
                        </div>
                        <div class="form-group row">  
                            <label for="radio_grup" class="col-sm-4 col-form-label">Selecciona el tipo de reporte</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1">
                                <label class="form-check-label" for="inlineRadio1">Reporte de Terceros</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2">
                                <label class="form-check-label" for="inlineRadio2">Reporte de Facturación</label>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary mlBtn">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
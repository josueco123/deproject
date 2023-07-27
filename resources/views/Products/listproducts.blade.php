@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
        @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <div class="text-right mb-2">
                        <a class='btn btn-primary' href="{{ route('formproduct') }}" role='button'> Agregar Producto </a>
                    </div>
            <div class="card">
                <div class="card-header">Listado de Productos</div>
                    <div class="table-responsive">
                        <table id="productsTable" class="table">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Nombre</th>
                                    <th>Referencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
        </div>
    </div>
</div>

@endsection
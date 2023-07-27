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
                <div class="card-header"><?= isset($product) ? 'Actualizar Producto': 'Agregar Producto' ?></div>
                @if (isset($product))
                <form method="POST" class="mt-2" action="{{ route('updateproduct',['id' => $product->id]) }}">
                @else
                <form method="POST" class="mt-2" action="{{ route('saveproduct') }}">
                @endif  
                    @csrf  
                    <div class="form-row m-3">
                        <div class="form-group col-md-6 ">
                            <label for="code">Codigo</label>
                            <input type="number" class="form-control" id="code" name="code" value="<?= isset($product) ? $product->code : '' ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="reference">Referencia</label>
                            <input type="text" class="form-control" id="reference" name="reference" value="<?= isset($product) ? $product->reference : '' ?>">
                        </div>
                    </div>
                    <div class="form-group m-3">
                        <label for="nameproduct">Nombre</label>
                        <input type="text" class="form-control" id="nameproduct" name="nameproduct" value="<?= isset($product) ? $product->name : '' ?>">
                    </div>
                    <div class="form-group mb-2">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary"><?= isset($product) ? "Actualizar": "Guardar" ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
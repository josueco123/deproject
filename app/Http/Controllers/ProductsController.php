<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view ('products.listproducts');

    }

    public function getProducts()
    {
        $Products = Products::all();
        return response()->json($Products);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view ('products.formproduct');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'code' => 'required|unique:productos,code',
            'reference' => 'required',
            'nameproduct' => 'required'
          ]);

        $Product = new Products;
         
        $Product->code = $request->code;
        $Product->reference = strtoupper(str_replace(' ', '',$request->reference));
        $Product->name = strtoupper($request->nameproduct);
 
        $Product->save();

        return redirect('showproducts')->with('status', 'Producto Agregado correctamente!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function show(Products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $product = Products::find($id);
        return view ('products.formproduct',compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'code' => 'required|unique:productos,code',
            'reference' => 'required',
            'nameproduct' => 'required'
          ]);

        $Product = Products::find($id);
         
        $Product->code = $request->code;
        $Product->reference = strtoupper(str_replace(' ', '',$request->reference));
        $Product->name = strtoupper($request->nameproduct);
 
        $Product->save();

        return redirect('showproducts')->with('status', 'Producto Editado correctamente!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy(Products $products)
    {
        //
    }
}

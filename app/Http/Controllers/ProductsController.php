<?php

namespace App\Http\Controllers;

use App\Models\products;
use App\Models\sections;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = products::all();
        $sections = sections::all();
        return view('products.products', compact('sections', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'product_name' => 'required|unique:products|max:255',
            'description' => 'required',
            'section_id' => 'required'
        ], [
            'product_name.required' => 'يرجى إدخال اسم المنتج',
            'product_name.unique' => 'اسم المنتج مسجل مسبقاً',
            'description.required' => 'يرجى إدخال التوصيف',
            'section_id.required' => 'يرجى تحديد اسم القسم'
        ]);

        products::create([
            'product_name' => $request->product_name,
            'section_id'  => $request->section_id,
            'description'  => $request->description,
        ]);
        session()->flash('Add', 'تم اضافة المنتج بنجاح ');
        return redirect('/products');
    }

    /**
     * Display the specified resource.
     */
    public function show(products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = sections::where('section_name', $request->section_name)->first()->id;
        $products = products::findOrFail($request->pro_id);
        $pro_id = $request->pro_id;

        $this->validate($request, [

            'product_name' => 'required|max:255' . $pro_id,
            'description' => 'required',
            'section_name' => 'required'
        ], [

            'product_name.required' => 'يرجى إدخال اسم المنتج',
            'description.required' => 'يرجى إدخال التوصيف',
            'section_name.required' => 'يرجى تحديد اسم القسم'

        ]);

        $products = products::find($pro_id);
        $products->update([
            'product_name' => $request->product_name,
            'section_id' => $id,
            'description' => $request->description
        ]);

        session()->flash('edit', 'تم تعديل المنتج بنجاح');
        return redirect('/products');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $pro_id = $request->pro_id;
        products::find($pro_id)->delete();
        session()->flash('delete', 'تم حذف القسم بنجاح');
        return redirect('/products');
    }
}

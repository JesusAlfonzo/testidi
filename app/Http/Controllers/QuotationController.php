<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:cotizaciones_ver')->only('index', 'show');
        $this->middleware('permission:cotizaciones_crear')->only('create', 'store');
        $this->middleware('permission:cotizaciones_editar')->only('edit', 'update');
        $this->middleware('permission:cotizaciones_eliminar')->only('destroy');
        $this->middleware('permission:cotizaciones_aprobar')->only('aprobar');
        $this->middleware('permission:cotizaciones_rechazar')->only('rechazar');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.quotations.index');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

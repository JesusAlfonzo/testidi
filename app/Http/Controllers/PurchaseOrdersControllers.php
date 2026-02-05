<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseOrdersControllers extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ordenes_compra_ver')->only(['index', 'show']);
        $this->middleware('permission:ordenes_compra_crear')->only(['create', 'store']);
        $this->middleware('permission:ordenes_compra_editar')->only(['edit', 'update']);
        $this->middleware('permission:ordenes_compra_eliminar')->only(['destroy']);
        $this->middleware('permission:ordenes_compra_aprobar')->only(['approve']);
        $this->middleware('permission:ordenes_compra_rechazar')->only(['reject']);
        $this->middleware('permission:ordenes_compra_anular')->only(['cancel']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.purchaseOrders.index');
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

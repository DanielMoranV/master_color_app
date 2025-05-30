<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Stock::with('product')->get();
        return response()->json($stocks);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::doesntHave('stock')->get();
        return view('stocks.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if stock already exists for this product
        $existingStock = Stock::where('product_id', $request->product_id)->first();
        if ($existingStock) {
            return response()->json(['error' => 'Stock already exists for this product'], 422);
        }

        $stock = Stock::create($request->all());

        return response()->json($stock, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stock = Stock::with('product', 'stockMovements')->findOrFail($id);
        return response()->json($stock);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stock = Stock::with('product')->findOrFail($id);
        return view('stocks.edit', compact('stock'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stock = Stock::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|required|integer|min:0',
            'min_stock' => 'sometimes|required|integer|min:0',
            'max_stock' => 'sometimes|required|integer|min:0',
            'purchase_price' => 'sometimes|required|numeric|min:0',
            'sale_price' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $stock->update($request->all());

        return response()->json($stock);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // In most inventory systems, we don't want to delete stock records
        // Instead, we might want to set quantity to 0 or mark as inactive
        return response()->json(['error' => 'Stock records cannot be deleted'], 403);
    }

    /**
     * Adjust stock quantity.
     */
    public function adjustStock(Request $request, string $id)
    {
        $stock = Stock::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'adjustment' => 'required|integer',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure stock doesn't go negative
        if ($stock->quantity + $request->adjustment < 0) {
            return response()->json(['error' => 'Stock cannot go below zero'], 422);
        }

        $stock->quantity += $request->adjustment;
        $stock->save();

        // Create stock movement record
        $movementType = $request->adjustment > 0 ? 'Entrada' : 'Ajuste';
        if ($request->adjustment < 0) {
            $movementType = 'Salida';
        }

        $stock->stockMovements()->create([
            'movement_type' => $movementType,
            'quantity' => abs($request->adjustment),
            'reason' => $request->reason,
            'unit_price' => $stock->purchase_price,
            'user_id' => Auth::id(),
        ]);

        return response()->json($stock);
    }
}

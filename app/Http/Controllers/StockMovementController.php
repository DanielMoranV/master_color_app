<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['stock.product', 'user']);
        
        // Filter by stock_id if provided
        if ($request->has('stock_id')) {
            $query->where('stock_id', $request->stock_id);
        }
        
        // Filter by movement_type if provided
        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }
        
        // Filter by date range if provided
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json($movements);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stocks = Stock::with('product')->get();
        return view('stock_movements.create', compact('stocks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_id' => 'required|exists:stocks,id',
            'movement_type' => 'required|in:Entrada,Salida,Ajuste,Devolucion',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'voucher_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $stock = Stock::findOrFail($request->stock_id);
        
        // Check if we have enough stock for outgoing movements
        if (in_array($request->movement_type, ['Salida', 'Devolucion']) && $stock->quantity < $request->quantity) {
            return response()->json(['error' => 'Not enough stock available'], 422);
        }

        // Create the movement
        $movement = new StockMovement();
        $movement->stock_id = $request->stock_id;
        $movement->movement_type = $request->movement_type;
        $movement->quantity = $request->quantity;
        $movement->reason = $request->reason;
        $movement->unit_price = $request->unit_price;
        $movement->user_id = Auth::id();
        $movement->voucher_number = $request->voucher_number;
        $movement->save();

        // Update stock quantity
        if (in_array($request->movement_type, ['Entrada', 'Devolucion'])) {
            $stock->quantity += $request->quantity;
        } else {
            $stock->quantity -= $request->quantity;
        }
        $stock->save();

        return response()->json($movement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $movement = StockMovement::with(['stock.product', 'user'])->findOrFail($id);
        return response()->json($movement);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Stock movements should not be editable after creation for audit purposes
        return response()->json(['error' => 'Stock movements cannot be edited'], 403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Stock movements should not be editable after creation for audit purposes
        return response()->json(['error' => 'Stock movements cannot be edited'], 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Stock movements should not be deletable after creation for audit purposes
        return response()->json(['error' => 'Stock movements cannot be deleted'], 403);
    }

    /**
     * Get stock movement report.
     */
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:Entrada,Salida,Ajuste,Devolucion',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = StockMovement::with(['stock.product', 'user'])
            ->whereBetween('created_at', [$request->from_date, $request->to_date]);

        if ($request->has('product_id')) {
            $query->whereHas('stock', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        return response()->json($movements);
    }
}

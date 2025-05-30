<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Address;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['client', 'address', 'orderDetails.product', 'payment']);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by client if provided
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by date range if provided
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json($orders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::all();
        $products = Product::with('stock')->whereHas('stock', function ($query) {
            $query->where('quantity', '>', 0);
        })->get();
        
        return view('orders.create', compact('clients', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'address_id' => 'required|exists:addresses,id',
            'shipping_cost' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Efectivo,Tarjeta,Yape,Plin,TC,Transferencia',
            'payment_code' => 'required|string',
            'document_type' => 'required|in:Boleta,Factura,Ticket,NC',
            'observations' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Calculate subtotal
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Create order
            $order = new Order();
            $order->user_id = Auth::id();
            $order->client_id = $request->client_id;
            $order->address_id = $request->address_id;
            $order->subtotal = $subtotal;
            $order->shipping_cost = $request->shipping_cost;
            $order->discount = $request->discount;
            $order->status = 'pendiente';
            $order->payment_code = $request->payment_code;
            $order->observations = $request->observations;
            $order->save();

            // Create order details and update stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $stock = $product->stock;

                // Check if enough stock is available
                if ($stock->quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Not enough stock for product: {$product->name}. Available: {$stock->quantity}"
                    ], 422);
                }

                // Create order detail
                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $item['product_id'];
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->unit_price = $item['unit_price'];
                $orderDetail->save();

                // Update stock
                $stock->quantity -= $item['quantity'];
                $stock->save();

                // Create stock movement
                $stock->stockMovements()->create([
                    'movement_type' => 'Salida',
                    'quantity' => $item['quantity'],
                    'reason' => "Venta - Orden #{$order->id}",
                    'unit_price' => $item['unit_price'],
                    'user_id' => Auth::id(),
                    'voucher_number' => $order->id,
                ]);
            }

            // Create payment
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->payment_method = $request->payment_method;
            $payment->payment_code = $request->payment_code;
            $payment->document_type = $request->document_type;
            $payment->nc_reference = $request->nc_reference;
            $payment->observations = $request->observations;
            $payment->save();

            DB::commit();

            return response()->json([
                'order' => $order->load(['client', 'address', 'orderDetails.product', 'payment'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error creating order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['client', 'address', 'orderDetails.product', 'payment', 'user'])
            ->findOrFail($id);
        return response()->json($order);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order = Order::with(['client', 'address', 'orderDetails.product', 'payment'])
            ->findOrFail($id);
        $clients = Client::all();
        $addresses = Address::where('client_id', $order->client_id)->get();
        
        return view('orders.edit', compact('order', 'clients', 'addresses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        // Only allow updating status and observations if order is not cancelled
        if ($order->status === 'cancelado') {
            return response()->json(['error' => 'Cannot update a cancelled order'], 422);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:pendiente,confirmado,procesando,enviado,entregado,cancelado',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If cancelling the order, return items to stock
        if ($request->has('status') && $request->status === 'cancelado' && $order->status !== 'cancelado') {
            DB::beginTransaction();
            
            try {
                foreach ($order->orderDetails as $detail) {
                    $stock = $detail->product->stock;
                    $stock->quantity += $detail->quantity;
                    $stock->save();

                    // Create stock movement
                    $stock->stockMovements()->create([
                        'movement_type' => 'Devolucion',
                        'quantity' => $detail->quantity,
                        'reason' => "Cancelación - Orden #{$order->id}",
                        'unit_price' => $detail->unit_price,
                        'user_id' => Auth::id(),
                        'voucher_number' => $order->id,
                    ]);
                }

                $order->status = 'cancelado';
                $order->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Error cancelling order: ' . $e->getMessage()], 500);
            }
        } else {
            $order->update($request->only(['status', 'observations']));
        }

        return response()->json($order->load(['client', 'address', 'orderDetails.product', 'payment']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        
        // Only allow soft deleting orders that are cancelled or delivered
        if (!in_array($order->status, ['cancelado', 'entregado'])) {
            return response()->json([
                'error' => 'Only cancelled or delivered orders can be deleted'
            ], 422);
        }
        
        $order->delete();
        return response()->json(null, 204);
    }

    /**
     * Get client addresses.
     */
    public function getClientAddresses(string $clientId)
    {
        $addresses = Address::where('client_id', $clientId)->get();
        return response()->json($addresses);
    }

    /**
     * Get order statistics.
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        $stats = [
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_sales' => Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelado')
                ->sum(DB::raw('subtotal + shipping_cost - discount')),
            'pending_orders' => Order::where('status', 'pendiente')->count(),
            'completed_orders' => Order::where('status', 'entregado')->count(),
            'cancelled_orders' => Order::where('status', 'cancelado')->count(),
            'top_products' => DB::table('order_details')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelado')
                ->select('products.name', DB::raw('SUM(order_details.quantity) as total_quantity'))
                ->groupBy('products.name')
                ->orderBy('total_quantity', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
    
    /**
     * Get all orders for the authenticated client.
     */
    public function clientOrders(Request $request)
    {
        $client = Auth::guard('client')->user();
        $orders = Order::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'status' => ucfirst($order->status),
                    'total' => $order->subtotal + $order->shipping_cost - $order->discount,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ];
            });
        
        return Inertia::render('client/Orders', [
            'orders' => $orders
        ]);
    }
    
    /**
     * Show a specific order for the authenticated client.
     */
    public function clientOrderShow(string $id)
    {
        $client = Auth::guard('client')->user();
        $order = Order::with(['address', 'orderDetails.product', 'payment'])
            ->where('client_id', $client->id)
            ->findOrFail($id);
            
        $formattedOrder = [
            'id' => $order->id,
            'order_number' => 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'status' => ucfirst($order->status),
            'subtotal' => $order->subtotal,
            'tax' => $order->subtotal * 0.18, // 18% tax
            'total' => $order->subtotal + $order->shipping_cost - $order->discount,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'items' => $order->orderDetails->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'product_name' => $detail->product->name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->unit_price,
                    'total' => $detail->quantity * $detail->unit_price
                ];
            }),
            'shipping_address' => [
                'address' => $order->address->address,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country ?? 'Peru'
            ]
        ];
            
        return Inertia::render('client/OrderDetail', [
            'order' => $formattedOrder
        ]);
    }
    
    /**
     * Store a new order for the authenticated client.
     */
    public function clientOrderStore(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:Efectivo,Tarjeta,Yape,Plin,TC,Transferencia',
            'payment_code' => 'required|string',
            'document_type' => 'required|in:Boleta,Factura,Ticket',
            'observations' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Calculate subtotal and validate stock
            $subtotal = 0;
            $items = [];
            
            foreach ($request->items as $item) {
                $product = Product::with('stock')->findOrFail($item['product_id']);
                
                // Check if enough stock is available
                if ($product->stock->quantity < $item['quantity']) {
                    return redirect()->back()->with('error', "Not enough stock for product: {$product->name}. Available: {$product->stock->quantity}");
                }
                
                $unitPrice = $product->stock->sale_price;
                $subtotal += $item['quantity'] * $unitPrice;
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice
                ];
            }

            // Apply shipping cost and discount
            $shippingCost = 10.00; // Default shipping cost
            $discount = 0.00;      // Default discount
            
            // Create order
            $order = new Order();
            $order->user_id = 1; // Default admin user ID
            $order->client_id = $client->id;
            $order->address_id = $request->address_id;
            $order->subtotal = $subtotal;
            $order->shipping_cost = $shippingCost;
            $order->discount = $discount;
            $order->status = 'pendiente';
            $order->payment_code = $request->payment_code;
            $order->observations = $request->observations;
            $order->save();

            // Create order details and update stock
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $stock = $product->stock;

                // Create order detail
                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $item['product_id'];
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->unit_price = $item['unit_price'];
                $orderDetail->save();

                // Update stock
                $stock->quantity -= $item['quantity'];
                $stock->save();

                // Create stock movement
                $stock->stockMovements()->create([
                    'movement_type' => 'Salida',
                    'quantity' => $item['quantity'],
                    'reason' => "Venta Online - Orden #{$order->id}",
                    'unit_price' => $item['unit_price'],
                    'user_id' => 1, // Default admin user ID
                    'voucher_number' => $order->id,
                ]);
            }

            // Create payment
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->payment_method = $request->payment_method;
            $payment->payment_code = $request->payment_code;
            $payment->document_type = $request->document_type;
            $payment->observations = $request->observations;
            $payment->save();

            DB::commit();

            return redirect()->route('client.orders.show', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }
}

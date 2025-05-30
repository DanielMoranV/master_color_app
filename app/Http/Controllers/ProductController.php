<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('stock')->get();
        return response()->json($products);
    }
    
    /**
     * API endpoint for retrieving products with stock information.
     */
    public function apiProducts()
    {
        $products = Product::with('stock')->get();
        return ProductResource::collection($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products',
            'image_url' => 'required|string|max:255',
            'barcode' => 'required|string|max:255|unique:products',
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'presentation' => 'required|string|max:255',
            'unidad' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create product
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->image_url = $request->image_url;
        $product->barcode = $request->barcode;
        $product->brand = $request->brand;
        $product->description = $request->description;
        $product->presentation = $request->presentation;
        $product->unidad = $request->unidad;
        $product->user_id = Auth::id();
        $product->save();

        // Create stock
        $stock = new Stock();
        $stock->product_id = $product->id;
        $stock->quantity = $request->quantity;
        $stock->min_stock = $request->min_stock;
        $stock->max_stock = $request->max_stock;
        $stock->purchase_price = $request->purchase_price;
        $stock->sale_price = $request->sale_price;
        $stock->save();

        return response()->json(['product' => $product, 'stock' => $stock], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('stock')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with('stock')->findOrFail($id);
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
            ],
            'image_url' => 'sometimes|required|string|max:255',
            'barcode' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
            ],
            'brand' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'presentation' => 'sometimes|required|string|max:255',
            'unidad' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());

        // Update stock if provided
        if ($request->has('purchase_price') || $request->has('sale_price') ||
            $request->has('min_stock') || $request->has('max_stock')) {
            
            $stockValidator = Validator::make($request->all(), [
                'purchase_price' => 'sometimes|required|numeric|min:0',
                'sale_price' => 'sometimes|required|numeric|min:0',
                'min_stock' => 'sometimes|required|integer|min:0',
                'max_stock' => 'sometimes|required|integer|min:0',
            ]);

            if ($stockValidator->fails()) {
                return response()->json(['errors' => $stockValidator->errors()], 422);
            }

            $stock = $product->stock;
            if ($stock) {
                if ($request->has('purchase_price')) {
                    $stock->purchase_price = $request->purchase_price;
                }
                if ($request->has('sale_price')) {
                    $stock->sale_price = $request->sale_price;
                }
                if ($request->has('min_stock')) {
                    $stock->min_stock = $request->min_stock;
                }
                if ($request->has('max_stock')) {
                    $stock->max_stock = $request->max_stock;
                }
                $stock->save();
            }
        }

        return response()->json(['product' => $product, 'stock' => $product->stock]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }
}

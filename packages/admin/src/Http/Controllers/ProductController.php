<?php

namespace VitaliJalbu\LaravelShopper\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use VitaliJalbu\LaravelShopper\Core\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'productType', 'variants'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->brand, function ($query, $brand) {
                $query->where('brand_id', $brand);
            });

        $products = $query->paginate(20);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'status', 'brand']),
            'brands' => \VitaliJalbu\LaravelShopper\Core\Models\Brand::all(['id', 'name']),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['brand', 'productType', 'variants', 'options', 'categories']);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'brands' => \VitaliJalbu\LaravelShopper\Core\Models\Brand::all(['id', 'name']),
            'productTypes' => \VitaliJalbu\LaravelShopper\Core\Models\ProductType::all(['id', 'name']),
            'categories' => \VitaliJalbu\LaravelShopper\Core\Models\Category::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . (new Product)->getTable() . ',slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'brand_id' => 'nullable|exists:' . (new \VitaliJalbu\LaravelShopper\Core\Models\Brand)->getTable() . ',id',
            'product_type_id' => 'required|exists:' . (new \VitaliJalbu\LaravelShopper\Core\Models\ProductType)->getTable() . ',id',
            'status' => 'required|in:active,draft,archived',
            'is_visible' => 'boolean',
            'requires_shipping' => 'boolean',
            'track_quantity' => 'boolean',
        ]);

        $product = Product::create($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load(['brand', 'productType', 'variants', 'categories']);

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'brands' => \VitaliJalbu\LaravelShopper\Core\Models\Brand::all(['id', 'name']),
            'productTypes' => \VitaliJalbu\LaravelShopper\Core\Models\ProductType::all(['id', 'name']),
            'categories' => \VitaliJalbu\LaravelShopper\Core\Models\Category::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:' . $product->getTable() . ',slug,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'brand_id' => 'nullable|exists:' . (new \VitaliJalbu\LaravelShopper\Core\Models\Brand)->getTable() . ',id',
            'product_type_id' => 'required|exists:' . (new \VitaliJalbu\LaravelShopper\Core\Models\ProductType)->getTable() . ',id',
            'status' => 'required|in:active,draft,archived',
            'is_visible' => 'boolean',
            'requires_shipping' => 'boolean',
            'track_quantity' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}

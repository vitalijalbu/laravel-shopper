<?php

namespace LaravelShopper\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelShopper\Models\Product;
use LaravelShopper\Models\Category;
use LaravelShopper\Models\Site;
use LaravelShopper\Services\TemplateEngine;

class StorefrontController extends Controller
{
    protected TemplateEngine $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * Home page
     */
    public function home(Request $request): Response
    {
        $site = app('laravel-shopper.site');
        
        $content = $this->templateEngine->render(
            'index',
            null,
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Product listing page
     */
    public function productIndex(Request $request): Response
    {
        $products = Product::active()
            ->with(['media', 'variants', 'category'])
            ->paginate(20);

        $content = $this->templateEngine->render(
            'collection',
            (object) [
                'title' => 'All Products',
                'handle' => 'all-products',
                'products' => $products,
                'type' => 'products'
            ],
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Single product page
     */
    public function productShow(Request $request, string $handle): Response
    {
        $product = Product::where('handle', $handle)
            ->with(['media', 'variants', 'category', 'reviews'])
            ->active()
            ->firstOrFail();

        $content = $this->templateEngine->render(
            'product',
            $product,
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Category listing page
     */
    public function categoryIndex(Request $request): Response
    {
        $categories = Category::active()
            ->with(['media'])
            ->orderBy('name')
            ->get();

        $content = $this->templateEngine->render(
            'collection',
            (object) [
                'title' => 'All Collections',
                'handle' => 'all-collections',
                'collections' => $categories,
                'type' => 'collections'
            ],
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Single category page
     */
    public function categoryShow(Request $request, string $handle): Response
    {
        $category = Category::where('handle', $handle)
            ->with(['media', 'products.media'])
            ->active()
            ->firstOrFail();

        $products = $category->products()
            ->active()
            ->with(['media', 'variants'])
            ->paginate(20);

        $category->products = $products;

        $content = $this->templateEngine->render(
            'collection',
            $category,
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Page show
     */
    public function pageShow(Request $request, string $handle): Response
    {
        // For now, create a basic page structure
        // You can extend this with a Page model later
        $page = (object) [
            'title' => str_replace('-', ' ', $handle),
            'handle' => $handle,
            'content' => "Content for {$handle} page",
        ];

        $content = $this->templateEngine->render(
            'page',
            $page,
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Blog index
     */
    public function blogIndex(Request $request): Response
    {
        // Placeholder for blog functionality
        $blog = (object) [
            'title' => 'Blog',
            'handle' => 'blog',
            'articles' => collect([]),
        ];

        $content = $this->templateEngine->render(
            'blog',
            $blog,
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Blog article show
     */
    public function blogShow(Request $request, string $handle): Response
    {
        // Placeholder for article functionality
        $article = (object) [
            'title' => str_replace('-', ' ', $handle),
            'handle' => $handle,
            'content' => "Article content for {$handle}",
        ];

        $content = $this->templateEngine->render(
            'article',
            $article,
            $request->attributes->get('custom_template')
        );

        return response($content);
    }

    /**
     * Search results
     */
    public function search(Request $request): Response
    {
        $query = $request->get('q', '');
        $results = collect([]);

        if ($query) {
            $products = Product::where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->active()
                ->with(['media', 'variants'])
                ->limit(20)
                ->get();

            $results = $results->merge($products);
        }

        $searchData = (object) [
            'query' => $query,
            'results' => $results,
            'total_results' => $results->count(),
        ];

        $content = $this->templateEngine->render(
            'search',
            $searchData
        );

        return response($content);
    }

    /**
     * Cart page
     */
    public function cartShow(Request $request): Response
    {
        // Placeholder for cart functionality
        $cart = (object) [
            'lines' => collect([]),
            'total' => 0,
            'item_count' => 0,
        ];

        $content = $this->templateEngine->render(
            'cart',
            $cart
        );

        return response($content);
    }

    /**
     * Add to cart
     */
    public function cartAdd(Request $request)
    {
        // Cart logic will be implemented separately
        return response()->json(['success' => true]);
    }

    /**
     * Update cart
     */
    public function cartUpdate(Request $request, $line)
    {
        // Cart logic will be implemented separately
        return response()->json(['success' => true]);
    }

    /**
     * Remove from cart
     */
    public function cartRemove(Request $request, $line)
    {
        // Cart logic will be implemented separately
        return response()->json(['success' => true]);
    }

    /**
     * Account dashboard
     */
    public function accountDashboard(Request $request): Response
    {
        $customer = $request->user('customers');
        
        $content = $this->templateEngine->render(
            'customers/account',
            $customer
        );

        return response($content);
    }

    /**
     * Account orders
     */
    public function accountOrders(Request $request): Response
    {
        $customer = $request->user('customers');
        $orders = $customer->orders()->with('lines.product')->paginate(10);
        
        $content = $this->templateEngine->render(
            'customers/orders',
            (object) [
                'customer' => $customer,
                'orders' => $orders,
            ]
        );

        return response($content);
    }

    /**
     * Account order show
     */
    public function accountOrderShow(Request $request, $order): Response
    {
        $customer = $request->user('customers');
        $order = $customer->orders()->with('lines.product')->findOrFail($order);
        
        $content = $this->templateEngine->render(
            'customers/order',
            $order
        );

        return response($content);
    }

    /**
     * Template preview (Admin only)
     */
    public function templatePreview(Request $request, string $template): Response
    {
        // Sample data for preview
        $sampleData = [
            'title' => 'Preview Template',
            'handle' => 'preview',
            'content' => 'This is a preview of the template.',
        ];

        return response(
            $this->templateEngine->preview($template, $sampleData)
        );
    }
}

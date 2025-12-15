<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\CP;

use Cartino\Http\Controllers\Controller;
use Cartino\Http\Requests\DiscountRequest;
use Cartino\Models\Discount;
use Cartino\Services\DiscountService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountController extends Controller
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    public function index(Request $request): Response
    {
        $query = Discount::with(['applications'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'active');
            } elseif ($request->status === 'inactive') {
                $query->where('status', 'inactive');
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $discounts = $query->paginate(15)->withQueryString();

        // Add statistics to each discount
        $discounts->getCollection()->transform(function ($discount) {
            $discount->statistics = $this->discountService->getDiscountStatistics($discount);

            return $discount;
        });

        return Inertia::render('Discounts/index', [
            'discounts' => $discounts,
            'filters' => $request->only(['status', 'type', 'search']),
            'statistics' => $this->getOverallStatistics(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Discounts/Create', [
            'discount_types' => $this->getDiscountTypes(),
        ]);
    }

    public function store(DiscountRequest $request)
    {
        $discount = $this->discountService->createDiscount($request->validated());

        return redirect()
            ->route('cp.discounts.show', $discount)
            ->with('success', __('discount.messages.created_successfully'));
    }

    public function show(Discount $discount): Response
    {
        $discount->load(['applications.applicable']);
        $discount->statistics = $this->discountService->getDiscountStatistics($discount);

        return Inertia::render('Discounts/Show', [
            'discount' => $discount,
        ]);
    }

    public function edit(Discount $discount): Response
    {
        return Inertia::render('Discounts/Edit', [
            'discount' => $discount,
            'discount_types' => $this->getDiscountTypes(),
        ]);
    }

    public function update(DiscountRequest $request, Discount $discount)
    {
        $this->discountService->updateDiscount($discount, $request->validated());

        return redirect()
            ->route('cp.discounts.show', $discount)
            ->with('success', __('discount.messages.updated_successfully'));
    }

    public function destroy(Discount $discount)
    {
        $deleted = $this->discountService->deleteDiscount($discount);

        if ($deleted) {
            return redirect()
                ->route('cp.discounts.index')
                ->with('success', __('discount.messages.deleted_successfully'));
        }

        return back()->with('error', __('discount.messages.delete_failed'));
    }

    protected function getDiscountTypes(): array
    {
        return [
            'percentage' => __('discount.types.percentage'),
            'fixed_amount' => __('discount.types.fixed_amount'),
            'free_shipping' => __('discount.types.free_shipping'),
        ];
    }

    protected function getOverallStatistics(): array
    {
        return [
            'total_discounts' => Discount::count(),
            'active_discounts' => Discount::where('status', 'active')->count(),
            'total_applications' => \Cartino\Models\DiscountApplication::count(),
            'total_discount_amount' => \Cartino\Models\DiscountApplication::sum('discount_amount'),
        ];
    }
}

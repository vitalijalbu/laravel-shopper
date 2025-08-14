<?php

namespace VitaliJalbu\LaravelShopper\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => $this->getDashboardStats(),
        ]);
    }

    private function getDashboardStats(): array
    {
        return [
            'orders' => [
                'total' => 0, // TODO: Implement actual count
                'today' => 0,
                'pending' => 0,
                'processing' => 0,
            ],
            'products' => [
                'total' => 0, // TODO: Implement actual count
                'active' => 0,
                'draft' => 0,
                'low_stock' => 0,
            ],
            'customers' => [
                'total' => 0, // TODO: Implement actual count
                'new_this_month' => 0,
            ],
            'revenue' => [
                'today' => 0, // TODO: Implement actual calculation
                'this_month' => 0,
                'this_year' => 0,
            ],
        ];
    }
}

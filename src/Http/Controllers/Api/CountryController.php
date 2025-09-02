<?php

namespace LaravelShopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelShopper\Http\Controllers\Controller;
use LaravelShopper\Models\Country;

class CountryController extends Controller
{
    /**
     * Display a listing of countries
     */
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('iso3', 'like', "%{$search}%");
            });
        }

        // Region filter
        if ($region = $request->get('region')) {
            $query->where('region', $region);
        }

        // Status filter
        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        $perPage = $request->get('per_page', 25);
        $countries = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $countries->items(),
            'meta' => [
                'current_page' => $countries->currentPage(),
                'last_page' => $countries->lastPage(),
                'per_page' => $countries->perPage(),
                'total' => $countries->total(),
                'from' => $countries->firstItem(),
                'to' => $countries->lastItem(),
            ],
            'links' => [
                'first' => $countries->url(1),
                'last' => $countries->url($countries->lastPage()),
                'prev' => $countries->previousPageUrl(),
                'next' => $countries->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Display the specified country
     */
    public function show(string $code): JsonResponse
    {
        try {
            $country = Country::where('code', strtoupper($code))->firstOrFail();

            return response()->json([
                'data' => $country,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Paese non trovato',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get enabled countries only
     */
    public function enabled(): JsonResponse
    {
        try {
            $countries = Country::where('is_enabled', true)
                               ->orderBy('name')
                               ->get();

            return response()->json([
                'data' => $countries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei paesi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get countries grouped by region
     */
    public function byRegion(): JsonResponse
    {
        try {
            $countries = Country::where('is_enabled', true)
                               ->orderBy('region')
                               ->orderBy('name')
                               ->get()
                               ->groupBy('region');

            return response()->json([
                'data' => $countries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei paesi per regione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all regions
     */
    public function regions(): JsonResponse
    {
        try {
            $regions = Country::select('region')
                             ->distinct()
                             ->whereNotNull('region')
                             ->orderBy('region')
                             ->pluck('region');

            return response()->json([
                'data' => $regions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero delle regioni',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search countries for autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;

            $countries = Country::where('is_enabled', true)
                               ->where(function ($q) use ($query) {
                                   $q->where('name', 'like', "%{$query}%")
                                     ->orWhere('code', 'like', "%{$query}%");
                               })
                               ->orderBy('name')
                               ->limit($limit)
                               ->get(['code', 'name', 'flag_emoji']);

            return response()->json([
                'data' => $countries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la ricerca',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

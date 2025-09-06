<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Models\Country;
use Shopper\Repositories\CountryRepository;

class CountryController extends ApiController
{
    public function __construct(
        private readonly CountryRepository $countryRepository
    ) {}

    /**
     * Display a listing of countries
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = $request->get('per_page', 50);
        
        $countries = $this->countryRepository->getPaginatedWithFilters($filters, $perPage);
        
        return $this->paginatedResponse($countries);
    }

    /**
     * Get enabled countries for select
     */
    public function enabled(): JsonResponse
    {
        $countries = $this->countryRepository->getEnabled();
        
        return $this->successResponse($countries->map(fn($country) => [
            'code' => $country->code,
            'name' => $country->name,
        ]));
    }

    /**
     * Get countries grouped by region
     */
    public function byRegion(): JsonResponse
    {
        $countries = $this->countryRepository->getByRegion();
        
        return $this->successResponse($countries);
    }

    /**
     * Get list of regions
     */
    public function regions(): JsonResponse
    {
        $regions = $this->countryRepository->getRegions();
        
        return $this->successResponse($regions);
    }

    /**
     * Search countries
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        $limit = $request->get('limit', 10);
        
        $filters = ['search' => $search, 'is_active' => true];
        $countries = $this->countryRepository->getPaginatedWithFilters($filters, $limit);
        
        return $this->successResponse($countries->items());
    }
}
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
            ],
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

<?php

namespace LaravelShopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelShopper\Http\Controllers\Controller;
use LaravelShopper\Models\Channel;

class ChannelController extends Controller
{
<?php

namespace LaravelShopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelShopper\Http\Controllers\Controller;
use LaravelShopper\Http\Traits\ApiResponseTrait;
use LaravelShopper\Models\Channel;

class ChannelController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of channels
     */
    public function index(Request $request): JsonResponse
    {
        $query = Channel::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        // Default filter
        if ($request->has('is_default')) {
            $query->where('is_default', $request->boolean('is_default'));
        }

        $perPage = $request->get('per_page', 25);
        $channels = $query->orderBy('is_default', 'desc')
                         ->orderBy('name')
                         ->paginate($perPage);

        return $this->paginatedResponse($channels);
    }

    /**
     * Store a newly created channel
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:channels,slug',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Ensure only one default channel
        if ($validated['is_default'] ?? false) {
            Channel::where('is_default', true)->update(['is_default' => false]);
        }

        try {
            $channel = Channel::create($validated);
            return $this->createdResponse($channel, 'Canale creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la creazione del canale');
        }
    }

    /**
     * Display the specified channel
     */
    public function show(string $id): JsonResponse
    {
        try {
            $channel = Channel::findOrFail($id);
            return $this->successResponse($channel);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Canale non trovato');
        }
    }

    /**
     * Update the specified channel
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:channels,slug,' . $id,
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        try {
            $channel = Channel::findOrFail($id);

            // Ensure only one default channel
            if ($validated['is_default'] ?? false) {
                Channel::where('id', '!=', $id)
                       ->where('is_default', true)
                       ->update(['is_default' => false]);
            }

            $channel->update($validated);
            return $this->successResponse($channel->fresh(), 'Canale aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento del canale');
        }
    }

    /**
     * Remove the specified channel
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $channel = Channel::findOrFail($id);

            // Prevent deletion of default channel
            if ($channel->is_default) {
                return $this->validationErrorResponse('Impossibile eliminare il canale predefinito');
            }

            $channel->delete();
            return $this->successResponse(null, 'Canale eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'eliminazione del canale');
        }
    }
}

<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\Http\Requests\Api\StoreGlobalRequest;
use Cartino\Http\Requests\Api\UpdateGlobalRequest;
use Cartino\Http\Resources\GlobalResource;
use Cartino\Models\Global;
use Cartino\Repositories\GlobalRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalsController extends ApiController
{
    public function __construct(
        private readonly GlobalRepository $repository
    ) {}

    /**
     * Display a listing of globals
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Global::class);
        
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified global
     */
    public function show(int|string $handleOrId): JsonResponse
    {
        $data = $this->repository->findOne($handleOrId);

        if (!$data) {
            return $this->errorResponse('Global non trovato', 404);
        }

        $this->authorize('view', $data);

        return $this->successResponse(new GlobalResource($data));
    }

    /**
     * Store a newly created global
     */
    public function store(StoreGlobalRequest $request): JsonResponse
    {
        $this->authorize('create', Global::class);
        
        try {
            $global = $this->repository->createOne($request->validated());

            return $this->created(new GlobalResource($global), 'Global creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del global: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified global
     */
    public function update(UpdateGlobalRequest $request, Global $global): JsonResponse
    {
        $this->authorize('update', $global);
        
        try {
            $updatedGlobal = $this->repository->updateOne($global->id, $request->validated());

            return $this->successResponse(new GlobalResource($updatedGlobal), 'Global aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del global: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified global
     */
    public function destroy(Global $global): JsonResponse
    {
        $this->authorize('delete', $global);
        
        try {
            $this->repository->deleteOne($global->id);

            return $this->successResponse(null, 'Global eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del global: ' . $e->getMessage());
        }
    }

    /**
     * Get global by handle
     */
    public function byHandle(string $handle): JsonResponse
    {
        $global = $this->repository->getByHandle($handle);

        if (!$global) {
            return $this->errorResponse('Global non trovato', 404);
        }

        return $this->successResponse(new GlobalResource($global));
    }

    /**
     * Update global data by handle
     */
    public function updateByHandle(Request $request, string $handle): JsonResponse
    {
        $request->validate([
            'data' => ['required', 'array'],
        ]);

        try {
            $global = $this->repository->updateByHandle($handle, $request->data);

            if (!$global) {
                return $this->errorResponse('Global non trovato', 404);
            }

            return $this->successResponse(new GlobalResource($global), 'Global aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del global: ' . $e->getMessage());
        }
    }

    /**
     * Set a specific value in global data
     */
    public function setValue(Request $request, string $handle): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string'],
            'value' => ['required'],
        ]);

        try {
            $global = $this->repository->setValue($handle, $request->key, $request->value);

            if (!$global) {
                return $this->errorResponse('Global non trovato', 404);
            }

            return $this->successResponse(new GlobalResource($global), 'Valore aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del valore: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific value from global data
     */
    public function getValue(string $handle, string $key): JsonResponse
    {
        $value = $this->repository->getValue($handle, $key);

        return $this->successResponse([
            'handle' => $handle,
            'key' => $key,
            'value' => $value,
        ]);
    }
}

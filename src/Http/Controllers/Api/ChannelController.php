<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Requests\Api\StoreChannelRequest;
use Shopper\Http\Requests\Api\UpdateChannelRequest;
use Shopper\Http\Resources\ChannelResource;
use Shopper\Models\Channel;
use Shopper\Repositories\ChannelRepository;

class ChannelController extends ApiController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository
    ) {}

    /**
     * Display a listing of channels
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_default', 'is_active']);
        $perPage = $request->get('per_page', 25);
        
        $channels = $this->channelRepository->getPaginatedWithFilters($filters, $perPage);
        
        return $this->paginatedResponse($channels);
    }

    /**
     * Store a newly created channel
     */
    public function store(StoreChannelRequest $request): JsonResponse
    {
        try {
            $channel = $this->channelRepository->create($request->validated());
            
            return $this->created(new ChannelResource($channel), 'Channel creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del channel: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified channel
     */
    public function show(Channel $channel): JsonResponse
    {
        return $this->successResponse(new ChannelResource($channel));
    }

    /**
     * Update the specified channel
     */
    public function update(UpdateChannelRequest $request, Channel $channel): JsonResponse
    {
        try {
            $updatedChannel = $this->channelRepository->update($channel->id, $request->validated());
            
            return $this->successResponse(new ChannelResource($updatedChannel), 'Channel aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del channel: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified channel
     */
    public function destroy(Channel $channel): JsonResponse
    {
        try {
            if (!$this->channelRepository->canDelete($channel->id)) {
                return $this->errorResponse('Impossibile eliminare il channel: è quello di default o ha prodotti/ordini associati', 422);
            }

            $this->channelRepository->delete($channel->id);
            
            return $this->successResponse(null, 'Channel eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del channel: ' . $e->getMessage());
        }
    }

    /**
     * Toggle channel status
     */
    public function toggleStatus(Channel $channel): JsonResponse
    {
        try {
            $updatedChannel = $this->channelRepository->toggleStatus($channel->id);
            
            return $this->successResponse(new ChannelResource($updatedChannel), 'Stato del channel aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: ' . $e->getMessage());
        }
    }

    /**
     * Set channel as default
     */
    public function setDefault(Channel $channel): JsonResponse
    {
        try {
            $updatedChannel = $this->channelRepository->setAsDefault($channel->id);
            
            return $this->successResponse(new ChannelResource($updatedChannel), 'Channel impostato come default');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'impostazione default: ' . $e->getMessage());
        }
    }

    /**
     * Get active channels for select
     */
    public function select(): JsonResponse
    {
        $channels = $this->channelRepository->getActive();
        
        return $this->successResponse($channels->map(fn($channel) => [
            'id' => $channel->id,
            'name' => $channel->name,
            'slug' => $channel->slug,
        ]));
    }

    /**
     * Bulk operations
     */
    public function bulk(Request $request): JsonResponse
    {
        $action = $request->get('action');
        $ids = $request->get('ids', []);

        if (empty($ids)) {
            return $this->validationErrorResponse('Nessun ID selezionato');
        }

        try {
            switch ($action) {
                case 'activate':
                    $count = $this->channelRepository->bulkUpdateStatus($ids, true);
                    return $this->bulkActionResponse('attivazione', $count);

                case 'deactivate':
                    $count = $this->channelRepository->bulkUpdateStatus($ids, false);
                    return $this->bulkActionResponse('disattivazione', $count);

                case 'delete':
                    $errors = [];
                    $deleted = 0;
                    
                    foreach ($ids as $id) {
                        if ($this->channelRepository->canDelete($id)) {
                            $this->channelRepository->delete($id);
                            $deleted++;
                        } else {
                            $errors[] = "Channel ID {$id} non può essere eliminato";
                        }
                    }
                    
                    return $this->bulkActionResponse('eliminazione', $deleted, $errors);

                default:
                    return $this->validationErrorResponse('Azione non riconosciuta');
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'operazione bulk: ' . $e->getMessage());
        }
    }
}
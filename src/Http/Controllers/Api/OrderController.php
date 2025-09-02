<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Controllers\Controller;
use Shopper\Http\Requests\Api\BulkOrderActionRequest;
use Shopper\Http\Requests\Api\MarkOrderAsShippedRequest;
use Shopper\Http\Requests\Api\StoreOrderRequest;
use Shopper\Http\Requests\Api\UpdateOrderRequest;
use Shopper\Http\Traits\ApiResponseTrait;
use Shopper\Repositories\OrderRepository;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'payment_status', 'fulfillment_status', 'customer_id', 'date_from', 'date_to']);
        $perPage = $request->get('per_page', 25);

        $orders = $this->orderRepository->getPaginatedWithFilters($filters, $perPage);

        return $this->paginatedResponse($orders);
    }

    /**
     * Store a newly created order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderRepository->create($request->validated());

            return $this->createdResponse($order, 'Ordine creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la creazione dell\'ordine');
        }
    }

    /**
     * Display the specified order
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->find($id);
            return $this->successResponse($order);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Ordine non trovato');
        }
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->update($id, $request->validated());

            return $this->successResponse($order, 'Ordine aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento dell\'ordine');
        }
    }

    /**
     * Cancel the specified order
     */
    public function cancel(string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->cancel($id);

            return $this->successResponse($order, 'Ordine cancellato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la cancellazione dell\'ordine');
        }
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->markAsPaid($id);

            return $this->successResponse($order, 'Ordine segnato come pagato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento del pagamento');
        }
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(MarkOrderAsShippedRequest $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->markAsShipped($id, $request->validated());

            return $this->successResponse($order, 'Ordine segnato come spedito');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento della spedizione');
        }
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->markAsDelivered($id);

            return $this->successResponse($order, 'Ordine segnato come consegnato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento della consegna');
        }
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);

        try {
            $statistics = $this->orderRepository->getStatistics($filters);

            return $this->successResponse($statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante il recupero delle statistiche');
        }
    }

    /**
     * Handle bulk actions
     */
    public function bulk(BulkOrderActionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->orderRepository->bulkAction($validated['action'], $validated['ids'], $validated['metadata'] ?? []);

            return $this->bulkActionResponse($result['count'], "Azione '{$validated['action']}' eseguita", $result);
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'esecuzione dell\'azione bulk');
        }
    }
}

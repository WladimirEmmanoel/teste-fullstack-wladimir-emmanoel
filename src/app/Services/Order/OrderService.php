<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Repositories\OrderRepository;
use App\Repositories\OrderMetricsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderMetricsRepository $OrderMetricsRepository
    ) {}

    /**
     * Filtragem e paginação dos pedidos
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($filters);
    }


    /**
     * Filtragem e paginação dos pedidos
     */
    public function show(Order $order): Order
    {
        return $this->orderRepository->findWithRelations($order);
    }

    /**
     * Pega as metricas dos pedidos e armazena em cache por 5m
     */
    public function metrics(): array
    {
        return Cache::store('redis')->remember(
            'orders:metrics',
            now()->addMinutes(5),
            fn() => [
                'data' => $this->OrderMetricsRepository->metrics(),
                'cached_time' => now()->toISOString(),
            ]
        );
    }

    /**
     * Atualiza o status do pedido validando a máquina de estados.
     */
    public function updateStatus(Order $order, int $newStatus): Order
    {
        $currentStatus = $order->status;
        $newStatusEnum = OrderStatus::from($newStatus);
        
        // Valida transição de status conforme regras da máquina de estados
        if (! $currentStatus->canTransitionTo($newStatusEnum)) {
            throw ValidationException::withMessages([
                'status' => "Transição inválida de {$currentStatus->name} para {$newStatusEnum->name}",
            ]);
        }

        DB::transaction(function () use ($order, $currentStatus, $newStatusEnum) {
            // Atualiza o Status do Order
            $this->orderRepository->updateStatus($order, $newStatusEnum);

            // Cria o historico do OrderStatus
            $this->orderRepository->createOrderStatusLog($order, $currentStatus, $newStatusEnum);
        });

        // Remove o cache das metricas
        Cache::store('redis')->forget('orders:metrics');

        return $this->orderRepository
            ->findWithRelations($order->refresh());
    }
}
<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /**
     * Retorna os pedidos páginados e filtrados
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Order::query();

        if (! empty($filters['affiliate_id'])) {
            $query->where('affiliate_id', $filters['affiliate_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['min_value'])) {
            $query->where('total_value', '>=', $filters['min_value']);
        }

        if (! empty($filters['max_value'])) {
            $query->where('total_value', '<=', $filters['max_value']);
        }

        $query->orderBy(
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_dir'] ?? 'desc'
        );

        return $query->paginate(20);
    }

    /**
     * Retorna os detalhes do pedido
     */
    public function findWithRelations(Order $order): Order
    {
        return $order->load([
            'affiliate',
            'items.product',
            'statusLogs',
        ]);
    }

    /**
     * Atualiza o Status do Order
     */
    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->update([
            'status' => $status,
        ]);

        return $order->fresh();
    }

    /**
     * Cria o log do OrderStatus
     */
    public function createOrderStatusLog(Order $order, OrderStatus $oldStatus, OrderStatus $newStatus): OrderStatusLog
    {
        return OrderStatusLog::create([
            'order_id'   => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }
}
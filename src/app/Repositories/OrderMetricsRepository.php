<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Affiliate;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

class OrderMetricsRepository
{
    /**
     * Retorna métricas de todos os pedidos.
     */
    public function metrics(): array
    {
        return $this->metricsFromQuery(
            Order::query()
        );
    }

    /**
     * Retorna métricas dos pedidos de um afiliado.
     */
    public function affiliateMetrics(Affiliate $affiliate): array
    {
        return $this->metricsFromQuery(
            Order::query()->where('affiliate_id', $affiliate->id)
        );
    }

    /**
     * Calcula métricas a partir de uma consulta de pedidos.
     */
    private function metricsFromQuery(Builder $query): array
    {
        $totalOrders = (clone $query)->count();

        return [
            // Total de pedidos
            'total_orders' => $totalOrders,

            // Receita total
            'total_revenue' => (float) (clone $query)
                ->sum('total_value'),

            // Ticket médio
            'avg_ticket' => round(
                (float) (clone $query)->avg('total_value'),
                2
            ),

            // Quantidade de pedidos por status
            'status_distribution' => (clone $query)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),

            // Porcentagem de cancelamento
            'cancel_rate' => $this->cancelRate(
                $query,
                $totalOrders
            ),
        ];
    }

    /**
     * Calcula a porcentagem  de cancelamento.
     */
    private function cancelRate(Builder $query, int $totalOrders): float
    {
        if ($totalOrders === 0) {
            return 0;
        }

        $cancelledOrders = (clone $query)
            ->where('status', OrderStatus::Cancelled->value)
            ->count();

        return round(
            ($cancelledOrders / $totalOrders) * 100,
            2
        );
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Support\ApiResponse;
use App\Services\Order\OrderService;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderIndexRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Retornas os pedidos páginados e filtrados
     */
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $orders = $this->orderService->paginate(
            $request->filters()
        );

        return ApiResponse::success(
            $orders->items(),
            [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ]
        );
    }

    /**
     * Retorna os detalhes do pedido
     */
    public function show(Order $order): JsonResponse
    {
        return ApiResponse::success(
            $this->orderService->show($order)
        );
    }

    /**
     * Retorna as metricas
     */
    public function metrics(): JsonResponse
    {
        $metrics = $this->orderService->metrics();

        return ApiResponse::success(
            data: $metrics['data'],
            meta: [
                'cached_time' => $metrics['cached_time'],
            ]
        );
    }

    /**
     * Retorna o Order atualizado
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        return ApiResponse::success(
            $this->orderService->updateStatus(
                $order,
                $request->integer('status')
            )
        );
    }
}
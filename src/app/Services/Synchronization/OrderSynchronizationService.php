<?php

namespace App\Services\Synchronization;

use App\Enums\OrderStatus;
use App\Models\Affiliate;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Services\FakeStore\AffiliateService;
use App\Services\FakeStore\ProductService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrderSynchronizationService
{
    public function __construct(
        private AffiliateService $affiliateService,
        private ProductService $productService
    ) {}

    public function sync(array $orders): void
    {
        $affiliates = collect(
            $this->affiliateService
                ->all()
                ->throw()
                ->json()
        )->keyBy('id');

        $products = collect(
            $this->productService
                ->all()
                ->throw()
                ->json()
        )->keyBy('id');

        foreach ($orders as $orderData) {


            $affiliateId = (int) $orderData['userId'];

            // Verifica se o Afiliado existe
            if (! $affiliates->has($affiliateId)) {
                throw new \RuntimeException("Afiliado {$affiliateId} não existe.");
            }

            // Cadastra afiliado
            $affiliate = $this->syncAffiliate(
                $affiliates,
                $affiliateId
            );

                        
            // Verifica se o Produto existe
            if (collect($orderData['products'])->pluck('productId')->diff($products->keys())->isNotEmpty()) {
                throw new \RuntimeException("Um ou mais produtos do pedido  não existem.");
            }

            // Cadastra o Produto
            $persistedProducts = $this->syncProducts(
                $products,
                $orderData['products']
            );

            // Cadastra o pedido
            $this->syncOrder(
                $affiliate,
                $persistedProducts,
                $orderData
            );
        }
    }

    private function syncAffiliate(Collection $affiliates, int $affiliateId): Affiliate
    {
        
        $affiliateData = $affiliates->get($affiliateId);
    
        // Verifica se o afiliado existe
        if (! $affiliateData) {
            throw new \RuntimeException("Afiliado {$affiliateId} não encontrado.");
        }

        $this->validateAffiliate($affiliateData);

        return Affiliate::updateOrCreate(
            [
                'external_id' => $affiliateData['id'],
            ],
            [
                'username' => $affiliateData['username'],
                'email' => $affiliateData['email'],
                'password' => Hash::make($affiliateData['password'])
            ]
        );
    }

    private function validateAffiliate(array $affiliateData): void
    {
        Validator::make($affiliateData, [
            'id' => 'required|integer',
            'username' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:2',
        ])->validate();
    }

    private function syncProducts(Collection $products, array $orderProducts): Collection {

        // Armazena os produtos persistidos indexados pelo external_id
        $persistedProducts = collect();

        foreach ($orderProducts as $item) {

            $productData = $products->get($item['productId']);

            // Verifica se o produto existe
            if (! $productData) {
                throw new \RuntimeException(
                    sprintf('Produto %d não encontrado.', $item['productId'])
                );
            }
                        
            $this->validateProduct($productData);

            $product = Product::updateOrCreate(
                [
                    'external_id' => $productData['id'],
                ],
                [
                    'title' => $productData['title'],
                    'price' => $productData['price'],
                    'description' => $productData['description'],
                    'category' => $productData['category'],
                    'image' => $productData['image'],
                ]
            );

            $persistedProducts->put(
                $product->external_id,
                $product
            );
        }

        return $persistedProducts;
    }

    private function validateProduct(array $productData): void
    {
        Validator::make($productData, [
            'id' => 'required|integer',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'category' => 'required|string',
            'image' => 'required|string',
        ])->validate();
    }

    private function syncOrder(Affiliate $affiliate, Collection $products, array $orderData): Order 
    {
        // Pega o valor total do pedido
        $total = $this->calculateOrderTotal($products, $orderData['products'], $orderData['id']);

        return DB::transaction(function () use ($affiliate, $products, $orderData, $total) {
    
            $status = OrderStatus::Pending->value;
            // Pega o pedido se já estiver cadastrado
            $order = Order::firstWhere(
                'external_id',
                $orderData['id']
            );

            // Se o pedido já existir e o status dele foi alterado, cria o log
            if ($order && $order->status !== $status) {

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'old_status' => $order->status,
                    'new_status' => $status,
                ]);
            }

            // Cria ou atualiza o pedido
            $order = Order::updateOrCreate(
                [
                    'external_id' => $orderData['id'],
                ],
                [
                    'affiliate_id' => $affiliate->id,
                    'status' => $status,
                    'total_value' => $total,
                ]
            );

            foreach ($orderData['products'] as $item) {

                $product = $products->get($item['productId']);

                OrderItem::updateOrCreate(
                    [
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'price'    => $product->price,
                    ]
                );
            }

            return $order;
        });
    }

    private function calculateOrderTotal(Collection $products, array $orderProducts, int $orderId): float 
    {
        $total = 0;

        foreach ($orderProducts as $item) {

            $product = $products->get($item['productId']);

            if (! $product) {
                throw new \RuntimeException(
                    "Produto {$item['productId']} não encontrado ao calcular pedido {$orderId}"
                );
            }

            $total += $product->price * $item['quantity'];
        }

        return $total;
    }
}
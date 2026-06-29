<?php

namespace App\Services\FakeStore;

use Illuminate\Http\Client\Response;

class OrderService extends BaseService
{

    /**
     * Retorna todos os pedidos.
     */
    public function all(): Response
    {
        return $this->client()->get('/carts');
    }

    /**
     * Cria um novo pedido.
     */
    public function create(array $data): Response
    {
        return $this->client()->post('/carts', $data);
    }

    /**
     * Retorna um pedido específico.
     */
    public function find(int $id): Response
    {
        return $this->client()->get("/carts/{$id}");
    }

    /**
     * Atualiza um pedido específico.
     */
    // public function update(int $id, array $data): Response
    // {
    //     return $this->client()->put("/carts/{$id}", $data);
    // }

    /**
     * Remove um pedido específico.
     */
    // public function delete(int $id): Response
    // {
    //     return $this->client()->delete("/carts/{$id}");
    // }
}
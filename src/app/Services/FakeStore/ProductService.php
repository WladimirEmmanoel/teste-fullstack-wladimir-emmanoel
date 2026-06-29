<?php

namespace App\Services\FakeStore;

use Illuminate\Http\Client\Response;

class ProductService extends BaseService
{

    /**
     * Retorna todos os produtos.
     */
    public function all(): Response
    {
        return $this->client()->get('/products');
    }

    /**
     * Retorna um produto específico.
     */
    public function find(int $id): Response
    {
        return $this->client()->get("/products/{$id}");
    }

}
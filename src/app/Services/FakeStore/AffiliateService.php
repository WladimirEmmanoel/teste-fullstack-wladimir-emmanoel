<?php

namespace App\Services\FakeStore;

use Illuminate\Http\Client\Response;

class AffiliateService extends BaseService
{

    /**
     * Retorna todos os afiliados.
     */
    public function all(): Response
    {
        return $this->client()->get('/users');
    }

    /**
     * Retorna um afiliado específico.
     */
    public function find(int $id): Response
    {
        return $this->client()->get("/users/{$id}");
    }

}
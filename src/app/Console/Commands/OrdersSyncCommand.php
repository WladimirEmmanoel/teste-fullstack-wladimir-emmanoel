<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrdersPageJob;
use App\Services\FakeStore\OrderService;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class OrdersSyncCommand extends Command
{
    private const CHUNK_SIZE = 10;

    protected $signature = 'orders:sync';
    protected $description = 'Sincroniza os pedidos da FakeStore API';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $this->info('Percorrendo os pedidos da FakeStore...');

        $response = $orderService->all();

        if (! $response->successful()){
            $this->error('Não foi possível recuperar os pedidos.');
            return self::FAILURE;
        }

        // Transforma o Json em uma collection
        $orders = collect($response->json());

        if ($orders->isEmpty()) {
            $this->warn('Nenhum pedido encontrado.');

            return self::SUCCESS;
        }

        // Limita a quantidade de pedidos por fila
        $orderChunks = $orders->chunk(self::CHUNK_SIZE);
        
        $orderChunks->each(function(Collection $chunk){
            SyncOrdersPageJob::dispatch($chunk->values()->all());
        });
            
        $this->info(sprintf(
            '%d pedidos distribuídos em %d job(s).',
            $orders->count(),
            $orderChunks->count()
        ));

        return self::SUCCESS;
    }
}

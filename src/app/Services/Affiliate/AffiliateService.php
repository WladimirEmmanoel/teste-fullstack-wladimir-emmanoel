<?php

namespace App\Services\Affiliate;

use App\Models\Affiliate;
use App\Repositories\OrderMetricsRepository;

class AffiliateService
{
    public function __construct(private OrderMetricsRepository $OrderMetricsRepository) {}

    /**
     * Retorna o resumo do afiliado.
     */
    public function summary(Affiliate $affiliate): array
    {   
        // Pega as metricas do affiliado
        $metrics = $this->OrderMetricsRepository->affiliateMetrics($affiliate);
        unset($metrics['status_distribution']);

        return [
            'affiliate' => $affiliate,
            'metrics' => $metrics,
        ];
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Support\ApiResponse;
use App\Services\Affiliate\AffiliateService;

use App\Models\Affiliate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AffiliateController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    /**
     * Retorna o resumo do afiliado.
     */
    public function summary(Affiliate $affiliate): JsonResponse
    {
        return ApiResponse::success(
            $this->affiliateService->summary($affiliate)
        );
    }
}

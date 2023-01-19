<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;

class IbgeRestIntegrationService extends BaseRestIntegrationService
{
    /**
     * Retorna todos os estados brasileiros
     *
     * @return array
     */
    public function getStates()
    {
        $uri = env('IBGE_REST_INTEGRATION_HOST', 'http://servicodados.ibge.gov.br/api/v1/') . '/localidades/estados';
        $request = Http::get($uri);

        return $request->json();
    }

    /**
     * Retorna todas as cidades de um determinado estado brasileiro
     *
     * @param int $stateIbgeId
     * @return mixed
     */
    public function getCitiesByState(int $stateIbgeId)
    {
        $uri = env('IBGE_REST_INTEGRATION_HOST', 'http://servicodados.ibge.gov.br/api/v1/') . "/localidades/estados/{$stateIbgeId}/municipios";
        $request = Http::get($uri);

        return $request->json();
    }
}

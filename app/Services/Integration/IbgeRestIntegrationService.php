<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IbgeRestIntegrationService
{
    /**
     * Obtem url de destino endpoint get estados integração
     *
     * @param String $state
     * @return String
     */
    public function getUriStates(): string
    {
        return config('ibge.integration.host') . config('ibge.integration.states');
    }

    /**
     * Obtem url de destino endpoint get cidades integração
     *
     * @param String $state
     * @return String
     */
    public function getUriCities($state): string
    {
        return config('ibge.integration.host') . config('ibge.integration.prefix') . "/" . $state . config('ibge.integration.cities');
    }

    /**
     * Retorna todos os estados brasileiros
     *
     */
    public function getStates()
    {
        $uri = $this->getUriStates();

        try {
            $request = Http::get($uri);
            return $request->json();
        }
        catch (HttpException $e){
            return $e->getMessage();
        }
    }

    /**
     * Retorna todas as cidades de um determinado estado brasileiro
     *
     * @param String $stateIbge
     */
    public function getCitiesByState(String $stateIbge)
    {
        $uri = $this->getUriCities($stateIbge);

        try {
            $request = Http::get($uri);
            return $request->json();
        }
        catch (HttpException $e){
            return $e->getMessage();
        }
    }
}

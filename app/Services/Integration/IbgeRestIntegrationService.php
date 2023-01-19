<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IbgeRestIntegrationService
{
    public function getUriStates(): string
    {
        return config('ibge.integration.host') . config('ibge.integration.states');
    }

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

        echo $uri;

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
     * @param int $stateIbgeId
     * @return mixed
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

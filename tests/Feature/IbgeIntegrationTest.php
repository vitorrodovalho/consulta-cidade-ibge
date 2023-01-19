<?php

namespace Tests\Feature;

use App\Services\Integration\IbgeRestIntegrationService;
use Tests\TestCase;

class IbgeIntegrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_ibge_api_integration()
    {
        $ibgeIntegration = new IbgeRestIntegrationService();

        $result = $ibgeIntegration->getStates();
        self::assertArrayHasKey('nome', $result[0]);
        self::assertArrayHasKey('sigla', $result[0]);

        $result = $ibgeIntegration->getCitiesByState("SP");
        self::assertArrayHasKey('nome', $result[0]);
    }
}

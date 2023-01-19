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

        $response = $this->withHeaders($this->headerJson)->getJson($ibgeIntegration->getUriStates());
        $response->assertStatus(200);

        $response = $this->getJson($ibgeIntegration->getUriCities("SP"));
        $response->assertStatus(200);
    }
}

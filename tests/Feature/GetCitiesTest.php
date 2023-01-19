<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetCitiesTest extends TestCase
{
    /**
     * Teste get municipios geral validacão se esta retornando os dados conforme
     * padrão esperado
     *
     * @return void
     */
    public function test_get_cities()
    {
        $response = $this->withHeaders($this->headerJson)->getJson('/api/cities/SP?q=Jarinu123');
        $response->assertStatus(200);
        $response->assertJsonMissing(
            [
                'name',
                'ibge_code'
            ]
        );
    }

    /**
     * Teste de pesquisa de cidade especifica
     * Garante que cidade retornada esta sendo retornada
     *
     * @return void
     */
    public function test_get_search_city()
    {
        $response = $this->withHeaders($this->headerJson)->getJson('/api/cities/SP?q=Limeira');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            "name" => "Limeira"
        ]);
    }
}

## Sobre

Api pesquisa e consulta municípios IBGE, integrado com API's públicas para fornecer os dados. A fonte pode ser definida na configuração, inserindo o host de destino do provider. O sitema irá consumir a api de destino de tempo em tempo, atualizando e sincronizando os dados armazenados no banco de dados.

Benefícios da arquitetura implementada:
- Garantia de disponibilidade  
- Independência do sistema terceiro
- Tratamentos de erros mediante a alteração do corpo e api de destino
- Velocidade na obtenção da informação com cache implementada

Tecnologias utilizadas
- Banco de dados MySQL
- Redis como cache

## Estrutura
### Serviço
Para implementação da integração com o sistema terceiro que irá obter os dados dos munícipios e estados, foi criado um serviço dentro do escopo *'App/Services/Integration'*; 
```
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
```
A classe irá se comuniciar com o endpoint de destino, obtendo os dados e retornando o conteúdo da resposta.

### Comando Import
Para obtenção dos dados e execução do serviço de comunicação com o endpoint foi criado em *Commands*, um comanda para execução da sincronização/criação dos dados.
```
ibge:import-states-cities
```
Esse serviço está disponível e foi configurado no schedule para ser executado de tempo em tempo.
No servidor precisa ser configurado um serviço **cron** para ser executado e o schedule ser feito periodicamente conforme implementado.
```
$schedule->command('ibge:import-states-cities')->daily();
```
A função de obtenção dos municipios é executada juntamente com a de estados, toda vez que o comando é disparado.
```
private function importCities()
{
    $states = State::all();
    foreach ($states as $state) {
        $cities = $this->ibgeService->getCitiesByState($state->acronym);

        foreach ($cities as $value) {
            $dataCity = [
                'ibge_id' => $value['id'] ?? $value['codigo_ibge'],
                'name' => $value['nome'],
                'state_id' => $state->id,
            ];

            if (!$city = City::where('ibge_id', $value['id'] ?? $value['codigo_ibge'])->first()) {
                City::create($dataCity);
                continue;
            }

            $city->update($dataCity);
        }
    }
}
```
A função de import das cidades irá consultar estado a estado, e caso a cidade retornada pelo endpoint de destino não estiver cadastrada no banco ainda, irá criar o registro, do contrário o registro será atualizado.

### Api
Para consultar as cidades de um determinado estado, foi incluído nas rotas API, o seguinte uri 'cities/{state}', onde state será o sigla do estado passado como referencia.
```
Route::get('cities/{state}', [CityController::class, 'index']);
```
Na função da api, primeiramente será feito a validação do estado passado se é existe no registros de estados cadastrados, após será verificado o filtro passado na requisão, quando informado, o sistema não utilizará cache para obtenção dos resultados. Se não informado o sistema armzenerá o cache no banco redis com o prefixo para cada estado e página.
```
public function index(getCitiesRequest $request, $state)
{
    // Valida o estado passado como referencia
    if(State::where('acronym', $state)->first() == null) {
        return response(['message' => 'UF estado informado inválido'], 404);
    }

    if ($request->has('page'))
        $page = $request->page;
    else
        $page = 1;

    $expiration = 1440; // Tempo em minutos para expirar cache
    $key = 'cities_' . $state . "_" . $page;

    $qb = City::query();
    $qb->select('cities.name', 'cities.ibge_id as ibge_code');
    $qb->where('acronym', $state);

    if ($request->has('q')) {
        $qb->where(function($query) use ($qb, $request) {
            $query->where('cities.name', 'Like', '%' . $request->q. '%');
        });

        $qb->join('states', 'states.id', '=', 'state_id');
        return response($qb->paginate());
    }
    else {
        return Cache::remember($key, $expiration, function () use ($state, $request, $qb) {
            $qb->join('states', 'states.id', '=', 'state_id');
            return response($qb->paginate());
        });
    }
}
```

### Banco de dados
Foi utilizado o banco de dados MySQL 8 para armazenamento das cidades e estaods conforme estrutura das tabelas abaixo:
#### Estado
| Coluna         | Tipo         |
|----------------|--------------|
| id             | BIGINT       |
| ibge_id        | BIGINT       |
| ibge_region_id | BIGINT       |
| name           | VARCHAR(255) |
| acronym        | VARCHAR(255) |
| region_name    | VARCHAR(255) |
| region_acronym | VARCHAR(255) |
| created_at     | TIMESTAMP    |
| updated_at     | TIMESTAMP    |

#### Cidade
| Coluna     | Tipo         |
|------------|--------------|
| id         | BIGINT       |
| state_id   | BIGINT       |
| ibge_id    | BIGINT       |
| name       | VARCHAR(255) |
| created_at | TIMESTAMP    |
| updated_at | TIMESTAMP    |

### Testes
Implementação do teste da aplicação API GET, garantindo que o retorno e o endpoint está executando corretamente.
```
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
```
Implementação do teste de integração com o sistema disponibilizado terceiro para obtenção dos dados IBGE, é feito a busca das cidades e estados e feito a validação se estão dentro do padrão esperado do sistema.

```
public function test_ibge_api_integration()
{
    $ibgeIntegration = new IbgeRestIntegrationService();

    $result = $ibgeIntegration->getStates();
    self::assertArrayHasKey('nome', $result[0]);
    self::assertArrayHasKey('sigla', $result[0]);

    $result = $ibgeIntegration->getCitiesByState("SP");
    self::assertArrayHasKey('nome', $result[0]);
}
```
### Configuração
Foi utilizado e realizado os testes com 2 API's públicas que disponibilizam dos dados:

#### IBGE
```
IBGE_REST_INTEGRATION_HOST="http://servicodados.ibge.gov.br/api/v1"
IBGE_REST_INTEGRATION_PATH_STATES="/localidades/estados"
IBGE_REST_INTEGRATION_PATH_CITIES_PREFIX="/localidades/estados"
IBGE_REST_INTEGRATION_PATH_CITIES="/municipios"
```

#### Brasil API
```
IBGE_REST_INTEGRATION_HOST="https://brasilapi.com.br/api/ibge"
IBGE_REST_INTEGRATION_PATH_STATES="/uf/v1"
IBGE_REST_INTEGRATION_PATH_CITIES_PREFIX="/municipios/v1"
```

Pode ser utilizado outras fontes de dados, desde que o retorno está dentro do padrão esperado no sistema.

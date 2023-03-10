<?php

namespace App\Http\Controllers;

use App\Http\Requests\getCitiesRequest;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    /**
     * Exibe e consulta todos municipios do estado passado como referencia
     *
     * @return \Illuminate\Http\Response
     */
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
}

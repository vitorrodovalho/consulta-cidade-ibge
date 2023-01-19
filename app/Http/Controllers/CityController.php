<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Exibe e consulta todos municipios do estado passado como referencia
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state)
    {
        return City::select('cities.name', 'cities.ibge_id as ibge_code')
            ->where('acronym', $state)
            ->join('states', 'states.id', '=', 'state_id')
            ->paginate();
    }
}

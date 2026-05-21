<?php

namespace App\Http\Controllers;

use App\Models\Scenario;
// use Illuminate\Http\Request;

class ScenarioController extends Controller
{
    public function index()
    {
        $scenarios = Scenario::all();

        return response()->json($scenarios);
    }
}

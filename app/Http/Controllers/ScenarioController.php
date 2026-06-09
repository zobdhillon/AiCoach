<?php

namespace App\Http\Controllers;

use App\Models\Scenario;
use Inertia\Inertia;
// use Illuminate\Http\Request;

class ScenarioController extends Controller
{
    public function index()
    {
        $scenarios = Scenario::all();

        return Inertia::render('Scenarios/Index', [
            'scenarios' => $scenarios
        ]);
    }
}

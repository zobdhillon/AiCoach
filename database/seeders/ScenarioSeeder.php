<?php

namespace Database\Seeders;

use App\Models\Scenario;
use Illuminate\Database\Seeder;

class ScenarioSeeder extends Seeder
{
    public function run(): void
    {
        Scenario::create([
            'title' => 'Job Interview',
            'description' => 'Practice technical interviews',
            'system_prompt' => 'You are a professional technical interviewer.'
        ]);

        Scenario::create([
            'title' => 'Salary Negotiation',
            'description' => 'Negotiate salary confidently',
            'system_prompt' => 'You are an HR manager negotiating salary.'
        ]);

        Scenario::create([
            'title' => 'Angry Customer',
            'description' => 'Handle difficult customer situations',
            'system_prompt' => 'You are an angry customer complaining about service.'
        ]);
    }
}

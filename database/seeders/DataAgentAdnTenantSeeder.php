<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DataAgentAdnTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create([
            'user_id' => 1,
            'name' => 'TENANT_abc123'
        ]);

        $agent = Agent::create([
            'tenant_id' => 1,
            'name' => 'AGENT_9f2c7e',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Services\DrillDataService;
use Illuminate\Database\Seeder;

class DrillDashboardJsonSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        config(['database.use_database' => true]);

        /** @var DrillDataService $service */
        $service = app(DrillDataService::class);
        $service->seedFromJsonIfEmpty();
    }
}

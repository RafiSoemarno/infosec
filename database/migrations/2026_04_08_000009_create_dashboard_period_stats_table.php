<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardPeriodStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_period_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year')->nullable();
            $table->string('period_label', 40);
            $table->unsignedInteger('target')->default(0);
            $table->unsignedInteger('actual')->default(0);
            $table->unsignedInteger('percentage')->default(0);
            $table->timestamps();
            $table->unique(['fiscal_year', 'period_label']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard_period_stats');
    }
}

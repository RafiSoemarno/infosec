<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardStatDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_stat_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_period_stat_id')->constrained('dashboard_period_stats')->onDelete('cascade');
            $table->string('label', 40);
            $table->unsignedInteger('actual')->default(0);
            $table->unsignedInteger('target')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard_stat_details');
    }
}

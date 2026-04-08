<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDrillCompletionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_drill_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('drill_simulation_id')->constrained('drill_simulations')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->string('response_time')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'drill_simulation_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_drill_completions');
    }
}

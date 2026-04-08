<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'company')) {
                $table->string('company')->nullable()->after('employee_id');
            }

            if (!Schema::hasColumn('users', 'business_unit')) {
                $table->string('business_unit')->nullable()->after('company');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'business_unit')) {
                $table->dropColumn('business_unit');
            }

            if (Schema::hasColumn('users', 'company')) {
                $table->dropColumn('company');
            }

            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropColumn('employee_id');
            }

            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });
    }
}

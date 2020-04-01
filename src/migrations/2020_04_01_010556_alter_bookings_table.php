<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBookingsTable extends Migration
{
    /**
     * Run the migrations.
     * Test
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('search');
            $table->index(["ref_iban"]);
            $table->dropIndex(['search']);
        });
    }
}

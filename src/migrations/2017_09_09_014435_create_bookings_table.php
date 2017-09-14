<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     * Test
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_iban', 32);
            $table->string('search', 32);
            $table->date('bookingdate');
            $table->date('valutadate');
            $table->decimal('amount');
            $table->string('creditdebit');
            $table->string('bookingtext')->nullable();
            $table->text('description1')->nullable();
            $table->string('structureddescription', 1024)->nullable();
            $table->string('bankcode')->nullable();
            $table->string('accountnumber')->nullable();
            $table->text('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(["search"]); // isSimpleIndex => bookings_search_index
        });
    }
}
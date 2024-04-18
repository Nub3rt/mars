<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('type', ['washing_machinge', 'room']);
            // The default duration of a reservation (in minutes)
            $table->smallInteger('default_reservation_duration');
            $table->boolean('is_default_compulsory');
            $table->set('allowed_starting_minutes', range(0, 59));
            $table->dateTime('out_of_order_from' )->nullable();
            $table->dateTime('out_of_order_until')->nullable();
            $table->timestamps();
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservable_item_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('verified')->default(true);
            $table->string('title')->nullable();
            $table->string('note' )->nullable();
            $table->dateTime('reserved_from');
            $table->dateTime('reserved_until');
            $table->timestamps();

            $table->foreign('reservable_item_id')->references('id')->on('reservable_items');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(['reservable_item_id', 'reserved_from']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservable_items');
        Schema::dropIfExists('reservations');
    }
};

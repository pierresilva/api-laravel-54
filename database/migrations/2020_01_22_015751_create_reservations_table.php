<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('reservation_id')->nullable()->comment('Id de la reserva del booking engine');
            $table->unsignedBigInteger('pms_reservation_id')->nullable()->comment('Id de la reserva de PMS');
            $table->string('booking_engine')->nullable()->comment('Nombre del booking engine');
            $table->string('customer_name')->nullable()->comment('Nombre del cliente');
            $table->string('customer_phone')->nullable()->comment('Telefono del cliente');
            $table->string('customer_email')->nullable()->comment('Correo electrónico del cliente');
            $table->string('customer_country')->nullable()->comment('Código del pais del cliente');
            $table->date('checkin')->nullable()->comment('Fecha de llegada');
            $table->date('checkout')->nullable()->comment('Fecha de salida');
            $table->double('price', 10, 2)->nullable()->comment('Precio total de la reserva');
            $table->string('currency')->nullable()->comment('Código de la moneda');
            $table->longText('metadata')->nullable()->comment('Meta data completa de la reserva del booking engine');
            $table->string('status')->nullable()->comment('Estado de la reserva');
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
        Schema::dropIfExists('reservations');
    }
}

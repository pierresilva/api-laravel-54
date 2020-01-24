<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeFieldToDatabasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases', function (Blueprint $table) {
            //
            $table->string('code')->unique()->nulable()->comment('Código único para la conexión');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases', function (Blueprint $table) {
            //
            $table->dropColumn('code');
        });
    }
}

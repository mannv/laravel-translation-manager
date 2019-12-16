<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sle_commands', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('test_case_id')->index();
            $table->integer('suite_id')->index();
            $table->text('comment')->nullable();
            $table->string('command');
            $table->string('target');
            $table->string('value');
            $table->json('targets');
            $table->integer('weight');
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
        Schema::dropIfExists('sle_commands');
    }
}

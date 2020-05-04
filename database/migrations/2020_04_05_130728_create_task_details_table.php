<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_details', function (Blueprint $table) {
            $table->id();
            $table->string("country")->default("Denmark");
            $table->integer("postal");
            $table->string("city");
            $table->string("street");
            $table->string("street_number");
            $table->string("phone")->nullable();
            $table->string("action", 4)->default("pick");
            $table->timestamp("scheduled_at");
            $table->timestamp("completed_at")->nullable();
            $table->foreignId("task_id");
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_details');
    }
}

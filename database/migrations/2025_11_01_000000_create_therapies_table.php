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
        Schema::create('therapies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->tinyInteger('age_from')->nullable();
            $table->tinyInteger('age_to')->nullable();
            $table->unsignedBigInteger('assigned_patient_id')->nullable();
            $table->unsignedBigInteger('author_id');
            $table->boolean('published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('assigned_patient_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('therapies');
    }
};

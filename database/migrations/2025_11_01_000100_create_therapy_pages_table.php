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
        Schema::create('therapy_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('therapy_id')->constrained('therapies')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('type')->default('step'); // hero, step, info
            $table->smallInteger('number')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->json('list_items')->nullable();
            $table->string('note')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('therapy_pages');
    }
};

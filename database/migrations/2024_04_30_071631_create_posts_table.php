<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreign('category_id')->on('categories')->references('id');
            $table->string('post_name', 100)->nullable(false);
            $table->string('post_permalink', 100)->nullable(false);
            $table->string('post_description', 100)->nullable(true);
            $table->string('post_image')->nullable(true);
            $table->text('post_content')->nullable(true);
            $table->unsignedBigInteger('category_id')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

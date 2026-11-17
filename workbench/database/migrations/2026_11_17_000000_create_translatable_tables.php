<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The common record is created by the model (createCommonRecord())
        Schema::create('category_commons', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('position');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_common_id')->nullable()->constrained();
            $table->string('title');
            $table->timestamps();
            $table->translatable();
        });

        // The common record has a required column, known only by the form: it must be saved before the translation
        Schema::create('product_commons', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->unsignedInteger('weight')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_common_id')->nullable()->constrained();
            // The products of a category: a nested resource
            $table->foreignId('category_id')->nullable()->constrained();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->timestamps();
            $table->translatable();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->publishable();
            $table->timestamps();
            $table->translatable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_commons');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('category_commons');
    }
};

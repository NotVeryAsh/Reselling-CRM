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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->date('purchased_at');
            $table->decimal('purchased_price');
            $table->string('purchased_platform');
            $table->date('sold_at')->nullable();
            $table->decimal('sold_price')->nullable();
            $table->string('sold_platform')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

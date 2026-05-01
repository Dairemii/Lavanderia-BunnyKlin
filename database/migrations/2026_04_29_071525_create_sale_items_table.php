<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->morphs('item');

            $table->string('name_snapshot', 100);
            $table->decimal('price_snapshot', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 10, 2);
            # $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

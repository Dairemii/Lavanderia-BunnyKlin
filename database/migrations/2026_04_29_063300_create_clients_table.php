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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->nullOnDelete();
            $table->date('end_subscription')->nullable();
            $table->string('rfc', 14)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('uso_cfdi', 10)->nullable();
            $table->string('regimen_fiscal', 10)->nullable();
            $table->string('codigo_postal', 5)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

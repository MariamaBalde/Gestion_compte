<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id')->index();
            $table->enum('type_transaction', ['depot', 'retrait'])->index();
            $table->decimal('montant', 15, 2);
            $table->string('devise')->default('FCFA');
            $table->string('reference')->unique();
            $table->decimal('solde_apres', 15, 2)->nullable();
            $table->enum('statut_transaction', ['effectue', 'annule', 'en_attente'])->default('effectue')->index();
            $table->timestamps();

            $table->foreign('compte_id')->references('id')->on('compte')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

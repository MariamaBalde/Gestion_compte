<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compte', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            $table->string('numero_compte')->unique();

          

            // type et statut (on utilise string pour simplicité)
            $table->enum('type_compte', ['cheque', 'epargne', 'courant'])->index();
            $table->decimal('solde', 15, 2)->default(0);
                $table->string('devise')->default('FCFA');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif')->index();
          
            $table->timestamps();
              $table->foreign('client_id')
                  ->references('id')->on('client')
                  ->cascadeOnDelete();
        });

       Schema::table('compte', function (Blueprint $table) {
            $table->softDeletes(); // ajoute deleted_at
        });
    }

    public function down(): void
    {
         Schema::table('compte', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('compte');
      
    }
};

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

         Schema::create('client', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('titulaire'); 
            $table->string('nci')->unique()->index();       
            $table->string('email')->unique()->index();                  
            $table->string('telephone')->unique()->index();             
            $table->string('adresse');             
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};

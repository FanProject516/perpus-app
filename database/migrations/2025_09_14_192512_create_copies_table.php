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
      Schema::create('copies', function (Blueprint $table) {
         $table->id();
         $table->foreignId('book_id')->constrained()->cascadeOnDelete();
         $table->string('barcode')->unique();
         $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('good');
         $table->string('location')->nullable(); // specific location/shelf
         $table->boolean('is_available')->default(true);
         $table->text('notes')->nullable(); // maintenance notes
         $table->date('acquired_date')->nullable();
         $table->timestamps();

         $table->index(['book_id', 'is_available']);
         $table->index(['barcode']);
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('copies');
   }
};

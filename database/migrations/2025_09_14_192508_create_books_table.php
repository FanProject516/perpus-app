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
      Schema::create('books', function (Blueprint $table) {
         $table->id();
         $table->string('title');
         $table->string('author');
         $table->string('isbn')->nullable()->unique();
         $table->string('publisher')->nullable();
         $table->year('year')->nullable();
         $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
         $table->text('summary')->nullable();
         $table->string('cover_path')->nullable();
         $table->integer('total_copies')->default(1);
         $table->integer('available_copies')->default(1);
         $table->decimal('price', 10, 2)->nullable();
         $table->string('language')->default('id');
         $table->integer('pages')->nullable();
         $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('good');
         $table->string('location')->nullable(); // shelf location
         $table->boolean('is_available')->default(true);
         $table->timestamps();

         $table->index(['title', 'author']);
         $table->index(['isbn']);
         $table->index(['category_id', 'is_available']);
         $table->fullText(['title', 'author', 'summary']);
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('books');
   }
};

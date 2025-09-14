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
      Schema::create('loans', function (Blueprint $table) {
         $table->id();
         $table->foreignId('user_id')->constrained()->cascadeOnDelete();
         $table->foreignId('book_id')->constrained()->cascadeOnDelete();
         $table->foreignId('copy_id')->nullable()->constrained()->nullOnDelete();
         $table->enum('status', ['requested', 'approved', 'borrowed', 'returned', 'overdue', 'cancelled'])->default('requested');
         $table->date('requested_at');
         $table->date('approved_at')->nullable();
         $table->date('borrowed_at')->nullable();
         $table->date('due_at')->nullable();
         $table->date('returned_at')->nullable();
         $table->decimal('fine_amount', 8, 2)->default(0);
         $table->text('notes')->nullable();
         $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
         $table->timestamps();

         $table->index(['user_id', 'status']);
         $table->index(['book_id', 'status']);
         $table->index(['status', 'due_at']);
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('loans');
   }
};

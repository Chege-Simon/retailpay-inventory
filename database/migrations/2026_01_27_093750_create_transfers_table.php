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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('to_store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('initiated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('transfer_date');
            $table->timestamp('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};

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
            $table->string('uuid');
            $table->string('transfer_number')->unique();
            $table->foreignId('from_store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->foreignId('to_store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('status', ['requested','pending_admin_approval', 'approved', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users');
            $table->timestamp('requested_date')->nullable();
            $table->foreignId('forwarded_by')->nullable()->constrained('users');
            $table->timestamp('forwarded_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_date')->nullable();
            $table->foreignId('shipped_by')->nullable()->constrained('users');
            $table->timestamp('shipped_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_date')->nullable();
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

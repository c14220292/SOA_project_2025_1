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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('reservation_date');
            $table->integer('guest_count');
            $table->integer('table_count');
            $table->decimal('dp_amount', 10, 2)->default(0);
            $table->decimal('minimal_charge', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'cancelled', 'paid'])->default('pending');
            $table->timestamp('payment_time')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

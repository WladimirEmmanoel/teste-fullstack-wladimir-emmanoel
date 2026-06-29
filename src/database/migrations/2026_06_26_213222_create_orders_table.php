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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('external_id')->unique();

            $table->foreignId('affiliate_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_value', 10, 2)->default(0);

            $table->unsignedTinyInteger('status')->default('1')
                ->comment('1 - pending; 2 - approved; 3 - cancelled; 4 - refunded');

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'affiliate_id',
                'status',
                'created_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

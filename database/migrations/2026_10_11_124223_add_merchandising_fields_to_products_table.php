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
        Schema::table('products', function (Blueprint $table) {
            $table->text('usage_instructions')->nullable()->after('description');
            $table->text('ingredients')->nullable()->after('usage_instructions');
            $table->text('precautions')->nullable()->after('ingredients');
            $table->boolean('is_pack')->default(false)->after('is_featured')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['usage_instructions', 'ingredients', 'precautions', 'is_pack']);
        });
    }
};

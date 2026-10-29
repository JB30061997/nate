<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_reference')->nullable()->unique()->after('order_number');
        });

        $migrationKey = Str::lower((string) Str::uuid());

        DB::table('orders')
            ->select(['id', 'order_number'])
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($migrationKey): void {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update([
                            'external_reference' => $order->order_number,
                            'order_number' => "__nate_migration_{$migrationKey}_{$order->id}",
                        ]);
                }
            });

        DB::table('orders')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['order_number' => $this->shortReference((int) $order->id)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereNull('external_reference')
            ->update(['external_reference' => DB::raw('order_number')]);

        $migrationKey = Str::lower((string) Str::uuid());

        DB::table('orders')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($migrationKey): void {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['order_number' => "__nate_rollback_{$migrationKey}_{$order->id}"]);
                }
            });

        DB::table('orders')
            ->select(['id', 'external_reference'])
            ->orderBy('id')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['order_number' => $order->external_reference]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['external_reference']);
            $table->dropColumn('external_reference');
        });
    }

    private function shortReference(int $orderId): string
    {
        return 'NATE-'.str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
    }
};

<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderReferenceMigrationTest extends TestCase
{
    public function test_it_preserves_existing_references_and_assigns_short_references(): void
    {
        $originalDefaultConnection = config('database.default');

        config([
            'database.default' => 'order_reference_migration_test',
            'database.connections.order_reference_migration_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('order_reference_migration_test');

        try {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->string('order_number')->unique();
            });

            DB::table('orders')->insert([
                ['order_number' => 'NATE-DCDBBFFB4CFD4984B6C9BF06955CFD60'],
                ['order_number' => 'NATE-ANCIENNE-REFERENCE'],
            ]);

            $migration = require database_path('migrations/2026_10_10_111909_add_external_reference_to_orders_table.php');
            $migration->up();

            $this->assertDatabaseHas('orders', [
                'id' => 1,
                'order_number' => 'NATE-000001',
                'external_reference' => 'NATE-DCDBBFFB4CFD4984B6C9BF06955CFD60',
            ], 'order_reference_migration_test');
            $this->assertDatabaseHas('orders', [
                'id' => 2,
                'order_number' => 'NATE-000002',
                'external_reference' => 'NATE-ANCIENNE-REFERENCE',
            ], 'order_reference_migration_test');
        } finally {
            DB::purge('order_reference_migration_test');
            config(['database.default' => $originalDefaultConnection]);
        }
    }
}

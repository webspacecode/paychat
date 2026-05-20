<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            if (!Schema::hasColumn('resources', 'area')) {
                $table->string('area')->nullable();
            }

            if (!Schema::hasColumn('resources', 'floor')) {
                $table->string('floor')->nullable();
            }

            if (!Schema::hasColumn('resources', 'pos_x')) {
                $table->integer('pos_x')->nullable();
            }

            if (!Schema::hasColumn('resources', 'pos_y')) {
                $table->integer('pos_y')->nullable();
            }

            if (!Schema::hasColumn('resources', 'width')) {
                $table->integer('width')->nullable();
            }

            if (!Schema::hasColumn('resources', 'height')) {
                $table->integer('height')->nullable();
            }

            if (!Schema::hasColumn('resources', 'shape')) {
                $table->string('shape', 30)->nullable();
            }

            if (!Schema::hasColumn('resources', 'rotation')) {
                $table->integer('rotation')->default(0);
            }

            if (!Schema::hasColumn('resources', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->index(['location_id', 'type', 'area'], 'resources_location_type_area_index');
            $table->index(['location_id', 'type', 'floor'], 'resources_location_type_floor_index');
            $table->index('sort_order', 'resources_sort_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropIndex('resources_location_type_area_index');
            $table->dropIndex('resources_location_type_floor_index');
            $table->dropIndex('resources_sort_order_index');
        });

        Schema::table('resources', function (Blueprint $table) {
            foreach ([
                'sort_order',
                'rotation',
                'shape',
                'height',
                'width',
                'pos_y',
                'pos_x',
                'floor',
                'area',
            ] as $column) {
                if (Schema::hasColumn('resources', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

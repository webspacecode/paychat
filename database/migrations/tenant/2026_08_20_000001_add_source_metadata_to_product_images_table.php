<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'source')) {
                $table->string('source')->nullable()->after('image_path');
            }
            if (! Schema::hasColumn('product_images', 'provider')) {
                $table->string('provider')->nullable()->after('source');
            }
            if (! Schema::hasColumn('product_images', 'provider_image_id')) {
                $table->string('provider_image_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('product_images', 'provider_url')) {
                $table->text('provider_url')->nullable()->after('provider_image_id');
            }
            if (! Schema::hasColumn('product_images', 'author_name')) {
                $table->string('author_name')->nullable()->after('provider_url');
            }
            if (! Schema::hasColumn('product_images', 'author_url')) {
                $table->text('author_url')->nullable()->after('author_name');
            }
            if (! Schema::hasColumn('product_images', 'license')) {
                $table->string('license')->nullable()->after('author_url');
            }
            if (! Schema::hasColumn('product_images', 'meta')) {
                $table->json('meta')->nullable()->after('license');
            }
            if (! Schema::hasColumn('product_images', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('meta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            foreach ([
                'source',
                'provider',
                'provider_image_id',
                'provider_url',
                'author_name',
                'author_url',
                'license',
                'meta',
                'is_primary',
            ] as $column) {
                if (Schema::hasColumn('product_images', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            // Add if columns don't exist already
            if (! Schema::hasColumn('templates', 'badge')) {
                $table->string('badge')->nullable()->after('thumbnail');
            }
            if (! Schema::hasColumn('templates', 'category')) {
                $table->string('category')->nullable()->after('badge');
            }
            if (! Schema::hasColumn('templates', 'tags')) {
                $table->json('tags')->nullable()->after('category');
            }
            if (! Schema::hasColumn('templates', 'cover_gradient')) {
                $table->string('cover_gradient', 500)->nullable()->after('tags');
            }
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumnIfExists('badge');
            $table->dropColumnIfExists('category');
            $table->dropColumnIfExists('tags');
            $table->dropColumnIfExists('cover_gradient');
        });
    }
};
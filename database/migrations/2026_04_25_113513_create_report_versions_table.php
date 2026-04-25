<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label')->nullable(); // e.g. "Auto-save", "Manual save"
            $table->json('content');
            $table->json('settings');
            $table->string('title');
            $table->unsignedInteger('version_number')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        // Add share_token to reports table
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'share_token')) {
                $table->string('share_token', 64)->nullable()->unique()->after('slug');
            }
            if (! Schema::hasColumn('reports', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('share_token');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_versions');
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumnIfExists('share_token');
            $table->dropColumnIfExists('is_public');
        });
    }
};
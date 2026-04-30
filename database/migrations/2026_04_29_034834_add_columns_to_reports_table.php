// database/migrations/2024_01_01_000001_add_columns_to_reports_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
                        
                         
            if (!Schema::hasColumn('reports', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('reports', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable();
            }
            if (!Schema::hasColumn('reports', 'is_shared')) {
                $table->boolean('is_shared')->default(false);
            }
            if (!Schema::hasColumn('reports', 'share_notes')) {
                $table->text('share_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assigned_by', 'assigned_at', 'is_shared', 'share_notes',]);
        });
    }
};
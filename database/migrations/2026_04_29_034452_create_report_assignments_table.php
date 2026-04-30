// database/migrations/2024_01_01_000003_create_report_assignments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users');
            $table->enum('permission', ['view', 'edit', 'manage'])->default('view');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['report_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['report_id', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_assignments');
    }
};
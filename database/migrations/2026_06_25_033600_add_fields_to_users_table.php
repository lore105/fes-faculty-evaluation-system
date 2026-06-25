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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->string('employee_id')->nullable()->unique()->after('middle_name');
            $table->string('student_id')->nullable()->unique()->after('employee_id');
            $table->string('phone')->nullable()->after('student_id');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone');
            $table->date('birthdate')->nullable()->after('gender');
            $table->boolean('is_active')->default(true)->after('birthdate');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'middle_name',
                'employee_id',
                'student_id',
                'phone',
                'gender',
                'birthdate',
                'is_active',
            ]);
            $table->dropSoftDeletes();
        });
    }
};

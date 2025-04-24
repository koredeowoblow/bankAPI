<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('loan_data')) {
            Schema::create('loan_data', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users');
                $table->decimal('principal', 10, 2);
                $table->decimal('fixed_interest_rate', 5, 2);
                $table->string('duration');
                $table->string('duration_type');
                $table->string('next_of_kin');
                $table->string('next_of_kin_phone');
                $table->decimal('interest', 10, 2);
                $table->string('status')->default('pending');
                $table->decimal('total_amount', 10, 2);
                $table->date('approved_date')-> nullable();
                $table->date('due_date')->nullable();
                $table->timestamps();
            });
        }

    }

    public function down()
    {
        Schema::dropIfExists('loan_data');
    }
};

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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // equivalent to `id int(11) NOT NULL`
            $table->string('fullname', 300); // `fullname varchar(300) NOT NULL`
            $table->string('email', 250)->unique(); // `email varchar(250) NOT NULL`
            $table->string('phone_number', 12); // `phone_number varchar(12) NOT NULL`
            $table->string('password', 100); // `password varchar(100) NOT NULL`
            $table->string('account_balance', 250)->default('0'); // `account_balance varchar(250) NOT NULL DEFAULT '0'`
            $table->string('pin', 250); // `pin varchar(250) NOT NULL`
            $table->string('status', 250)->default('active'); // `status varchar(250) NOT NULL DEFAULT 'active'`
            $table->decimal('loan_amount', 10, 2)->default(0.00); // `loan_amount decimal(10,2) NOT NULL DEFAULT 0.00`

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

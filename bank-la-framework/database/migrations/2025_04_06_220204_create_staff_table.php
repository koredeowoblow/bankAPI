<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('staffs')) {
            Schema::create('staffs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('access_role_id')->default(1)->constrained()->onDelete('cascade');
                $table->string('password');
                $table->string('email');
                $table->timestamps();
            });
        }

    }

    public function down()
    {
        Schema::dropIfExists('staffs');
    }
};

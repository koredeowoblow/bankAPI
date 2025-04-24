<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('access_role')) {
            Schema::create('access_role', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('access')) {
            Schema::create('access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained('access_role')->onDelete('cascade');
                $table->text('crud');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('access');
        Schema::dropIfExists('access_role');
    }
};

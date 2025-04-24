<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('access_modules')) {
            Schema::create('access_modules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('icon');
                $table->string('link');
                $table->string('style');
                $table->integer('parent_id')->default(0);
                $table->timestamps();
            });
        }


    }

    public function down()
    {
        Schema::dropIfExists('access_modules');
    }
};

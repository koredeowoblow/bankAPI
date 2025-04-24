<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('reference_number');
                $table->string('transaction_type');
                $table->decimal('amount', 15, 2);
                $table->text('sender_bank_detail')->nullable();
                $table->text('recipient_bank_details')->nullable();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('funding_details')->nullable();
                $table->string('transaction_nature'); // 'credit' or 'debit'
                $table->timestamps();
            });

        }
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}

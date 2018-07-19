<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->uuid('uuid')->nullable();
            $table->integer('user_id')->unsigned();
            $table->integer('expense_category_id')->unsigned()->nullable();
            $table->integer('income_category_id')->unsigned()->nullable();
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->integer('account_id')->unsigned()->nullable();
            $table->integer('from_account_id')->unsigned()->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('supplier_id')->unsigned()->nullable();
            $table->integer('currency_id')->unsigned()->nullable();
            $table->decimal('conversion_rate',25,5)->default(0);
            $table->string('source',20)->nullable();
            $table->decimal('amount',25,5)->default(0);
            $table->string('head',50)->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(0);
            $table->integer('payment_method_id')->unsigned()->nullable();
            $table->string('reference_number',100)->nullable();
            $table->string('token',64)->nullable();
            $table->string('upload_token')->nullable();
            $table->string('coupon',25)->nullable();
            $table->decimal('coupon_discount',25,5)->default(0);
            $table->string('phone',20)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city',50)->nullable();
            $table->string('state',50)->nullable();
            $table->string('zipcode',10)->nullable();
            $table->string('country',50)->nullable();
            $table->text('gateway_response')->nullable();
            $table->string('gateway_token')->nullable();
            $table->string('gateway_status',20)->nullable();
            $table->decimal('processing_fee',25,5)->default(0);
            $table->boolean('is_withdrawl')->default(0);
            $table->text('withdawl_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}

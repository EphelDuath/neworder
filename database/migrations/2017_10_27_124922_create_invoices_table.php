<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->uuid('uuid')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('currency_id')->unsigned();
            $table->boolean('is_draft')->default(0);
            $table->string('status',20)->nullable();
            $table->boolean('is_cancelled')->default(0);
            $table->string('prefix',20)->nullable();
            $table->integer('number')->nullable();
            $table->date('date')->nullable();
            $table->string('reference_number',50)->nullable();
            $table->string('due_date',50)->nullable();
            $table->date('due_date_detail')->nullable();
            $table->boolean('line_item_tax')->default(0);
            $table->boolean('line_item_discount')->default(0);
            $table->boolean('line_item_discount_type')->default(0);
            $table->boolean('line_item_description')->default(0);
            $table->boolean('subtotal_tax')->default(0);
            $table->boolean('subtotal_discount')->default(0);
            $table->boolean('subtotal_shipping_and_handling')->default(0);
            $table->string('item_type',50)->nullable();
            $table->decimal('subtotal_tax_amount',25,5)->default(0);
            $table->decimal('subtotal_discount_amount',25,5)->default(0);
            $table->boolean('subtotal_discount_type')->default(0);
            $table->decimal('subtotal_shipping_and_handling_amount',25,5)->default(0);
            $table->decimal('subtotal',25,5)->default(0);
            $table->decimal('total',25,5)->default(0);
            $table->text('customer_note')->nullable();
            $table->text('tnc')->nullable();
            $table->text('memo')->nullable();
            $table->boolean('is_recurring')->default(0);
            $table->date('recurrence_start_date')->nullable();
            $table->integer('recurring_frequency')->default(0);
            $table->date('next_recurring_date')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->boolean('partial_payment')->default(0);
            $table->integer('recurring_invoice_id')->unsigned()->nullable();
            $table->integer('quotation_id')->unsigned()->nullable();
            $table->string('upload_token')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->uuid('uuid');
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->decimal('quantity',25,5)->default(0);
            $table->decimal('unit_price',25,5)->default(0);
            $table->decimal('discount',25,5)->default(0);
            $table->integer('discount_type')->default(0);
            $table->decimal('tax',25,5)->default(0);
            $table->decimal('amount',25,5)->default(0);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('invoice_items');
    }
}

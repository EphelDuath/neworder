<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code',20)->nullable();
            $table->decimal('discount',25,5)->default(0);
            $table->date('valid_start_date')->nullable();
            $table->date('valid_end_date')->nullable();
            $table->string('valid_days')->nullable();
            $table->boolean('new_user')->default(0);
            $table->integer('max_use_count')->default(0);
            $table->integer('use_count')->default(0);
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
        Schema::dropIfExists('coupons');
    }
}

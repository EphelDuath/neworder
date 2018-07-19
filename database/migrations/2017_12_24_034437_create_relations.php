<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('backups', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('todos', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('activity_logs', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('login_as_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('profiles', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });

        Schema::table('messages', function(Blueprint $table)
        {
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_id')->references('id')->on('messages')->onDelete('cascade');
        });

        Schema::table('user_preferences', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('uploads', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('designations', function(Blueprint $table)
        {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('top_designation_id')->references('id')->on('designations')->onDelete('cascade');
        });

        Schema::table('suppliers', function(Blueprint $table)
        {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('announcements', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('announcement_designation', function(Blueprint $table)
        {
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
        });

        Schema::table('coupons', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('currency_conversions', function(Blueprint $table)
        {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });

        Schema::table('customer_group_user', function(Blueprint $table)
        {
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('items', function(Blueprint $table)
        {
            $table->foreign('item_category_id')->references('id')->on('item_categories')->onDelete('cascade');
            $table->foreign('taxation_id')->references('id')->on('taxations')->onDelete('set null');
        });

        Schema::table('item_prices', function(Blueprint $table)
        {
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });

        Schema::table('invoices', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('recurring_invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('set null');
        });

        Schema::table('invoice_items', function(Blueprint $table)
        {
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });

        Schema::table('quotations', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });

        Schema::table('quotation_items', function(Blueprint $table)
        {
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });

        Schema::table('quotation_discussions', function(Blueprint $table)
        {
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_id')->references('id')->on('quotation_discussions')->onDelete('cascade');
        });

        Schema::table('transactions', function(Blueprint $table)
        {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('cascade');
            $table->foreign('income_category_id')->references('id')->on('income_categories')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('from_account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('backups', function(Blueprint $table)
        {
            $table->dropForeign('backups_user_id_foreign');
        });

        Schema::table('todos', function(Blueprint $table)
        {
            $table->dropForeign('todos_user_id_foreign');
        });

        Schema::table('activity_logs', function(Blueprint $table)
        {
            $table->dropForeign('activity_logs_user_id_foreign');
            $table->dropForeign('activity_logs_login_as_user_id_foreign');
        });

        Schema::table('profiles', function(Blueprint $table)
        {
            $table->dropForeign('profiles_user_id_foreign');
            $table->dropForeign('profiles_designation_id_foreign');
            $table->dropForeign('profiles_company_id_foreign');
        });

        Schema::table('messages', function(Blueprint $table)
        {
            $table->dropForeign('messages_from_user_id_foreign');
            $table->dropForeign('messages_to_user_id_foreign');
            $table->dropForeign('messages_reply_id_foreign');
        });

        Schema::table('user_preferences', function(Blueprint $table)
        {
            $table->dropForeign('user_preferences_user_id_foreign');
        });

        Schema::table('uploads', function(Blueprint $table)
        {
            $table->dropForeign('uploads_user_id_foreign');
        });

        Schema::table('designations', function(Blueprint $table)
        {
            $table->dropForeign('designations_department_id_id_foreign');
            $table->dropForeign('designations_top_designation_id_id_foreign');
        });

        Schema::table('suppliers', function(Blueprint $table)
        {
            $table->dropForeign('suppliers_company_id_foreign');
        });

        Schema::table('announcements', function(Blueprint $table)
        {
            $table->dropForeign('announcements_user_id_foreign');
        });

        Schema::table('announcement_designation', function(Blueprint $table)
        {
            $table->dropForeign('announcement_designation_announcement_id_foreign');
            $table->dropForeign('announcement_designation_designation_id_foreign');
        });

        Schema::table('coupons', function(Blueprint $table)
        {
            $table->dropForeign('coupons_user_id_foreign');
        });

        Schema::table('currency_conversions', function(Blueprint $table)
        {
            $table->dropForeign('currency_conversions_currency_id_foreign');
        });

        Schema::table('customer_group_user', function(Blueprint $table)
        {
            $table->dropForeign('customer_group_user_customer_group_id_foreign');
            $table->dropForeign('customer_group_user_user_id_foreign');
        });

        Schema::table('items', function(Blueprint $table)
        {
            $table->dropForeign('items_item_category_id_foreign');
            $table->dropForeign('items_taxation_id_foreign');
        });

        Schema::table('item_prices', function(Blueprint $table)
        {
            $table->dropForeign('item_prices_item_id_foreign');
            $table->dropForeign('item_prices_currency_id_foreign');
        });

        Schema::table('invoices', function(Blueprint $table)
        {
            $table->dropForeign('invoices_user_id_foreign');
            $table->dropForeign('invoices_customer_id_foreign');
            $table->dropForeign('invoices_currency_id_foreign');
            $table->dropForeign('invoices_recurring_invoice_id_foreign');
            $table->dropForeign('invoices_quotation_id_foreign');
        });

        Schema::table('invoice_items', function(Blueprint $table)
        {
            $table->dropForeign('invoice_items_invoice_id_foreign');
            $table->dropForeign('invoice_items_item_id_foreign');
        });

        Schema::table('quotations', function(Blueprint $table)
        {
            $table->dropForeign('quotations_user_id_foreign');
            $table->dropForeign('quotations_customer_id_foreign');
            $table->dropForeign('quotations_currency_id_foreign');
            $table->dropForeign('quotations_invoice_id_foreign');
        });

        Schema::table('quotation_items', function(Blueprint $table)
        {
            $table->dropForeign('quotation_items_quotation_id_foreign');
            $table->dropForeign('quotation_items_item_id_foreign');
        });

        Schema::table('quotation_discussions', function(Blueprint $table)
        {
            $table->dropForeign('quotation_discussions_quotation_id_foreign');
            $table->dropForeign('quotation_discussions_user_id_foreign');
            $table->dropForeign('quotation_discussions_reply_id_foreign');
        });

        Schema::table('transactions', function(Blueprint $table)
        {
            $table->dropForeign('transactions_user_id_foreign');
            $table->dropForeign('transactions_expense_category_id_foreign');
            $table->dropForeign('transactions_income_category_id_foreign');
            $table->dropForeign('transactions_invoice_id_foreign');
            $table->dropForeign('transactions_account_id_foreign');
            $table->dropForeign('transactions_from_account_id_foreign');
            $table->dropForeign('transactions_customer_id_foreign');
            $table->dropForeign('transactions_supplier_id_foreig');
            $table->dropForeign('transactions_currency_id_foreign');
            $table->dropForeign('transactions_payment_method_id_foreign');
        });
    }
}

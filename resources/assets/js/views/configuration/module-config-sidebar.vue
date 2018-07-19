<template>
    <div class="col-2 col-lg-2 col-md-2">
        <div class="list-group">
            <router-link to="/configuration/customer-group" :class="[menu === 'customer-group' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-users"></i></span> <span class="hidden-xs-down">{{trans('user.customer')}}</span> </router-link>
            <router-link to="/configuration/currency" :class="[menu === 'currency' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-money-bill-alt"></i></span> <span class="hidden-xs-down">{{trans('currency.currency')}}</span> </router-link>
            <router-link to="/configuration/taxation" :class="[menu === 'taxation' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="far fa-plus-square"></i></span> <span class="hidden-xs-down">{{trans('taxation.taxation')}}</span> </router-link>
            <router-link to="/configuration/expense-category" :class="[menu === 'expense-category' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-industry"></i></span> <span class="hidden-xs-down">{{trans('transaction.expense')}}</span> </router-link>
            <router-link to="/configuration/income-category" :class="[menu === 'income-category' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-industry"></i></span> <span class="hidden-xs-down">{{trans('transaction.income')}}</span> </router-link>
            <router-link to="/configuration/item-category" :class="[menu === 'item' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-th-large"></i></span> <span class="hidden-xs-down">{{trans('item.item')}}</span> </router-link>
            <router-link to="/configuration/invoice" :class="[menu === 'invoice' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-list-alt"></i></span> <span class="hidden-xs-down">{{trans('invoice.invoice')}}</span> </router-link>
            <router-link to="/configuration/quotation" :class="[menu === 'quotation' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="fas fa-file-alt"></i></span> <span class="hidden-xs-down">{{trans('quotation.quotation')}}</span> </router-link>
            <router-link to="/configuration/payment-method" :class="[menu === 'payment' ? 'active' : '', 'list-group-item']"><span class="hidden-sm-up"><i class="far fa-handshake"></i></span> <span class="hidden-xs-down">{{trans('payment.payment')}}</span> </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            menu: {
                default: ''
            }
        },
        mounted(){
        }
    }
</script>

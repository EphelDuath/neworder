<template>
    <aside class="left-sidebar">
        <div class="scroll-sidebar">
            <div class="user-profile">
                <div class="profile-img"> <img :src="getAuthUser('avatar')" alt="user" /> </div>
            </div>
            <nav class="sidebar-nav m-t-20">
                <div class="text-center" v-if="getConfig('maintenance_mode')"><span class="badge badge-danger m-b-10">{{trans('configuration.under_maintenance')}}</span></div>
                <div class="text-center" v-if="!getConfig('mode')"><span class="badge badge-danger m-b-10">{{trans('configuration.test_mode')}}</span></div>
                <ul id="sidebarnav">
                    <li><router-link to="/home" exact><i class="fas fa-home fa-fw"></i> <span class="hide-menu">{{trans('general.home')}}</span></router-link></li>
                    <li v-if="hasPermission('list-company') && getConfig('show_company_menu')"><router-link to="/company" exact><i class="fas fa-building fa-fw"></i> <span class="hide-menu">{{trans('company.company')}}</span></router-link></li>
                    <li v-if="hasPermission('list-account') && getConfig('show_account_menu')"><router-link to="/account" exact><i class="fas fa-briefcase fa-fw"></i> <span class="hide-menu">{{trans('account.account')}}</span></router-link></li>
                    <li v-if="hasPermission('list-customer') && getConfig('show_customer_menu')"><router-link to="/user/customer" exact><i class="fas fa-users fa-fw"></i> <span class="hide-menu">{{trans('user.customer')}}</span></router-link></li>
                    <li v-if="hasPermission('list-supplier') && getConfig('show_supplier_menu')"><router-link to="/supplier" exact><i class="fas fa-address-book fa-fw"></i> <span class="hide-menu">{{trans('supplier.supplier')}}</span></router-link></li>
                    <li v-if="(hasPermission('list-account-transfer') || hasPermission('list-expense') || hasPermission('list-income')) && getConfig('show_transaction_menu')">
                        <a class="has-arrow" href="#" aria-expanded="false"><i class="fas fa-industry fa-fw"></i> <span class="hide-menu">{{trans('transaction.transaction')}}</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li v-if="hasPermission('list-income') && getConfig('show_income_menu')"><router-link to="/transaction/income"><i class="fas fa-angle-double-right"></i> {{trans('transaction.income')}}</router-link></li>
                            <li v-if="hasPermission('list-expense') && getConfig('show_expense_menu')"><router-link to="/transaction/expense"><i class="fas fa-angle-double-right"></i> {{trans('transaction.expense')}}</router-link></li>
                            <li v-if="hasPermission('list-account-transfer') && getConfig('show_account_transfer_menu')"><router-link to="/transaction/account-transfer"><i class="fas fa-angle-double-right"></i> {{trans('transaction.account_transfer')}}</router-link></li>
                        </ul>
                    </li>
                    <li v-if="hasPermission('list-invoice') && getConfig('show_invoice_menu')"><router-link to="/invoice" exact><i class="fas fa-list-alt fa-fw"></i> <span class="hide-menu">{{trans('invoice.invoice')}}</span></router-link></li>
                    <li v-if="hasPermission('list-quotation') && getConfig('show_quotation_menu')"><router-link to="/quotation" exact><i class="fas fa-file-alt fa-fw"></i> <span class="hide-menu">{{trans('quotation.quotation')}}</span></router-link></li>
                    <li v-if="hasPermission('list-item') && getConfig('show_item_menu')"><router-link to="/item" exact><i class="fas fa-th-large fa-fw"></i> <span class="hide-menu">{{trans('item.item')}}</span></router-link></li>
                    <li v-if="hasPermission('list-coupon') && getConfig('coupon') && getConfig('show_coupon_menu')"><router-link to="/coupon" exact><i class="fas fa-gift fa-fw"></i> <span class="hide-menu">{{trans('coupon.coupon')}}</span></router-link></li>
                    <li v-if="hasPermission('list-announcement') && getConfig('announcement') && getConfig('show_announcement_menu')"><router-link to="/announcement" exact><i class="fas fa-bullhorn fa-fw"></i> <span class="hide-menu">{{trans('announcement.announcement')}}</span></router-link></li>
                    <li v-if="hasPermission('list-staff') && getConfig('show_staff_menu')"><router-link to="/user/staff" exact><i class="fas fa-user-plus fa-fw"></i> <span class="hide-menu">{{trans('user.staff')}}</span></router-link></li>
                </ul>
            </nav>
        </div>
        <div class="sidebar-footer">
            <router-link v-if="hasPermission('access-configuration')" to="/configuration" class="link" v-tooltip="trans('configuration.configuration')"><i class="fas fa-cogs"></i></router-link>
            <router-link to="/profile" class="link" v-tooltip="trans('user.profile')"><i class="fas fa-user"></i></router-link>
            <a href="#" class="link" v-tooltip="trans('auth.logout')" @click.prevent="logout"><i class="fas fa-power-off"></i></a>
        </div>
    </aside>
</template>

<script>
    export default {
        mounted() {
        },
        methods : {
            logout(){
                helper.logout().then(() => {
                    this.$store.dispatch('resetAuthUserDetail');
                    this.$router.push('/login');
                })
            },
            getAuthUser(name){
                return helper.getAuthUser(name);
            },
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getConfig(config){
                return helper.getConfig(config);
            }
        },
        computed: {
        }
    }
</script>

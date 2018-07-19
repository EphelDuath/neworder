<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('account.account')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('account.account')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <transition name="fade">
                    <div class="card" v-if="showFilterPanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showFilterPanel" @click="showFilterPanel = !showFilterPanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('general.filter')}}</h4>
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('account.name')}}</label>
                                        <input class="form-control" name="name" v-model="filterAccountForm.name">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('account.type')}}</label>
                                        <div class="radio radio-success">
                                            <input type="radio" value="bank" id="type_bank" v-model="filterAccountForm.type" name="type">
                                            <label for="type_bank" class="m-r-10">{{trans('account.bank')}}</label>
                                            <input type="radio" value="cash" id="type_cash" v-model="filterAccountForm.type" name="type">
                                            <label for="type_cash">{{trans('account.cash')}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="order" class="form-control" v-model="filterAccountForm.sort_by">
                                            <option value="name">{{trans('account.name')}}</option>
                                            <option value="type">{{trans('account.type')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterAccountForm.order">
                                            <option value="asc">{{trans('general.ascending')}}</option>
                                            <option value="desc">{{trans('general.descending')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
                <transition name="fade" v-if="hasPermission('create-account')">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('account.add_new_account')}}</h4>
                            <account-form @completed="getAccounts"></account-form>
                        </div>
                    </div>
                </transition>

                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="accounts.total && !showCreatePanel && hasPermission('create-account')" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                        <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                        <h4 class="card-title">{{trans('account.account_list')}}</h4>
                        <h6 class="card-subtitle" v-if="accounts">{{trans('general.total_result_found',{'count' : accounts.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="accounts.total && hasPermission('list-account')">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('account.name')}}</th>
                                        <th>{{trans('account.type')}}</th>
                                        <th>{{trans('account.opening_balance')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account in accounts.data">
                                        <td v-text="account.name"></td>
                                        <td v-text="ucword(account.type)"></td>
                                        <td v-text="formatCurrency(account.opening_balance,default_currency)"></td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-account')" v-tooltip="trans('account.edit_account')" @click.prevent="editAccount(account)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-account')"  :key="account.id" v-confirm="{ok: confirmDelete(account)}" v-tooltip="trans('account.delete_account')"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!accounts.total" module="account" title="module_info_title" description="module_info_description" icon="briefcase">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-account') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterAccountForm.page_length" :records="accounts" @updateRecords="getAccounts"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import accountForm from './form'

    export default {
        components : { accountForm },
        data() {
            return {
                accounts: {
                    total: 0,
                    data: []
                },
                filterAccountForm: {
                    name: '',
                    type: '',
                    sort_by : 'name',
                    order: 'asc',
                    page_length: helper.getConfig('page_length')
                },
                default_currency: {},
                showCreatePanel: false,
                showFilterPanel: false
            };
        },
        mounted(){
            if(!helper.hasPermission('list-account') && !helper.hasPermission('create-account')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getAccounts();
            axios.get('/api/currency/fetch/default')
                .then(response => response.data)
                .then(response => {
                    this.default_currency = response;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getAccounts(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterAccountForm);

                axios.get('/api/account?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.accounts = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editAccount(account){
                this.$router.push('/account/'+account.id+'/edit');
            },
            confirmDelete(account){
                return dialog => this.deleteAccount(account);
            },
            deleteAccount(account){
                axios.delete('/api/account/'+account.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getAccounts();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            formatCurrency(number,currency){
                return helper.formatCurrency(number,currency);
            },
            ucword(str){
                return helper.ucword(str);
            }
        },
        watch: {
            filterAccountForm: {
                handler(val){
                    this.getAccounts();
                },
                deep: true
            }
        }
    }
</script>

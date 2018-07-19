<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('transaction.'+type)}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('transaction.'+type)}}</li>
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
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('transaction.reference_number')}}</label>
                                        <input class="form-control" name="reference_number" v-model="filterTransactionForm.reference_number">
                                    </div>
                                </div>
                                <div class="col-3 col-md-3" v-show="type === 'income'">
                                    <div class="form-group">
                                        <label for="">{{trans('transaction.select_transaction_category',{type: trans('transaction.income')})}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_income_category" name="income_category_id" id="income_category_id" :options="income_categories" :placeholder="trans('transaction.select_transaction_category',{type: trans('transaction.income')})" @select="onIncomeCategorySelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onIncomeCategoryRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3" v-show="type === 'expense'">
                                    <div class="form-group">
                                        <label for="">{{trans('transaction.select_transaction_category',{type: trans('transaction.expense')})}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_expense_category" name="expense_category_id" id="expense_category_id" :options="expense_categories" :placeholder="trans('transaction.select_transaction_category',{type: trans('transaction.expense')})" @select="onExpenseCategorySelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onExpenseCategoryRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('transaction.account')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_account" name="account_id" id="account_id" :options="accounts" :placeholder="trans('account.select_account')" @select="onAccountSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onAccountRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3" v-show="type === 'account-transfer'">
                                    <div class="form-group">
                                        <label for="">{{trans('transaction.from_account')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_from_account" name="from_account_id" id="from_account_id" :options="accounts" :placeholder="trans('account.select_account')" @select="onFromAccountSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onFromAccountRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('payment.select_payment_method')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_payment_method" name="payment_method_id" id="payment_method_id" :options="payment_methods" :placeholder="trans('payment.select_payment_method')" @select="onPaymentMethodSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onPaymentMethodRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3" v-show="type === 'income'">
                                    <div class="form-group">
                                        <label for="">{{trans('transaction.payer')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_customer" name="customer_id" id="customer_id" :options="customers" :placeholder="trans('general.select_one')" @select="onCustomerSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onCustomerRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3" v-show="type === 'expense'">
                                    <div class="form-group">
                                        <label for="">{{trans('supplier.supplier')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_supplier" name="supplier_id" id="supplier_id" :options="suppliers" :placeholder="trans('general.select_one')" @select="onSupplierSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onSupplierRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('currency.select_currency')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_currency" name="currency_id" id="currency_id" :options="currencies" :placeholder="trans('currency.select_currency')" @select="onCurrencySelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onCurrencyRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <date-range-picker :start-date.sync="filterTransactionForm.date_start_date" :end-date.sync="filterTransactionForm.date_end_date" :label="trans('transaction.date')"></date-range-picker>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="sort_by" class="form-control" v-model="filterTransactionForm.sort_by">
                                            <option value="date">{{trans('transaction.date')}}</option>
                                            <option value="amount">{{trans('transaction.amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterTransactionForm.order">
                                            <option value="asc">{{trans('general.ascending')}}</option>
                                            <option value="desc">{{trans('general.descending')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
                <transition name="fade" v-if="hasPermission('create-'+type)">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('transaction.add_new_transaction',{type: trans('transaction.'+type)})}}</h4>
                            <transaction-form @completed="getTransactions" :type="type"></transaction-form>
                        </div>
                    </div>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="transactions.total && hasPermission('create-'+type) && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('transaction.add_new_record',{type:trans('transaction.'+type)})}}</button>
                        <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                        <h4 class="card-title">{{trans('transaction.transaction_list',{type: trans('transaction.'+type)})}}</h4>
                        <h6 class="card-subtitle" v-if="transactions">{{trans('general.total_result_found',{'count' : transactions.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="hasPermission('list-'+type) && transactions.total">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('transaction.reference_number')}}</th>
                                        <th>{{trans('transaction.account')}}</th>
                                        <th v-if="type === 'income'">{{trans('transaction.payer')}}</th>
                                        <th v-if="type === 'expense'">{{trans('supplier.supplier')}}</th>
                                        <th v-if="type === 'account-transfer'">{{trans('transaction.from_account')}}</th>
                                        <th>{{trans('transaction.amount')}}</th>
                                        <th>{{trans('payment.payment_method')}}</th>
                                        <th>{{trans('transaction.date')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="transaction in transactions.data">
                                        <td v-text="transaction.reference_number"></td>
                                        <td v-text="transaction.account.name"></td>
                                        <td v-if="type === 'income' && transaction.customer" v-text="transaction.customer.profile.first_name+' '+transaction.customer.profile.last_name"></td>
                                        <td v-if="type === 'expense' && transaction.supplier" v-text="transaction.supplier.name"></td>
                                        <td v-if="type === 'account-transfer' && transaction.from_account" v-text="transaction.from_account.name"></td>
                                        <td>{{formatCurrency(transaction.amount,transaction.currency)}}</td>
                                        <td v-text="transaction.payment_method.name"></td>
                                        <td>{{transaction.date | moment}}</td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-income') && !(transaction.head === 'income' && transaction.invoice_id)" v-tooltip="trans('transaction.edit_transaction',{type: trans('transaction.'+type)})" @click.prevent="editTransaction(transaction)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-income') && !(transaction.head === 'income' && transaction.invoice_id)" :key="transaction.id" v-confirm="{ok: confirmDelete(transaction)}" v-tooltip="trans('transaction.delete_transaction',{type: trans('transaction.'+type)})"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!transactions.total" module="transaction" :title="'module_info_'+formatType+'_title'" :description="'module_info_'+formatType+'_description'" icon="industry">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-'+type) && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterTransactionForm.page_length" :records="transactions" @updateRecords="getTransactions"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import transactionForm from './form'
    import vSelect from 'vue-multiselect'
    import dateRangePicker from '../../components/date-range-picker'

    export default {
        components : { transactionForm,vSelect,dateRangePicker },
        data() {
            return {
                type: this.$route.params.type,
                showCreatePanel: false,
                transactions: {
                    total: 0,
                    data: []
                },
                filterTransactionForm: {
                    income_category_id: [],
                    expense_category_id: [],
                    account_id: [],
                    from_account_id: [],
                    payment_method_id: [],
                    currency_id: [],
                    customer_id: [],
                    date_start_date: '',
                    date_end_date: '',
                    sort_by: 'date',
                    order: 'desc',
                    page_length: helper.getConfig('page_length')
                },
                showFilterPanel: false,
                selected_currency: '',
                selected_income_category: '',
                selected_expense_category: '',
                selected_account: '',
                selected_from_account: '',
                selected_payment_method: '',
                selected_customer: '',
                currencies: [],
                income_categories: [],
                expense_categories: [],
                accounts: [],
                payment_methods: [],
                customers: [],
                suppliers: []
            };
        },
        mounted(){
            if(!helper.hasPermission('list-'+this.type) && !helper.hasPermission('create-'+type)){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            this.validateType();
            this.getPreRequisites();

            this.getTransactions();
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getPreRequisites(){
                this.selected_expense_category = null;
                this.selected_from_account = null;
                this.selected_income_category = null;
                this.selected_customer = null;
                this.selected_supplier = null;
                this.selected_currency = null;
                this.selected_payment_method = null;
                this.selected_from_account = null;

                axios.get('/api/transaction/'+this.type+'/pre-requisite')
                    .then(response => response.data)
                    .then(response => {
                        this.customers = (this.type === 'income') ? response.customers : [];
                        this.suppliers = (this.type === 'expense') ? response.suppliers : [];
                        this.currencies = response.currencies;
                        this.income_categories = (this.type === 'income') ? response.transaction_categories : [];
                        this.expense_categories = (this.type === 'expense') ? response.transaction_categories : [];
                        this.accounts = response.accounts;
                        this.payment_methods = response.payment_methods;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getTransactions(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterTransactionForm);
                axios.get('/api/transaction/'+this.type+'?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.transactions = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editTransaction(transaction){
                this.$router.push('/transaction/'+this.type+'/'+transaction.uuid+'/edit');
            },
            confirmDelete(transaction){
                return dialog => this.deleteTransaction(transaction);
            },
            deleteTransaction(transaction){
                axios.delete('/api/transaction/'+this.type+'/'+transaction.uuid)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getTransactions();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            formatNumber(number){
                return helper.formatNumber(number);
            },
            formatCurrency(price,currency){
                return helper.formatCurrency(price,currency);
            },
            validateType(){
                if(this.type !== 'income' && this.type !== 'expense' && this.type !== 'account-transfer')
                    this.$router.push('/transaction/income');
            },
            onCurrencySelect(selectedOption){
                this.filterTransactionForm.currency_id.push(selectedOption.id);
            },
            onCurrencyRemove(removedOption){
                this.filterTransactionForm.currency_id.splice(this.filterTransactionForm.currency_id.indexOf(removedOption.id), 1);
            },
            onIncomeCategorySelect(selectedOption){
                this.filterTransactionForm.income_category_id.push(selectedOption.id);
            },
            onIncomeCategoryRemove(removedOption){
                this.filterTransactionForm.income_category_id.splice(this.filterTransactionForm.income_category_id.indexOf(removedOption.id), 1);
            },
            onExpenseCategorySelect(selectedOption){
                this.filterTransactionForm.expense_category_id.push(selectedOption.id);
            },
            onExpenseCategoryRemove(removedOption){
                this.filterTransactionForm.expense_category_id.splice(this.filterTransactionForm.expense_category_id.indexOf(removedOption.id), 1);
            },
            onPaymentMethodSelect(selectedOption){
                this.filterTransactionForm.payment_method_id.push(selectedOption.id);
            },
            onPaymentMethodRemove(removedOption){
                this.filterTransactionForm.payment_method_id.splice(this.filterTransactionForm.payment_method_id.indexOf(removedOption.id), 1);
            },
            onAccountSelect(selectedOption){
                this.filterTransactionForm.account_id.push(selectedOption.id);
            },
            onAccountRemove(removedOption){
                this.filterTransactionForm.account_id.splice(this.filterTransactionForm.account_id.indexOf(removedOption.id), 1);
            },
            onFromAccountSelect(selectedOption){
                this.filterTransactionForm.from_account_id.push(selectedOption.id);
            },
            onFromAccountRemove(removedOption){
                this.filterTransactionForm.from_account_id.splice(this.filterTransactionForm.from_account_id.indexOf(removedOption.id), 1);
            },
            onCustomerSelect(selectedOption){
                this.filterTransactionForm.customer_id.push(selectedOption.id);
            },
            onCustomerRemove(removedOption){
                this.filterTransactionForm.customer_id.splice(this.filterTransactionForm.customer_id.indexOf(removedOption.id), 1);
            },
            onSupplierSelect(selectedOption){
                this.filterTransactionForm.supplier_id.push(selectedOption.id);
            },
            onSupplierRemove(removedOption){
                this.filterTransactionForm.supplier_id.splice(this.filterTransactionForm.supplier_id.indexOf(removedOption.id), 1);
            }
        },
        computed: {
            formatType(){
                return this.type.replace(/-/g, '_');
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          }
        },
        watch: {
            '$route.params.type'(newType, oldType) {
                this.type = newType;
                this.getPreRequisites();
                this.getTransactions();
            },
            filterTransactionForm: {
                handler(val){
                    this.getTransactions();
                },
                deep: true
            }
        }
    }
</script>

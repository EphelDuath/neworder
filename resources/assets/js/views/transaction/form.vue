<template>
    <form @submit.prevent="proceed" @keydown="transactionForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('transaction.account')}}</label>
                    <v-select label="name" v-model="selected_account" name="account_id" id="account_id" :options="accounts" :placeholder="trans('account.select_account')" @select="onAccountSelect" @close="transactionForm.errors.clear('account_id')" @remove="transactionForm.account_id = ''"></v-select>
                    <show-error :form-name="transactionForm" prop-name="account_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3" v-if="type === 'account-transfer'">
                <div class="form-group">
                    <label for="">{{trans('transaction.from_account')}}</label>
                    <v-select label="name" v-model="selected_from_account" name="from_account_id" id="from_account_id" :options="accounts" :placeholder="trans('account.select_account')" @select="onFromAccountSelect" @close="transactionForm.errors.clear('from_account_id')" @remove="transactionForm.from_account_id = ''"></v-select>
                    <show-error :form-name="transactionForm" prop-name="from_account_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3" v-if="type !== 'account-transfer'">
                <div class="form-group">
                    <label for="">{{trans('transaction.transaction_category',{type: trans('transaction.'+type)})}}</label>
                    <v-select label="name" v-model="selected_transaction_category" name="transaction_category_id" id="transaction_category_id" :options="transaction_categories" :placeholder="trans('transaction.select_transaction_category',{type: trans('transaction.'+type)})" @select="onTransactionCategorySelect" @close="transactionForm.errors.clear('transaction_category_id')" @remove="transactionForm.transaction_category_id = ''"></v-select>
                    <show-error :form-name="transactionForm" prop-name="transaction_category_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('payment.payment_method')}}</label>
                    <v-select label="name" v-model="selected_payment_method" name="payment_method_id" id="payment_method_id" :options="payment_methods" :placeholder="trans('payment.select_payment_method')" @select="onPaymentMethodSelect" @close="transactionForm.errors.clear('payment_method_id')" @remove="transactionForm.payment_method_id = ''"></v-select>
                    <show-error :form-name="transactionForm" prop-name="payment_method_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3" v-if="type === 'income'">
                <div class="form-group">
                    <label for="">{{trans('user.customer')}}</label>
                    <v-select label="name" v-model="selected_customer" name="customer_id" id="customer_id" :options="customers" :placeholder="trans('transaction.select_payer')" @select="onCustomerSelect" @close="transactionForm.errors.clear('customer_id')" @remove="transactionForm.customer_id = ''"></v-select>
                    <show-error :form-name="transactionForm" prop-name="customer_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3" v-if="type === 'expense'">
                <div class="form-group">
                    <label for="">{{trans('supplier.supplier')}}</label>
                    <v-select label="name" v-model="selected_supplier" name="supplier_id" id="supplier_id" :options="suppliers" :placeholder="trans('transaction.select_payer')" @select="onSupplierSelect" @close="transactionForm.errors.clear('supplier_id')" @remove="transactionForm.supplier_id = ''"></v-select>
                    <show-error :form-name="transactionForm" prop-name="supplier_id"></show-error>
                </div>
            </div>
            <template v-if="selected_currency">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">{{trans('currency.currency')}}</label>
                        <v-select label="name" v-model="selected_currency" name="currency_id" id="currency_id" :options="currencies" :placeholder="trans('currency.select_currency')" @select="onCurrencySelect" @close="transactionForm.errors.clear('currency_id')" @remove="transactionForm.currency_id = ''"></v-select>
                        <show-error :form-name="transactionForm" prop-name="currency_id"></show-error>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">{{trans('transaction.amount')}}</label>
                        <currency-input :position="selected_currency.position" :symbol="selected_currency.symbol" name="amount" :placeholder="trans('transaction.amount')" v-model="transactionForm.amount"></currency-input>
                        <show-error :form-name="transactionForm" prop-name="amount"></show-error>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group" v-if="default_currency.id != selected_currency.id">
                        <label for="">{{trans('transaction.conversion_rate')}}</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">1 {{selected_currency.symbol}} = </span></div>
                            <input class="form-control" type="text" name="conversion_rate" :placeholder="trans('transaction.conversion_rate')" v-model="transactionForm.conversion_rate">
                            <div class="input-group-append"><span class="input-group-text">{{default_currency.symbol}}</span></div>
                        </div>
                    </div>
                </div>
            </template>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('transaction.reference_number')}}</label>
                    <input class="form-control" type="text" value="" v-model="transactionForm.reference_number" name="reference_number" :placeholder="trans('transaction.reference_number')">
                    <show-error :form-name="transactionForm" prop-name="reference_number"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('transaction.date')}}</label>
                    <datepicker v-model="transactionForm.date" :bootstrapStyling="true" @selected="transactionForm.errors.clear('date')" :placeholder="trans('transaction.date')"></datepicker>
                    <show-error :form-name="transactionForm" prop-name="date"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('transaction.description')}}</label>
                    <textarea class="form-control" type="text" value="" v-model="transactionForm.description" rows="2" name="description" :placeholder="trans('transaction.description')"></textarea>
                    <show-error :form-name="transactionForm" prop-name="description"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <file-upload-input :button-text="trans('general.upload_document')" :token="transactionForm.upload_token" module="transaction" :clear-file="clear_transaction_attachment" :module-id="transactionForm.id"></file-upload-input>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="uuid">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link :to="`/transaction/${type}`" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="transactionForm.id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import uuid from 'uuid/v4'
    import datepicker from 'vuejs-datepicker'
    import vSelect from 'vue-multiselect'
    import fileUploadInput from '../../components/file-upload-input'
    import currencyInput from '../../components/currency-input'

    export default {
        components: {datepicker,vSelect,fileUploadInput,currencyInput},
        props: ['type','uuid'],
        data() {
            return {
                transactionForm: new Form({
                    account_id : null,
                    from_account_id: null,
                    transaction_category_id : null,
                    currency_id: null,
                    amount: '',
                    payment_method_id: null,
                    customer_id: null,
                    supplier_id: null,
                    reference_number: '',
                    date: '',
                    upload_token: '',
                    description: '',
                    type: '',
                    conversion_rate: 1,
                    id: ''
                }),
                accounts: [],
                selected_account: null,
                selected_from_account: null,
                transaction_categories: [],
                selected_transaction_category: null,
                currencies: [],
                selected_currency: null,
                payment_methods: [],
                selected_payment_method: null,
                customers: [],
                suppliers: [],
                selected_customer: null,
                selected_supplier: null,
                default_currency: {},
                clear_transaction_attachment: true
            };
        },
        mounted() {
            this.getPreRequisites();
        },
        methods: {
            getPreRequisites(){
                this.transactionForm.reference_number = this.newReferenceNumber();
                if(this.uuid)
                    this.getTransaction();
                if(!this.uuid)
                    this.transactionForm.upload_token = uuid();

                axios.get('/api/transaction/'+this.type+'/pre-requisite')
                    .then(response => response.data)
                    .then(response => {
                        this.accounts = response.accounts;
                        this.transaction_categories = (this.type != 'account-transfer') ? response.transaction_categories : [];
                        this.currencies = response.currencies;
                        this.payment_methods = response.payment_methods;
                        this.customers = (this.type === 'income') ? response.customers : [];
                        this.suppliers = (this.type === 'expense') ? response.suppliers : [];
                        this.default_currency = response.default_currency;
                        if(!this.uuid)
                            this.selected_currency = this.default_currency;
                        this.transactionForm.currency_id = this.default_currency.id;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            newReferenceNumber(){
                return helper.randomString(10).toUpperCase();
            },
            proceed(){
                if(this.uuid)
                    this.updateTransaction();
                else
                    this.storeTransaction();
            },
            storeTransaction(){
                this.transactionForm.date = moment(this.transactionForm.date).format('YYYY-MM-DD');
                this.transactionForm.type = this.type;
                this.transactionForm.post('/api/transaction')
                    .then(response => {
                        toastr.success(response.message);
                        this.clear_transaction_attachment = !this.clear_transaction_attachment;
                        this.selected_customer = null;
                        this.selected_supplier = null;
                        this.selected_currency = this.default_currency;
                        this.selected_transaction_category = null;
                        this.selected_payment_method = null;
                        this.selected_account = null;
                        this.selected_from_account = null;
                        this.transactionForm.conversion_rate = 1;
                        this.transactionForm.reference_number = this.newReferenceNumber();
                        this.transactionForm.upload_token = uuid();
                        this.transactionForm.currency_id = this.selected_currency.id;
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getTransaction(){
                axios.get('/api/transaction/'+this.type+'/'+this.uuid)
                    .then(response => response.data)
                    .then(response => {
                        this.transactionForm.date = response.transaction.date;
                        this.transactionForm.reference_number = response.transaction.reference_number;
                        this.transactionForm.description = response.transaction.description;
                        this.transactionForm.account_id = response.transaction.account_id;
                        this.transactionForm.from_account_id = (this.type === 'account-transfer') ? response.transaction.from_account_id : null;
                        if(this.type === 'income' || this.type === 'expense'){
                            this.transactionForm.transaction_category_id = (this.type === 'income') ? response.transaction.income_category_id : ((this.type === 'expense') ? response.transaction.expense_category_id : null);
                        }
                        this.transactionForm.customer_id = (this.type === 'income') ? response.transaction.customer_id : null;
                        this.transactionForm.supplier_id = (this.type === 'expense') ? response.transaction.supplier_id : null;
                        this.selected_customer = response.selected_customer;
                        this.selected_supplier = response.selected_supplier;
                        this.transactionForm.currency_id = response.transaction.currency_id;
                        this.transactionForm.payment_method_id = response.transaction.payment_method_id;
                        this.transactionForm.upload_token = response.transaction.upload_token;
                        this.selected_account = response.selected_account;
                        this.selected_from_account = (this.type === 'account-transfer') ? response.selected_from_account : {};
                        this.selected_transaction_category = response.selected_transaction_category;
                        this.selected_currency = response.selected_currency;
                        this.selected_payment_method = response.selected_payment_method;
                        this.transactionForm.amount = helper.formatNumber(response.transaction.amount,this.selected_currency.decimal_place);
                        this.transactionForm.conversion_rate = helper.formatNumber(response.transaction.conversion_rate);
                        this.transactionForm.id = response.transaction.id;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/transaction/'+this.type);
                    });
            },
            updateTransaction(){
                this.transactionForm.date = moment(this.transactionForm.date).format('YYYY-MM-DD');
                this.transactionForm.type = this.type;
                this.transactionForm.patch('/api/transaction/'+this.uuid)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/transaction/'+this.type);
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            onAccountSelect(selectedOption){
                this.transactionForm.account_id = selectedOption.id;
            },
            onFromAccountSelect(selectedOption){
                this.transactionForm.from_account_id = selectedOption.id;
            },
            onTransactionCategorySelect(selectedOption){
                this.transactionForm.transaction_category_id = selectedOption.id;
            },
            onPaymentMethodSelect(selectedOption){
                this.transactionForm.payment_method_id = selectedOption.id;
            },
            onSupplierSelect(selectedOption){
                this.transactionForm.supplier_id = selectedOption.id;
            },
            onCustomerSelect(selectedOption){
                this.transactionForm.customer_id = selectedOption.id;
            },
            onCurrencySelect(selectedOption){
                this.transactionForm.currency_id = selectedOption.id;
            }
        },
        watch: {
            '$route.params.type'(newType, oldType) {
                this.type = newType;
                this.getPreRequisites();
            },
            default_currency(val){
                if(!val){
                    toastr.error(i18n.currency.no_default_currency_found);
                    this.$router.push('/home');
                }
            }
        }
    }
</script>

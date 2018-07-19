<template>
    <form @submit.prevent="submit" @keydown="paymentForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('transaction.account')}}</label>
                    <v-select label="name" v-model="selected_account" name="account_id" id="account_id" :options="accounts" :placeholder="trans('account.select_account')" @select="onAccountSelect" @close="paymentForm.errors.clear('account_id')" @remove="paymentForm.account_id = ''"></v-select>
                    <show-error :form-name="paymentForm" prop-name="account_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('transaction.transaction_category',{type: trans('transaction.income')})}}</label>
                    <v-select label="name" v-model="selected_income_category" name="income_category_id" id="income_category_id" :options="income_categories" :placeholder="trans('transaction.select_transaction_category',{type: trans('transaction.income')})" @select="onIncomeCategorySelect" @close="paymentForm.errors.clear('income_category_id')" @remove="paymentForm.income_category_id = ''"></v-select>
                    <show-error :form-name="paymentForm" prop-name="income_category_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('payment.payment_method')}}</label>
                    <v-select label="name" v-model="selected_payment_method" name="payment_method_id" id="payment_method_id" :options="payment_methods" :placeholder="trans('payment.select_payment_method')" @select="onPaymentMethodSelect" @close="paymentForm.errors.clear('payment_method_id')" @remove="paymentForm.payment_method_id = ''"></v-select>
                    <show-error :form-name="paymentForm" prop-name="payment_method_id"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('transaction.date')}}</label>
                    <datepicker v-model="paymentForm.date" :bootstrapStyling="true" @selected="paymentForm.errors.clear('date')" :placeholder="trans('transaction.date')"></datepicker>
                    <show-error :form-name="paymentForm" prop-name="date"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('transaction.amount')}}</label>
                    <currency-input :position="selected_currency.position" :symbol="selected_currency.symbol" name="amount" :placeholder="trans('transaction.amount')" v-model="paymentForm.amount"></currency-input>
                    <show-error :form-name="paymentForm" prop-name="amount"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group" v-if="default_currency.id != selected_currency.id">
                    <label for="">{{trans('transaction.conversion_rate')}}</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">1 {{default_currency.symbol}} = </span></div>
                        <input class="form-control" type="text" name="conversion_rate" :placeholder="trans('transaction.conversion_rate')" v-model="paymentForm.conversion_rate">
                        <div class="input-group-append"><span class="input-group-text">{{selected_currency.symbol}}</span></div>
                    </div>
                    <show-error :form-name="paymentForm" prop-name="conversion_rate"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-12">
                <div class="form-group">
                    <label for="">{{trans('transaction.description')}}</label>
                    <autosize-textarea row="3" class="form-control" v-model="paymentForm.description" :placeholder="trans('transaction.description')" name="description"></autosize-textarea>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <file-upload-input :button-text="trans('general.upload_document')" :token="paymentForm.upload_token" module="transaction" :clear-file="clear_payment_attachment" :module-id="paymentForm.id"></file-upload-input>
                </div>
            </div>
            <div class="col-12 col-md-6" v-if="getConfig('email_template')">
                <div class="form-group">
                    <switches class="m-l-20" v-model="paymentForm.email" theme="bootstrap" color="success"></switches> {{trans('invoice.send_payment_confirmation')}}
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="paymentUuid">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
    </form>
</template>
<script>
    import autosizeTextarea from '../../components/autosize-textarea'
    import vSelect from 'vue-multiselect'
    import datepicker from 'vuejs-datepicker'
    import currencyInput from '../../components/currency-input'
    import switches from 'vue-switches'
    import uuid from 'uuid/v4'
    import fileUploadInput from '../../components/file-upload-input'

    export default {
        components: {autosizeTextarea,vSelect,datepicker,currencyInput,switches,fileUploadInput},
        props: ['uuid','paymentUuid','payable','paid'],
        data() {
            return {
                paymentForm: new Form({
                    account_id: '',
                    income_category_id: '',
                    payment_method_id: '',
                    amount: '',
                    conversion_rate: 1,
                    date: '',
                    description: '',
                    upload_token: '',
                    id: '',
                    invoice_uuid: '',
                    email: false
                }),
                income_categories: [],
                accounts: [],
                payment_methods: [],
                selected_account: null,
                selected_income_category: null,
                selected_payment_method: null,
                selected_currency: {},
                clear_payment_attachment: false,
                default_currency: {}
            }
        },
        watch: {

        },
        mounted(){
            if(!this.paymentUuid)
                this.paymentForm.upload_token = uuid();

            this.paymentForm.invoice_uuid = this.uuid;
            axios.post('/api/invoice/'+this.uuid+'/payment/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.income_categories = response.income_categories;
                    this.accounts = response.accounts;
                    this.payment_methods = response.payment_methods;
                    this.selected_currency = response.selected_currency;
                    this.default_currency = response.default_currency;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            onAccountSelect(selectedOption){
                this.paymentForm.account_id = selectedOption.id;
            },  
            onIncomeCategorySelect(selectedOption){
                this.paymentForm.income_category_id = selectedOption.id;
            },
            onPaymentMethodSelect(selectedOption){
                this.paymentForm.payment_method_id = selectedOption.id;
            },
            submit(){
                this.paymentForm.date = moment(this.paymentForm.date).format('YYYY-MM-DD');
                this.paymentForm.post('/api/invoice/'+this.uuid+'/payment')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('updateStatus');
                        this.selected_account = null;
                        this.selected_payment_method = null;
                        this.selected_income_category = null;
                        this.paymentForm.email = false;
                        this.clear_payment_attachment = true;
                        this.paymentForm.conversion_rate = 1;
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getConfig(config){
                return helper.getConfig(config);
            }
        },
        computed: {

        },
        filters: {

        }
    }
</script>

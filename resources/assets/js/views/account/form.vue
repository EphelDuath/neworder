<template>
    <form @submit.prevent="proceed" @keydown="accountForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('account.name')}}</label>
                            <input class="form-control" type="text" value="" v-model="accountForm.name" name="name" :placeholder="trans('account.name')">
                            <show-error :form-name="accountForm" prop-name="name"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('account.opening_balance')}}</label>
                            <currency-input :position="default_currency.position" :symbol="default_currency.symbol" name="opening_balance" :placeholder="trans('account.opening_balance')" v-model="accountForm.opening_balance"></currency-input>
                            <show-error :form-name="accountForm" prop-name="opening_balance"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <div class="radio radio-success">
                                <input type="radio" value="bank" id="type_bank" v-model="accountForm.type" :checked="accountForm.type === 'bank'" name="type" @click="accountForm.errors.clear('type')">
                                <label for="type_bank">{{trans('account.bank')}}</label>
                            </div>
                            <div class="radio radio-success">
                                <input type="radio" value="cash" id="type_cash" v-model="accountForm.type" :checked="accountForm.type === 'cash'" name="type" @click="accountForm.errors.clear('type')">
                                <label for="type_cash">{{trans('account.cash')}}</label>
                            </div>
                            <show-error :form-name="accountForm" prop-name="type"></show-error>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6" v-if="accountForm.type === 'bank'">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('account.number')}}</label>
                            <input class="form-control" type="text" value="" v-model="accountForm.number" name="number" :placeholder="trans('account.number')">
                            <show-error :form-name="accountForm" prop-name="number"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('account.bank_name')}}</label>
                            <input class="form-control" type="text" value="" v-model="accountForm.bank_name" name="bank_name" :placeholder="trans('account.bank_name')">
                            <show-error :form-name="accountForm" prop-name="bank_name"></show-error>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('account.bank_branch')}}</label>
                            <input class="form-control" type="text" value="" v-model="accountForm.bank_branch" name="bank_branch" :placeholder="trans('account.bank_branch')">
                            <show-error :form-name="accountForm" prop-name="bank_branch"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('account.branch_code')}}</label>
                            <input class="form-control" type="text" value="" v-model="accountForm.branch_code" name="branch_code" :placeholder="trans('account.branch_code')">
                            <show-error :form-name="accountForm" prop-name="branch_code"></show-error>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/account" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import currencyInput from '../../components/currency-input'

    export default {
        components: {currencyInput},
        data() {
            return {
                accountForm: new Form({
                    name : '',
                    opening_balance : '',
                    type: '',
                    number: '',
                    bank_name: '',
                    branch_code: '',
                    bank_branch: ''
                }),
                default_currency: {}
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getAccount();
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
            proceed(){
                if(this.id)
                    this.updateAccount();
                else
                    this.storeAccount();
            },
            storeAccount(){
                this.accountForm.post('/api/account')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed')
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getAccount(){
                axios.get('/api/account/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.accountForm.name = response.name;
                        this.accountForm.opening_balance = this.formatNumber(response.opening_balance,this.default_currency.decimal_place);
                        this.accountForm.type = response.type;
                        this.accountForm.number = response.number;
                        this.accountForm.bank_name = response.bank_name;
                        this.accountForm.bank_branch = response.bank_branch;
                        this.accountForm.branch_code = response.branch_code;
                    })
                    .catch(error => {
                        this.$router.push('/account');
                    });
            },
            updateAccount(){
                this.accountForm.patch('/api/account/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/account');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            formatNumber(number,decimal_place){
                if(decimal_place === undefined)
                    decimal_place = 2;
                return helper.formatNumber(number,decimal_place);
            }
        },
        watch: {
            defaultCurrency(val){
                if(!val){
                    toastr.error(i18n.currency.no_default_currency_found);
                    this.$router.push('/home');
                }
            }
        }
    }
</script>

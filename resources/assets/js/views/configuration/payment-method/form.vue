<template>
    <form @submit.prevent="proceed" @keydown="paymentMethodForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('payment.payment_method_name')}}</label>
            <input class="form-control" type="text" value="" v-model="paymentMethodForm.name" name="name" :placeholder="trans('payment.payment_method_name')">
            <show-error :form-name="paymentMethodForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <div class="radio radio-success">
                <input type="radio" value="income" id="type_income" v-model="paymentMethodForm.type" :checked="paymentMethodForm.type === 'income'" name="type" @click="paymentMethodForm.errors.clear('type')">
                <label for="type_income">{{trans('transaction.income')}}</label>
            </div>
            <div class="radio radio-success">
                <input type="radio" value="expense" id="type_expense" v-model="paymentMethodForm.type" :checked="paymentMethodForm.type === 'expense'" name="type" @click="paymentMethodForm.errors.clear('type')">
                <label for="type_expense">{{trans('transaction.expense')}}</label>
            </div>
            <div class="radio radio-success">
                <input type="radio" value="account_transfer" id="type_account_transfer" v-model="paymentMethodForm.type" :checked="paymentMethodForm.type === 'account_transfer'" name="type" @click="paymentMethodForm.errors.clear('type')">
                <label for="type_account_transfer">{{trans('transaction.account_transfer')}}</label>
            </div>
            <show-error :form-name="paymentMethodForm" prop-name="type"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('payment.payment_method_description')}}</label>
            <textarea class="form-control" type="text" value="" v-model="paymentMethodForm.description" rows="2" name="description" :placeholder="trans('payment.payment_method_description')"></textarea>
            <show-error :form-name="paymentMethodForm" prop-name="description"></show-error>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/payment-method" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    export default {
        components: {},
        data() {
            return {
                paymentMethodForm: new Form({
                    name : '',
                    description : '',
                    type: ''
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getPaymentMethod();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updatePaymentMethod();
                else
                    this.storePaymentMethod();
            },
            storePaymentMethod(){
                this.paymentMethodForm.post('/api/payment-method')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getPaymentMethod(){
                axios.get('/api/payment-method/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.paymentMethodForm.name = response.name;
                        this.paymentMethodForm.type = response.type;
                        this.paymentMethodForm.description = response.description;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/configuration/payment-method');
                    });
            },
            updatePaymentMethod(){
                this.paymentMethodForm.patch('/api/payment-method/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/payment-method');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

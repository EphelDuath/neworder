<template>
    <div>
        <form class="form-material" @submit.prevent="proceed" @keydown="paymentForm.errors.clear($event.target.name)">
            <div class="form-group">
                <div class="radio radio-success" v-if="getConfig('paypal')">
                    <input type="radio" value="paypal" id="method_paypal" v-model="paymentForm.method" :checked="paymentForm.method === 'paypal'" name="method" @click="paymentForm.errors.clear('method')">
                    <label for="method_paypal">Paypal</label>
                </div>
                <div class="radio radio-success" v-if="getConfig('stripe')">
                    <input type="radio" value="credit_card" id="method_credit_card" v-model="paymentForm.method" :checked="paymentForm.method === 'credit_card'" name="method" @click="paymentForm.errors.clear('method')">
                    <label for="method_credit_card">Credit Card</label>
                </div>
                <show-error :form-name="paymentForm" prop-name="method"></show-error>
            </div>
            <div v-if="paymentForm.method">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.amount" name="amount" :placeholder="trans('payment.amount')" @blur="checkCoupon">
                            <span class="help text-strong" v-if="discount" style="color:green;">{{trans('payment.payable_amount')+' '+formatCurrency((paymentForm.amount - discount),currency)}}</span>
                            <show-error :form-name="paymentForm" prop-name="amount"></show-error>
                        </div>
                    </div>
                    <div class="col-6" v-if="getConfig('coupon')">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.coupon" name="coupon" :placeholder="trans('coupon.coupon')" @blur="checkCoupon" @input="coupon_error_message = ''">
                            <span class="help has-error" v-if="coupon_error_message" v-text="coupon_error_message"></span>
                            <span class="help text-strong" style="color:green;" v-else v-text="coupon_success_message"></span>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="paymentForm.method === 'credit_card'">
                    <div class="col-12">
                        <div class="form-group">
                            <input class="form-control" type="number" maxlength="16" value="" v-model="card_number" :placeholder="trans('payment.card_number')">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <input class="form-control" type="number" value="" v-model="month" :placeholder="trans('payment.month')">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <input class="form-control" type="number" value="" v-model="year" :placeholder="trans('payment.year')">
                        </div>
                    </div>
                    <div class="col-1">
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <input class="form-control" type="number" value="" v-model="cvc" :placeholder="trans('payment.cvc')">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.address_line_1" name="address_line_1" :placeholder="trans('payment.address_line_1')">
                            <show-error :form-name="paymentForm" prop-name="address_line_1"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.address_line_2" name="address_line_2" :placeholder="trans('payment.address_line_2')">
                            <show-error :form-name="paymentForm" prop-name="address_line_2"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.city" name="city" :placeholder="trans('payment.city')">
                            <show-error :form-name="paymentForm" prop-name="city"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.state" name="state" :placeholder="trans('payment.state')">
                            <show-error :form-name="paymentForm" prop-name="state"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.zipcode" name="zipcode" :placeholder="trans('payment.zipcode')">
                            <show-error :form-name="paymentForm" prop-name="zipcode"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.country" name="country" :placeholder="trans('payment.country')">
                            <show-error :form-name="paymentForm" prop-name="country"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" value="" v-model="paymentForm.phone" name="phone" :placeholder="trans('payment.phone')">
                            <show-error :form-name="paymentForm" prop-name="phone"></show-error>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-info waves-effect waves-light pull-right" v-if="button"><span>{{trans('general.proceed')}}</span></button>
            </div>
        </form>
    </div>
</template>
<script>
    import autosizeTextarea from '../../components/autosize-textarea'
    import currencyInput from '../../components/currency-input'

    export default {
        components: {autosizeTextarea,currencyInput},
        props: ['uuid','payable','paid','currency'],
        data(){
            return {
                paymentForm: new Form({
                    method: '',
                    address_line_1: '',
                    addres_line_2: '',
                    city: '',
                    state: '',
                    zipcode: '',
                    country: '',
                    phone: '',
                    amount: '',
                    coupon: '',
                    stripeToken: ''
                }),
                card_number: '',
                month: '',
                year: '',
                cvc: '',
                discount: 0,
                coupon_error_message: '',
                coupon_success_message: '',
                button: true,
            }
        },
        mounted(){
        },
        methods: {
            checkCoupon(){
                this.coupon_error_message = '';
                this.coupon_success_message = '';
                axios.post('/api/payment/validate/coupon',{
                    amount: this.paymentForm.amount,
                    coupon: this.paymentForm.coupon
                })
                .then(response => response.data)
                .then(response => {
                    this.discount = response.discount;
                    this.coupon_success_message = response.message;
                })
                .catch(error => {
                    this.coupon_error_message = helper.fetchDataErrorMsg(error);
                    this.discount = 0;
                    this.paymentForm.coupon = '';
                });
            },
            injectScript() {
                let self = this;
                let el = document.createElement('SCRIPT');
                let ctr = 0;
                let scriptSource = 'https://js.stripe.com/v2';
                let scripts = document.getElementsByTagName('script');
                let scriptExists = false;
                for (var i in scripts){
                    if (scripts[i].src === scriptSource) {
                        scriptExists = true;
                    }
                }
                el.setAttribute('src', scriptSource);
                if(!scriptExists) {
                    document.head.appendChild(el);
                }

                return new Promise((resolve, reject) => {
                    let handle = window.setInterval(function () {
                        if (window.Stripe) {
                            self.loaded = true;
                            resolve();
                            clearInterval(handle);
                        }
                        ctr++;
                        if (ctr > 1000) {
                            reject("vue-stripe: Unable to load stripe.js");
                            clearInterval(handle);
                        }
                    }, 5);
                });
            },
            proceed(){
                this.button = false;
                toastr.success(i18n.payment.payment_processing);
                if(this.paymentForm.method === 'credit_card')
                    this.stripeCheckout();
                else
                    this.paypalCheckout();
            },
            stripeCheckout(){
                this.injectScript()
                    .then(() => {
                        Stripe.setPublishableKey(this.getConfig('stripe_publishable_key'));
                        Stripe.card.createToken({
                            number: this.card_number,
                            cvc: this.cvc,
                            exp_month: this.month,
                            exp_year: this.year
                        }, this.stripeResponseHandler);
                    })
                    .catch(e => {
                        toastr.error('Could not load Stripe.js!');
                    });
            },
            stripeResponseHandler(status, response) {
                if(status == 200){
                    this.paymentForm.stripeToken = response.id;
                    this.paymentForm.post('/api/payment/'+this.uuid+'/credit-card')
                        .then(response => {
                            toastr.success(response.message);
                            this.$emit('updateStatus');
                            this.button = true;
                        })
                        .catch(error => {
                            helper.showErrorMsg(error);
                            this.button = true;
                        })
                } else {
                    toastr.error(response.error.message);
                    this.button = true;
                }
            },
            formatCurrency(number,currency){
                return helper.formatCurrency(number,currency);
            },
            paypalCheckout(){
                this.paymentForm.post('/api/payment/'+this.uuid+'/paypal')
                    .then(response => {
                        window.location = response;
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                        this.button = true;
                    })
            },
            getConfig(config){
                return helper.getConfig(config);
            }
        }
    }
</script>

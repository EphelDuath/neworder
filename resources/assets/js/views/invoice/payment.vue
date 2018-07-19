<template>
    <div class="card" v-if="showPanel">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 col-md-6 col-sm-6">
                    <h4 class="card-title">{{trans('payment.payment')}}</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-strong">{{trans('payment.payable')}}</td>
                                    <td class="text-right">{{formatCurrency(payable,currency)}}</td>
                                </tr>
                                <tr>
                                    <td class="text-strong">{{trans('payment.paid')}}</td>
                                    <td class="text-right">{{formatCurrency(paid,currency)}}</td>
                                </tr>
                                <tr>
                                    <td class="text-strong">{{trans('payment.balance')}}</td>
                                    <td class="text-right">{{formatCurrency(payable-paid,currency)}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="paid < payable">
                        <h4 class="card-title">{{trans('payment.make_payment')}}</h4>
                        <payment-form :uuid="uuid" @updateStatus="updateStatus" :paid="paid" :payable="payable" v-if="is_staff"></payment-form>
                        <customer-payment-form :uuid="uuid" @updateStatus="updateStatus" :currency="currency" :paid="paid" :payable="payable" v-if="is_customer && hasPermission('make-invoice-payment')"></customer-payment-form>
                    </div>
                    <div v-else>
                        <p class="alert alert-success" v-text="trans('payment.no_payment_due')"></p>
                    </div>
                </div>
                <div class="col-12 col-lg-6 col-md-6 col-sm-6">
                    <button class="btn btn-info btn-sm pull-right" v-if="showPanel" @click="toggle">{{trans('general.hide')}}</button>
                    <h4 class="card-title">{{trans('payment.payment_list')}}</h4>
                    <h6 class="card-subtitle" v-if="payments">{{trans('general.total_result_found',{'count' : payments.total})}}</h6>
                    <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                    <div class="table-responsive">
                        <table class="table" v-if="payments.total">
                            <thead>
                                <tr>
                                    <th>{{trans('transaction.amount')}}</th>
                                    <th>{{trans('payment.payment_method')}}</th>
                                    <th>{{trans('transaction.date')}}</th>
                                    <th class="table-option-sm">{{trans('general.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payment in payments.data">
                                    <td>{{formatCurrency(payment.amount,payment.currency)}}
                                        <span v-if="payment.coupon_discount > 0">
                                            <br />({{trans('coupon.discount')+' '+formatCurrency(payment.coupon_discount,payment.currency)}})
                                        </span>
                                    </td>
                                    <td v-text="(payment.payment_method) ? payment.payment_method.name : ucword(payment.source)"></td>
                                    <td>{{payment.date | moment}}</td>
                                    <td class="table-option-sm">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info btn-sm" @click="getPayment(payment)" v-tooltip="trans('payment.view_payment')" data-toggle="modal" data-target=".payment-detail"><i class="fas fa-arrow-circle-right"></i></button>
                                            <button class="btn btn-danger btn-sm" v-if="is_staff && !payment.source" :key="payment.id" v-confirm="{ok: confirmDelete(payment)}" v-tooltip="trans('payment.delete_payment')"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <pagination-record :page-length.sync="filterPaymentForm.page_length" :records="payments" @updateRecords="getPayments" @change.native="getPayments"></pagination-record>
                </div>
            </div>
        </div>
        <div class="modal fade payment-detail" tabindex="-1" role="dialog" aria-labelledby="paymentDetail" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="paymentDetail">{{trans('payment.payment_detail')}}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" v-if="payment">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="text-strong">{{trans('transaction.unique_id')}}</td><td>{{payment.token}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-strong">{{trans('invoice.invoice_number')}}</td><td>{{getInvoiceNumber(payment.invoice)}}</td>
                                    </tr>
                                    <tr v-if="payment.source">
                                        <td class="text-strong">{{trans('payment.source')}}</td><td>{{ucword(payment.source)}}</td>
                                    </tr>
                                    <tr v-if="payment.payment_method_id">
                                        <td class="text-strong">{{trans('payment.payment_method')}}</td><td>{{payment.payment_method.name}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-strong">{{trans('payment.amount')}}</td><td>{{ formatCurrency(payment.amount,payment.currency)}}</td>
                                    </tr>
                                    <tr v-if="payment.coupon">
                                        <td class="text-strong">{{trans('coupon.coupon')}}</td><td>{{payment.coupon}}</td>
                                    </tr>
                                    <tr v-if="payment.coupon_discount > 0">
                                        <td class="text-strong">{{trans('coupon.discount')}}</td><td>{{ formatCurrency(payment.coupon_discount,payment.currency)}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-strong">{{trans('transaction.date')}}</td><td>{{ payment.created_at | momentDateTime }}</td>
                                    </tr>
                                    <tr v-if="payment.income_category_id && is_staff">
                                        <td class="text-strong">{{trans('transaction.transaction_category',{type: trans('transaction.income')})}}</td><td>{{ payment.income_category.name }}</td>
                                    </tr>
                                    <tr v-if="payment.account_id && is_staff">
                                        <td class="text-strong">{{trans('account.account')}}</td><td>{{ payment.account.name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-strong">{{trans('user.user')}}</td><td>{{ payment.customer.profile.first_name+' '+payment.customer.profile.last_name }}</td>
                                    </tr>
                                    <tr v-if="payment.source">
                                        <td class="text-strong">{{trans('payment.billing_address')}}</td>
                                        <td>
                                            <p v-if="payment.address_line_1">{{payment.address_line_1}}</p>
                                            <p v-if="payment.address_line_2">{{payment.address_line_2}}</p>
                                            <p v-if="payment.city">{{payment.city}}</p>
                                            <p v-if="payment.state">{{payment.state}}</p>
                                            <p v-if="payment.zipcode">{{payment.zipcode}}</p>
                                            <p v-if="payment.country">{{payment.country}}</p>
                                            <p v-if="payment.phone">{{payment.phone}}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">{{trans('general.close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import paymentForm from './payment-form'
    import customerPaymentForm from './customer-payment-form'

    export default {
        components: {paymentForm,customerPaymentForm},
        props: {
            uuid:{
                required: true,
            },
            showPanel:{
                required: true,
            },
            currency:{
                required: true
            }
        },
        data(){
            return {
                is_customer: false,
                is_staff: false,
                payments: {
                    total: 0,
                    data: []
                },
                paid: 0,
                payable: 0,
                filterPaymentForm: {
                    page_length: helper.getConfig('page_length')
                },
                payment: ''
            }
        },
        watch: {
            showPanel(val){
                if(val){
                    this.getPayments();
                }
            }
        },
        mounted(){
            if(!helper.hasPermission('list-invoice')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            if(helper.hasRole('customer'))
                this.is_customer = true;
            else
                this.is_staff = true;
        },
        methods: {
            toggle(){
                this.$emit('toggle');
            },
            getPayments(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterPaymentForm);
                axios.get('/api/invoice/'+this.uuid+'/payment?page=' + page + url)
                    .then(response => response.data)
                    .then(response => {
                        this.payments = response.payments;
                        this.paid = response.paid;
                        this.payable = response.payable;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    })
            },
            getPayment(payment){
                axios.get('/api/invoice/'+this.uuid+'/payment/'+payment.uuid)
                    .then(response => response.data)
                    .then(response => {
                        this.payment = response;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    })
            },
            confirmDelete(payment){
                return dialog => this.deletePayment(payment);
            },
            deletePayment(payment){
                axios.delete('/api/invoice/'+this.uuid+'/payment/'+payment.id)
                    .then(response => response.data)
                    .then(response => {
                        this.$emit('updateStatus');
                        toastr.success(response.message);
                        this.getPayments();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            updateStatus(){
                this.$emit('updateStatus');
                this.getPayments();
            },
            formatCurrency(price,currency){
                return helper.formatCurrency(price,currency);
            },
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            ucword(str){
                return helper.ucword(str);
            },
            formatCurrency(amount,currency){
                return helper.formatCurrency(amount,currency);
            },
            getInvoiceNumber(invoice){
                return invoice.prefix+''+helper.formatWithPadding(invoice.number,helper.getConfig('invoice_number_digit'));
            },
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          },
          momentDateTime(datetime){
            return helper.formatDateTime(datetime);
          }
        },
        computed: {

        }
    }
</script>

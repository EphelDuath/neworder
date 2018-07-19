<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('invoice.invoice')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/invoice">{{trans('invoice.invoice')}}</router-link></li>
                    <li class="breadcrumb-item active">{{getInvoiceNumber(invoice)}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="row m-b-5">
                    <div class="col-12 col-md-8 p-0">
                        <div class="btn btn-group">
                            <router-link v-if="is_staff && !invoice.is_cancelled" :to="`/invoice/${invoice.uuid}/edit`" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> {{trans('general.edit')}}</router-link>
                            <button v-if="is_staff && invoice.is_draft && !invoice.is_cancelled" key="send" v-confirm="{ok: confirmSendInvoice(invoice)}" class="btn btn-success btn-sm"><i class="fas fa-send"></i> {{trans('invoice.send')}}</button>
                            <button v-if="is_staff && !invoice.is_draft && !invoice.is_cancelled" @click="toggleRecurringPanel" class="btn btn-danger btn-sm"><i class="fas fa-repeat"></i> {{trans('invoice.recurring')}}</button>
                            <button v-if="!invoice.is_draft && !invoice.is_cancelled" @click="togglePaymentPanel" class="btn btn-success btn-sm"><i class="fas fa-money"></i> {{trans('invoice.payment')}}</button>
                            <a :href="`/invoice/${invoice.uuid}/print?token=${authToken}`" v-if="!invoice.is_draft && !invoice.is_cancelled" class="btn btn-info btn-sm"><i class="fas fa-print"></i> {{trans('invoice.print')}}</a>
                            <a :href="`/invoice/${invoice.uuid}/pdf?token=${authToken}`" v-if="!invoice.is_draft && !invoice.is_cancelled" class="btn btn-primary btn-sm"><i class="far fa-file-pdf"></i> {{trans('invoice.pdf')}}</a>
                            <button v-if="is_staff && !invoice.is_draft && !invoice.is_cancelled && getConfig('email_template')" @click="toggleMailPanel" class="btn btn-success btn-sm"><i class="fas fa-envelope"></i> {{trans('invoice.email')}}</button>

                            <button v-if="is_staff && !invoice.is_draft && invoice.is_cancelled" key="undo_canel" v-confirm="{ok: confirmUndoCancelInvoice(invoice)}" class="btn btn-secondary btn-sm"><i class="fas fa-strikethrough"></i> {{trans('invoice.undo_cancel')}}</button>
                            <div class="dropdown m-l-20" v-if="is_staff && !invoice.is_cancelled">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="invoiceActionButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{trans('invoice.more')}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="invoiceActionButton">
                                    <li class="dropdown-item" v-if="invoice.is_draft" key="mark_as_sent" v-confirm="{ok: confirmMarkAsSent(invoice)}">{{trans('invoice.mark_as_sent')}}</li>
                                    <li class="dropdown-item" key="copy" v-confirm="{ok: confirmCopy(invoice)}">{{trans('invoice.copy_invoice')}}</li>
                                    <li class="dropdown-item" key="cancel" v-if="!invoice.is_draft" v-confirm="{ok: confirmCancelInvoice(invoice)}">{{trans('invoice.cancel_invoice')}}</li>
                                    <li class="dropdown-item" key="partial_payment" v-if="!invoice.is_draft" v-confirm="{ok: confirmTogglePartialPayment(invoice)}">
                                        <span v-if="!invoice.partial_payment">
                                            {{trans('invoice.enable_partial_payment')}}
                                        </span>
                                        <span v-else>
                                            {{trans('invoice.disable_partial_payment')}}
                                        </span>
                                    </li>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 p-0" v-if="is_staff">
                        <div class="btn btn-group pull-right">
                            <router-link to="/invoice/create" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> {{trans('invoice.new')}}</router-link>
                            <button class="btn btn-danger btn-sm" :key="invoice.id" v-confirm="{ok: confirmDelete(invoice)}"><i class="fas fa-trash"></i> {{trans('invoice.delete')}}</button>
                        </div>
                    </div>
                </div>
                <transition name="fade">
                    <payment-template :uuid="uuid" :currency="invoice.currency" :show-panel="showPaymentPanel" @toggle="togglePaymentPanel" @updateStatus="updateInvoiceStatus"></payment-template>
                </transition>
                <transition name="fade">
                    <recurring-template :uuid="uuid" :show-panel="showRecurringPanel" @toggle="toggleRecurringPanel"></recurring-template>
                </transition>
                <transition name="fade">
                    <mail-template :uuid="uuid" :show-panel="showMailPanel" @toggle="toggleMailPanel"></mail-template>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <img :src="getMainLogo">
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="pull-right text-right">
                                    {{trans('invoice.reference_number')}}: <strong>{{invoice.reference_number}}</strong> <br />
                                    {{trans('invoice.invoice_number')}}: <strong>{{getInvoiceNumber(invoice)}}</strong> <br />
                                    <span v-if="invoice.date">{{trans('invoice.date')}}: <strong>{{invoice.date | moment}}</strong> <br /></span>
                                    <span v-if="invoice.date && invoice.due_date_detail">{{trans('invoice.due_date')}}: <strong>{{invoice.due_date_detail | moment}}</strong></span>
                                </p>
                            </div>
                            <div class="col-12 col-md-6">
                                <h2>{{trans('company.company_info')}}</h2>
                                <h4>{{getConfig('company_name')}}</h4>
                                <p class="contact-info">{{trans('configuration.email')}}: {{getConfig('email')}}</p>
                                <p class="contact-info" v-if="getConfig('phone')">{{trans('configuration.phone')}}: <strong>{{getConfig('phone')}}</strong></p>
                                <p class="contact-info" v-if="getConfig('fax')">{{trans('configuration.fax')}}: <strong>{{getConfig('fax')}}</strong></p>
                                <p class="contact-info" v-if="getConfig('website')">{{trans('configuration.website')}}: <strong>{{getConfig('website')}}</strong></p>
                                <p class="contact-info">{{getConfig('address_line_1')}}<span v-if="getConfig('address_line_2')">, {{getConfig('address_line_2')}}</span></p>
                                <p class="contact-info"><span v-if="getConfig('city')">{{getConfig('city')}}</span><span v-if="getConfig('state')">, {{getConfig('state')}}</span><span v-if="getConfig('zipcode')">, {{getConfig('zipcode')}},</span> {{getConfig('country')}}</p>
                            </div>
                            <div class="col-12 col-md-6">

                                <div class="text-right">
                                    <span v-for="status in getInvoiceStatus(invoice)" :class="['label','lb-md','label-'+status.color,'m-l-5']">{{status.label}}</span>
                                </div>

                                <h2 class="text-right">{{trans('user.customer')}}</h2>
                                <div class="pull-right text-right" v-if="invoice.customer">
                                    <h4 v-if="invoice.customer.profile.company" class="text-strong">
                                        {{invoice.customer.profile.company.name}}
                                    </h4>
                                    <h5 class="text-strong">{{invoice.customer.profile.first_name}} {{invoice.customer.profile.last_name}}</h5>
                                    <p class="contact-info">{{trans('user.email')}}: <strong>{{invoice.customer.email}}</strong></p>
                                    <p class="contact-info" v-if="invoice.customer.profile.phone">{{trans('user.phone')}}: <strong>{{invoice.customer.profile.phone}}</strong></p>
                                    <p class="contact-info">{{invoice.customer.profile.address_line_1}}<span v-if="invoice.customer.profile.address_line_2">, {{invoice.customer.profile.address_line_2}}</span></p>
                                    <p class="contact-info"><span v-if="invoice.customer.profile.city">{{invoice.customer.profile.city}}</span><span v-if="invoice.customer.profile.state">, {{invoice.customer.profile.state}}</span><span v-if="invoice.customer.profile.zipcode">, {{invoice.customer.profile.zipcode}}</span><span v-if="invoice.customer.profile.country_id">, {{countries[invoice.customer.profile.country_id]}}</span></p>
                                </div>
                            </div>
                            <div class="col-12 m-t-20">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td class="text-strong">#</td>
                                                <td class="text-strong">{{trans('invoice.item')}}</td>
                                                <td v-if="invoice.line_item_description" class="text-strong">{{trans('invoice.description')}}</td>
                                                <td v-if="invoice.item_type != 'amount'" class="text-strong text-right">
                                                    <span v-if="invoice.item_type === 'quantity'">{{trans('invoice.quantity')}}</span>
                                                    <span v-else>{{trans('invoice.hour')}}</span>
                                                </td>
                                                <td class="text-strong text-right">{{trans('invoice.unit_price')}} ({{invoice.currency.symbol}})</td>
                                                <td v-if="invoice.line_item_discount" class="text-strong text-right">
                                                    {{trans('invoice.discount')}}
                                                    <span v-if="invoice.line_item_discount_type">(%)</span>
                                                    <span v-else>({{invoice.currency.symbol}})</span>
                                                </td>
                                                <td v-if="invoice.line_item_tax" class="text-strong text-right">{{trans('invoice.tax')}} (%)</td>
                                                <td class="text-strong text-right" style="width:150px;">{{trans('invoice.amount')}} ({{invoice.currency.symbol}})</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item,index) in invoice.invoice_item">
                                                <td v-text="index + 1"></td>
                                                <td>
                                                    <span v-if="item.item">{{item.item.name}}</span>
                                                    <span v-else>{{item.name}}</span>
                                                </td>
                                                <td v-if="invoice.line_item_description" v-text="item.description"></td>
                                                <td v-if="invoice.item_type != 'amount'" class="text-right" v-text="formatNumber(item.quantity,getConfig('invoice_line_item_quantity_decimal_place'))"></td>
                                                <td class="text-right" v-text="formatNumber(item.unit_price,invoice.currency.decimal_place)"></td>
                                                <td v-if="invoice.line_item_discount" class="text-right" v-text="formatNumber(item.discount,getConfig('invoice_line_item_discount_decimal_place'))"></td>
                                                <td v-if="invoice.line_item_tax" class="text-right" v-text="formatNumber(item.tax,getConfig('invoice_line_item_tax_decimal_place'))"></td>
                                                <td class="text-right" v-text="formatNumber(item.amount,invoice.currency.decimal_place)"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <blockquote style="margin-top: 10px;" v-if="invoice.tnc">
                                    <h4 class="card-title">{{trans('invoice.tnc')}}</h4>
                                    {{invoice.tnc}}
                                </blockquote>
                                <blockquote style="margin-top: 10px;" v-if="invoice.customer_note">
                                    <h4 class="card-title">{{trans('invoice.customer_note')}}</h4>
                                    {{invoice.customer_note}}
                                </blockquote>
                                <blockquote style="margin-top: 10px;" v-if="invoice.memo">
                                    <h4 class="card-title">{{trans('invoice.memo')}}</h4>
                                    {{invoice.memo}}
                                </blockquote>
                                <div v-show="attachments.length">
                                    <h4 class="card-title">{{trans('invoice.attachment')}}</h4>
                                    <ul style="list-style: none;padding: 0;" class="m-t-10">
                                        <li v-for="attachment in attachments">
                                            <a :href="`/invoice/${invoice.uuid}/attachment/${attachment.uuid}/download?token=${authToken}`"><i class="fas fa-paperclip"></i> {{attachment.user_filename}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 pull-right" style="margin-top: -17px;" v-if="invoice.invoice_item">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="text-strong text-right">{{trans('invoice.subtotal')}} ({{invoice.currency.symbol}})</td>
                                            <td style="width:150px;" class="text-right" v-text="formatNumber(invoice.subtotal,invoice.currency.decimal_place)"></td>
                                        </tr>
                                        <tr v-if="invoice.subtotal_discount">
                                            <td class="text-strong text-right">{{trans('invoice.subtotal_discount')}}
                                                <span v-if="invoice.subtotal_discount_type">(%)</span>
                                                <span v-else>({{invoice.currency.symbol}})</span>
                                            </td>
                                            <td class="text-right" v-text="formatNumber(invoice.subtotal_discount_amount,getConfig('invoice_subtotal_discount_decimal_place'))"></td>
                                        </tr>
                                        <tr v-if="invoice.subtotal_tax">
                                            <td class="text-strong text-right">{{trans('invoice.subtotal_tax')}} (%)</td>
                                            <td class="text-right" v-text="formatNumber(invoice.subtotal_tax_amount,getConfig('invoice_subtotal_tax_decimal_place'))"></td>
                                        </tr>
                                        <tr v-if="invoice.subtotal_shipping_and_handling">
                                            <td class="text-strong text-right">{{trans('invoice.subtotal_shipping_and_handling')}} ({{invoice.currency.symbol}})</td>
                                            <td class="text-right" v-text="formatNumber(invoice.subtotal_shipping_and_handling_amount,getConfig('invoice_subtotal_shipping_and_handling_decimal_place'))"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-strong text-right">{{trans('invoice.total')}} ({{invoice.currency.symbol}})</td>
                                            <td class="text-strong text-right" v-text="formatNumber(invoice.total,invoice.currency.decimal_place)"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import uuid from 'uuid/v4'
    import paymentTemplate from './payment'
    import recurringTemplate from './recurring'
    import mailTemplate from './mail'

    export default {
        components : { paymentTemplate,recurringTemplate,mailTemplate },
        data() {
            return {
                uuid:this.$route.params.uuid,
                txnuuid:this.$route.params.txnuuid,
                invoice: {
                    currency:{},
                    customer:{
                        profile:{
                            company:{}
                        }
                    },
                    invoice_item: [
                    ]
                },
                is_customer: false,
                is_staff: false,
                countries: [],
                today: moment().format('D-MM-YYYY'),
                showRecurringPanel: false,
                showMailPanel: false,
                showPaymentPanel: false,
                attachments: []
            };
        },
        mounted(){
            if(!helper.hasPermission('list-invoice')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getInvoiceDetail(this.uuid);
            if(helper.hasRole('customer'))
                this.is_customer = true;
            else
                this.is_staff = true;

            if(this.txnuuid){
                axios.get('/api/invoice/'+this.uuid+'/payment/'+this.txnuuid)
                    .then(response => response.data)
                    .then(response => {
                        if(response.status > 0)
                            toastr.success(i18n.payment.succeed);
                        else
                            toastr.error(i18n.payment.failed);
                        this.$router.push('/invoice/'+this.uuid);
                    })
                    .catch(error => {
                        this.$router.push('/invoice');
                    });
            }
        },
        watch: {
            '$route.params.uuid'(newUuid, oldUuid) {
                this.getInvoiceDetail(newUuid);
            }
        },
        methods: {
            getInvoiceDetail(uuid){
                axios.get('/api/invoice/'+uuid)
                    .then(response => response.data)
                    .then(response => {
                        this.invoice = response.invoice;
                        this.countries = response.countries;
                        this.attachments = response.attachments;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/invoice');
                    });
            },
            formatNumber(number,decimal_place){
                if(decimal_place === undefined)
                    decimal_place = 2;
                return helper.formatNumber(number,decimal_place);
            },
            getInvoiceNumber(invoice){
                return invoice.prefix+''+helper.formatWithPadding(invoice.number,helper.getConfig('invoice_number_digit'));
            },
            confirmDelete(invoice){
                return dialog => this.deleteInvoice(invoice);
            },
            deleteInvoice(invoice){
                axios.delete('/api/invoice/'+this.uuid)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/invoice');
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmSendInvoice(invoice){
                return dialog => this.sendInvoice(invoice);
            },
            sendInvoice(invoice){
                axios.post('/api/invoice/'+this.uuid+'/send')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.invoice.is_draft = 0;
                        this.invoice.status = 'sent';
                    }).catch(error => {
                        console.log(error.response);
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmMarkAsSent(invoice){
                return dialog => this.markAsSent(invoice);
            },
            markAsSent(invoice){
                axios.post('/api/invoice/'+this.uuid+'/mark-as-sent')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.invoice.is_draft = 0;
                        this.invoice.status = 'sent';
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmCancelInvoice(invoice){
                return dialog => this.cancelInvoice(invoice);
            },
            cancelInvoice(invoice){
                axios.post('/api/invoice/'+this.uuid+'/cancel')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.invoice.is_cancelled = 1;
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmUndoCancelInvoice(invoice){
                return dialog => this.undoCancelInvoice(invoice);
            },
            undoCancelInvoice(){
                axios.post('/api/invoice/'+this.uuid+'/undo-cancel')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.invoice.is_cancelled = 0;
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmCopy(invoice){
                return dialog => this.copyInvoice(invoice);
            },
            copyInvoice(invoice){
                axios.post('/api/invoice/'+this.uuid+'/copy')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/invoice/'+response.uuid);
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmTogglePartialPayment(invoice){
                return dialog => this.togglePartialPayment(invoice);
            },
            togglePartialPayment(invoice){
                axios.post('/api/invoice/'+this.uuid+'/toggle-partial-payment')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.invoice.partial_payment = !this.invoice.partial_payment;
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getConfig(name){
                return helper.getConfig(name);
            },
            getInvoiceStatus(invoice){
                return helper.getInvoiceStatus(invoice);
            },
            toggleMailPanel(){
                this.showMailPanel = !this.showMailPanel;
                this.showPaymentPanel = false;
                this.showRecurringPanel = false;
            },
            toggleRecurringPanel(){
                this.showRecurringPanel = !this.showRecurringPanel;
                this.showPaymentPanel = false;
                this.showMailPanel = false;
            },
            togglePaymentPanel(){
                this.showPaymentPanel = !this.showPaymentPanel;
                this.showRecurringPanel = false;
                this.showMailPanel = false;
            },
            updateInvoiceStatus(){
               this.getInvoiceDetail(this.uuid);
            },
            getConfig(config){
                return helper.getConfig(config);
            }
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
            getMainLogo(){
                if(helper.getConfig('main_logo'))
                    return '/'+this.getConfig('main_logo');
                else
                    return '/images/default_main_logo.png';
            },
            generateUuid(){
                return uuid();
            },
            authToken(){
                return localStorage.getItem('auth_token');
            }
        }
    }
</script>

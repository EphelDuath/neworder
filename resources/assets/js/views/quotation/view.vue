<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('quotation.quotation')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/quotation">{{trans('quotation.quotation')}}</router-link></li>
                    <li class="breadcrumb-item active">{{getQuotationNumber(quotation)}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="row m-b-5">
                    <div class="col-12 col-md-8 p-0">
                        <div class="btn btn-group">
                            <router-link v-if="is_staff && !quotation.is_cancelled" :to="`/quotation/${quotation.uuid}/edit`" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> {{trans('general.edit')}}</router-link>
                            <button v-if="is_staff && quotation.is_draft && !quotation.is_cancelled" key="send" v-confirm="{ok: confirmSendQuotation(quotation)}" class="btn btn-success btn-sm"><i class="fas fa-send"></i> {{trans('quotation.send')}}</button>
                            <a :href="`/quotation/${quotation.uuid}/print?token=${authToken}`" v-if="!quotation.is_draft && !quotation.is_cancelled" class="btn btn-info btn-sm"><i class="fas fa-print"></i> {{trans('quotation.print')}}</a>
                            <a :href="`/quotation/${quotation.uuid}/pdf?token=${authToken}`" v-if="!quotation.is_draft && !quotation.is_cancelled" class="btn btn-primary btn-sm"><i class="far fa-file-pdf"></i> {{trans('quotation.pdf')}}</a>
                            <button v-if="is_staff && !quotation.is_draft && !quotation.is_cancelled && getConfig('email_template')" @click="toggleMailPanel" class="btn btn-success btn-sm"><i class="fas fa-envelope"></i> {{trans('quotation.email')}}</button>
                            <button @click="toggleDiscussionPanel" class="btn btn-warning btn-sm"><i class="fas fa-comment"></i> {{trans('quotation.discussion')}}</button>

                            <button v-if="isAcceptable" key="accept" v-confirm="{ok: confirmAccept(quotation)}" class="btn btn-success btn-sm"><i class="fas fa-thumbs-up"></i> {{trans('quotation.accept')}}</button>
                            <button v-if="isRejectable" key="reject" v-confirm="{ok: confirmReject(quotation)}" class="btn btn-danger btn-sm"><i class="fas fa-thumbs-down"></i> {{trans('quotation.reject')}}</button>

                            <button v-if="is_staff && !quotation.is_draft && quotation.is_cancelled" key="undo_canel" v-confirm="{ok: confirmUndoCancelQuotation(quotation)}" class="btn btn-secondary btn-sm"><i class="fas fa-strikethrough"></i> {{trans('quotation.undo_cancel')}}</button>
                            <div class="dropdown m-l-20" v-if="is_staff && !quotation.is_cancelled">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="quotationActionButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{trans('quotation.more')}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="quotationActionButton">
                                    <li class="dropdown-item" v-if="quotation.is_draft" key="mark_as_sent" v-confirm="{ok: confirmMarkAsSent(quotation)}">{{trans('quotation.mark_as_sent')}}</li>
                                    <li class="dropdown-item" v-if="!quotation.is_draft && !quotation.is_cancelled && quotation.status != 'invoiced'" key="convert_to_invoice" v-confirm="{ok: confirmConvertToInvoice(quotation)}">{{trans('quotation.convert_to_invoice')}}</li>
                                    <li class="dropdown-item" key="copy" v-confirm="{ok: confirmCopy(quotation)}">{{trans('quotation.copy_quotation')}}</li>
                                    <li class="dropdown-item" key="cancel" v-if="!quotation.is_draft" v-confirm="{ok: confirmCancelQuotation(quotation)}">{{trans('quotation.cancel_quotation')}}</li>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 p-0" v-if="is_staff">
                        <div class="btn btn-group pull-right">
                            <router-link to="/quotation/create" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> {{trans('quotation.new')}}</router-link>
                            <button class="btn btn-danger btn-sm" :key="quotation.id" v-confirm="{ok: confirmDelete(quotation)}"><i class="fas fa-trash"></i> {{trans('quotation.delete')}}</button>
                        </div>
                    </div>
                </div>
                <transition name="fade">
                    <mail-template :uuid="uuid" :show-panel="showMailPanel" @toggle="toggleMailPanel"></mail-template>
                </transition>
                <transition name="fade">
                    <discussion-template :uuid="uuid" :show-panel="showDiscussionPanel" @toggle="toggleDiscussionPanel"></discussion-template>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <img :src="getMainLogo">
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="pull-right text-right">
                                    {{trans('quotation.reference_number')}}: <strong>{{quotation.reference_number}}</strong> <br />
                                    {{trans('quotation.quotation_number')}}: <strong>{{getQuotationNumber(quotation)}}</strong> <br />
                                    <span v-if="quotation.date">{{trans('quotation.date')}}: <strong>{{quotation.date | moment}}</strong> <br /></span>
                                    <span v-if="quotation.date && quotation.expiry_date_detail">{{trans('quotation.expiry_date')}}: <strong>{{quotation.expiry_date_detail | moment}}</strong></span>
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
                                    <span v-for="status in getQuotationStatus(quotation)" :class="['label','lb-md','label-'+status.color,'m-l-5']">{{status.label}}</span>
                                </div>

                                <h2 class="text-right">{{trans('user.customer')}}</h2>
                                <div class="pull-right text-right" v-if="quotation.customer">
                                    <h4 v-if="quotation.customer.profile.company" class="text-strong">
                                        {{quotation.customer.profile.company.name}}
                                    </h4>
                                    <h5 class="text-strong">{{quotation.customer.profile.first_name}} {{quotation.customer.profile.last_name}}</h5>
                                    <p class="contact-info">{{trans('user.email')}}: <strong>{{quotation.customer.email}}</strong></p>
                                    <p class="contact-info" v-if="quotation.customer.profile.phone">{{trans('user.phone')}}: <strong>{{quotation.customer.profile.phone}}</strong></p>
                                    <p class="contact-info">{{quotation.customer.profile.address_line_1}}<span v-if="quotation.customer.profile.address_line_2">, {{quotation.customer.profile.address_line_2}}</span></p>
                                    <p class="contact-info"><span v-if="quotation.customer.profile.city">{{quotation.customer.profile.city}}</span><span v-if="quotation.customer.profile.state">, {{quotation.customer.profile.state}}</span><span v-if="quotation.customer.profile.zipcode">, {{quotation.customer.profile.zipcode}}</span><span v-if="quotation.customer.profile.country_id">, {{countries[quotation.customer.profile.country_id]}}</span></p>
                                </div>
                            </div>
                            <div class="col-12 m-t-20" v-if="quotation.subject || quotation.description">
                                <h4 v-if="quotation.subject" v-text="quotation.subject"></h4>
                                <div v-if="quotation.description" v-html="quotation.description" style="text-align:justify;"></div>
                                <hr />
                            </div>
                            <div class="col-12 m-t-20">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td class="text-strong">#</td>
                                                <td class="text-strong">{{trans('quotation.item')}}</td>
                                                <td v-if="quotation.line_item_description" class="text-strong">{{trans('quotation.description')}}</td>
                                                <td v-if="quotation.item_type != 'amount'" class="text-strong text-right">
                                                    <span v-if="quotation.item_type === 'quantity'">{{trans('quotation.quantity')}}</span>
                                                    <span v-else>{{trans('quotation.hour')}}</span>
                                                </td>
                                                <td class="text-strong text-right">{{trans('quotation.unit_price')}} ({{quotation.currency.symbol}})</td>
                                                <td v-if="quotation.line_item_discount" class="text-strong text-right">
                                                    {{trans('quotation.discount')}}
                                                    <span v-if="quotation.line_item_discount_type">(%)</span>
                                                    <span v-else>({{quotation.currency.symbol}})</span>
                                                </td>
                                                <td v-if="quotation.line_item_tax" class="text-strong text-right">{{trans('quotation.tax')}} (%)</td>
                                                <td class="text-strong text-right" style="width:150px;">{{trans('quotation.amount')}} ({{quotation.currency.symbol}})</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item,index) in quotation.quotation_item">
                                                <td v-text="index + 1"></td>
                                                <td>
                                                    <span v-if="item.item">{{item.item.name}}</span>
                                                    <span v-else>{{item.name}}</span>
                                                </td>
                                                <td v-if="quotation.line_item_description" v-text="item.description"></td>
                                                <td v-if="quotation.item_type != 'amount'" class="text-right" v-text="formatNumber(item.quantity,getConfig('quotation_line_item_quantity_decimal_place'))"></td>
                                                <td class="text-right" v-text="formatNumber(item.unit_price,quotation.currency.decimal_place)"></td>
                                                <td v-if="quotation.line_item_discount" class="text-right" v-text="formatNumber(item.discount,getConfig('quotation_line_item_discount_decimal_place'))"></td>
                                                <td v-if="quotation.line_item_tax" class="text-right" v-text="formatNumber(item.tax,getConfig('quotation_line_item_tax_decimal_place'))"></td>
                                                <td class="text-right" v-text="formatNumber(item.amount,quotation.currency.decimal_place)"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <blockquote style="margin-top: 10px;" v-if="quotation.tnc">
                                    <h4 class="card-title">{{trans('quotation.tnc')}}</h4>
                                    {{quotation.tnc}}
                                </blockquote>
                                <blockquote style="margin-top: 10px;" v-if="quotation.customer_note">
                                    <h4 class="card-title">{{trans('quotation.customer_note')}}</h4>
                                    {{quotation.customer_note}}
                                </blockquote>
                                <blockquote style="margin-top: 10px;" v-if="quotation.memo">
                                    <h4 class="card-title">{{trans('quotation.memo')}}</h4>
                                    {{quotation.memo}}
                                </blockquote>
                                <div v-show="attachments.length">
                                    <h4 class="card-title">{{trans('quotation.attachment')}}</h4>
                                    <ul style="list-style: none;padding: 0;" class="m-t-10">
                                        <li v-for="attachment in attachments">
                                            <a :href="`/quotation/${quotation.uuid}/attachment/${attachment.uuid}/download?token=${authToken}`"><i class="fas fa-paperclip"></i> {{attachment.user_filename}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 pull-right" style="margin-top: -17px;" v-if="quotation.quotation_item">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="text-strong text-right">{{trans('quotation.subtotal')}} ({{quotation.currency.symbol}})</td>
                                            <td style="width:150px;" class="text-right" v-text="formatNumber(quotation.subtotal,quotation.currency.decimal_place)"></td>
                                        </tr>
                                        <tr v-if="quotation.subtotal_discount">
                                            <td class="text-strong text-right">{{trans('quotation.subtotal_discount')}}
                                                <span v-if="quotation.subtotal_discount_type">(%)</span>
                                                <span v-else>({{quotation.currency.symbol}})</span>
                                            </td>
                                            <td class="text-right" v-text="formatNumber(quotation.subtotal_discount_amount,getConfig('quotation_subtotal_discount_decimal_place'))"></td>
                                        </tr>
                                        <tr v-if="quotation.subtotal_tax">
                                            <td class="text-strong text-right">{{trans('quotation.subtotal_tax')}} (%)</td>
                                            <td class="text-right" v-text="formatNumber(quotation.subtotal_tax_amount,getConfig('quotation_subtotal_tax_decimal_place'))"></td>
                                        </tr>
                                        <tr v-if="quotation.subtotal_shipping_and_handling">
                                            <td class="text-strong text-right">{{trans('quotation.subtotal_shipping_and_handling')}} ({{quotation.currency.symbol}})</td>
                                            <td class="text-right" v-text="formatNumber(quotation.subtotal_shipping_and_handling_amount,getConfig('quotation_subtotal_shipping_and_handling_decimal_place'))"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-strong text-right">{{trans('quotation.total')}} ({{quotation.currency.symbol}})</td>
                                            <td class="text-strong text-right" v-text="formatNumber(quotation.total,quotation.currency.decimal_place)"></td>
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
    import mailTemplate from './mail'
    import discussionTemplate from './discussion'

    export default {
        components : { mailTemplate,discussionTemplate },
        data() {
            return {
                uuid:this.$route.params.uuid,
                quotation: {
                    currency:{},
                    customer:{
                        profile:{
                            company:{}
                        }
                    },
                    quotation_item: [
                    ]
                },
                is_customer: false,
                is_staff: false,
                countries: [],
                today: moment().format('D-MM-YYYY'),
                showMailPanel: false,
                showDiscussionPanel: false,
                attachments: []
            };
        },
        mounted(){
            if(!helper.hasPermission('list-quotation')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getQuotationDetail(this.uuid);
            if(helper.hasRole('customer'))
                this.is_customer = true;
            else
                this.is_staff = true;
        },
        watch: {
            '$route.params.uuid'(newUuid, oldUuid) {
                this.getQuotationDetail(newUuid);
            }
        },
        methods: {
            getQuotationDetail(uuid){
                axios.get('/api/quotation/'+uuid)
                    .then(response => response.data)
                    .then(response => {
                        this.quotation = response.quotation;
                        this.countries = response.countries;
                        this.attachments = response.attachments;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/quotation');
                    });
            },
            formatNumber(number,decimal_place){
                if(decimal_place === undefined)
                    decimal_place = 2;
                return helper.formatNumber(number,decimal_place);
            },
            getQuotationNumber(quotation){
                return quotation.prefix+''+helper.formatWithPadding(quotation.number,helper.getConfig('quotation_number_digit'));
            },
            confirmDelete(quotation){
                return dialog => this.deleteQuotation(quotation);
            },
            deleteQuotation(quotation){
                axios.delete('/api/quotation/'+this.uuid)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/quotation');
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmSendQuotation(quotation){
                return dialog => this.sendQuotation(quotation);
            },
            sendQuotation(quotation){
                axios.post('/api/quotation/'+this.uuid+'/send')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.quotation.is_draft = 0;
                        this.quotation.status = 'sent';
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmMarkAsSent(quotation){
                return dialog => this.markAsSent(quotation);
            },
            markAsSent(quotation){
                axios.post('/api/quotation/'+this.uuid+'/mark-as-sent')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.quotation.is_draft = 0;
                        this.quotation.status = 'sent';
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmCancelQuotation(quotation){
                return dialog => this.cancelQuotation(quotation);
            },
            cancelQuotation(quotation){
                axios.post('/api/quotation/'+this.uuid+'/cancel')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.quotation.is_cancelled = 1;
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmUndoCancelQuotation(quotation){
                return dialog => this.undoCancelQuotation(quotation);
            },
            undoCancelQuotation(){
                axios.post('/api/quotation/'+this.uuid+'/undo-cancel')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.quotation.is_cancelled = 0;
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmCopy(quotation){
                return dialog => this.copyQuotation(quotation);
            },
            copyQuotation(quotation){
                axios.post('/api/quotation/'+this.uuid+'/copy')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/quotation/'+response.uuid);
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getConfig(name){
                return helper.getConfig(name);
            },
            getQuotationStatus(quotation){
                return helper.getQuotationStatus(quotation);
            },
            toggleMailPanel(){
                this.showMailPanel = !this.showMailPanel;
                this.showDiscussionPanel = false;
            },
            toggleDiscussionPanel(){
                this.showDiscussionPanel = !this.showDiscussionPanel;
                this.showMailPanel = false;
            },
            updateQuotationStatus(){
               this.getQuotationDetail(this.uuid);
            },
            confirmAccept(quotation){
                return dialog => this.processCustomerAction(quotation,'accept');
            },
            confirmReject(quotation){
                return dialog => this.processCustomerAction(quotation,'reject');
            },
            processCustomerAction(quotation,action){
                axios.post('/api/quotation/'+this.uuid+'/action',{action:action})
                    .then(response => response.data)
                    .then(response => {
                        this.quotation.status = response.status;
                        toastr.success(response.message);
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            confirmConvertToInvoice(quotation){
                return dialog => this.convertToInvoice(quotation);
            },
            convertToInvoice(quotation){
                axios.post('/api/quotation/'+this.uuid+'/convert-to-invoice')
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/invoice/'+response.invoice_uuid);
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
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
            },
            isAcceptable(){
                if(this.is_customer && this.quotation.status !== 'accepted' && this.quotation.status != 'invoiced' && !this.quotation.is_draft && !this.quotation.is_cancelled && this.quotation.expiry_date != -1 && this.quotation.expiry_date_detail > moment().format('YYYY-MM-DD'))
                    return true;
                else
                    return false;
            },
            isRejectable(){
                if(this.is_customer && this.quotation.status !== 'rejected' && this.quotation.status != 'invoiced' && !this.quotation.is_draft && !this.quotation.is_cancelled && this.quotation.expiry_date != -1 && this.quotation.expiry_date_detail > moment().format('YYYY-MM-DD'))
                    return true;
                else
                    return false;
            }
        }
    }
</script>

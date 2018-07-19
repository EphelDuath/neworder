<template>
    <div>
        <div class="pull-right">
            <router-link v-if="uuid" class="btn btn-success" :to="`/quotation/${uuid}`">{{trans('quotation.view_quotation')}}</router-link>
            <button type="button" class="btn btn-info" @click.prevent="save">{{trans('quotation.save')}}</button>
            <button type="submit" class="btn btn-success" @click.prevent="send" v-if="quotationForm.rows.length && !uuid">{{trans('quotation.send')}}</button>
            <router-link to="/quotation" class="btn btn-danger">{{trans('quotation.cancel_button')}}</router-link>
        </div>
        <h4 class="card-title" v-if="!uuid">{{trans('quotation.create_quotation')}}</h4>
        <h4 class="card-title" v-else>{{trans('quotation.edit_quotation')}}</h4>
        <form @keydown="quotationForm.errors.clear($event.target.name)">
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="">{{trans('user.customer')}}</label>
                        <v-select label="name" v-model="selected_customer" name="customer_id" id="customer_id" :options="customers" :placeholder="trans('user.select_customer')" @select="onCustomerSelect" @close="quotationForm.errors.clear('customer_id')" @remove="quotationForm.customer_id = ''"></v-select>
                        <show-error :form-name="quotationForm" prop-name="customer_id"></show-error>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">{{trans('quotation.item_type')}}</label>
                                <v-select label="name" v-model="selected_item_type" name="item_type" id="item_type" :options="item_types" :placeholder="trans('quotation.select_item_type')" @select="onItemTypeSelect" @close="quotationForm.errors.clear('item_type')" @remove="quotationForm.item_type = ''"></v-select>
                                <show-error :form-name="quotationForm" prop-name="item_type"></show-error>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">{{trans('currency.currency')}}</label>
                                <v-select label="name" v-model="selected_currency" name="currency_id" id="currency_id" :options="currencies" :placeholder="trans('currency.select_currency')" @select="onCurrencySelect" @close="quotationForm.errors.clear('currency_id')" @remove="quotationForm.currency_id = ''"></v-select>
                                <show-error :form-name="quotationForm" prop-name="currency_id"></show-error>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group m-t-20 row">
                        <label for="" class="col-4 col-form-label text-right">{{trans('quotation.quotation_number')}}</label>
                        <div class="col-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <input class="form-control" type="text" value="" v-model="quotationForm.quotation_prefix" name="quotation_prefix" :placeholder="trans('transaction.quotation_prefix')">
                                    <show-error :form-name="quotationForm" prop-name="quotation_prefix"></show-error>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" value="" v-model="quotationForm.quotation_number" name="quotation_number" :placeholder="trans('transaction.quotation_number')">
                                    <show-error :form-name="quotationForm" prop-name="quotation_number"></show-error>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-4 col-form-label text-right">{{trans('quotation.reference_number')}}</label>
                        <div class="col-8">
                            <input class="form-control" type="text" value="" v-model="quotationForm.reference_number" name="reference_number" :placeholder="trans('transaction.reference_number')">
                            <show-error :form-name="quotationForm" prop-name="reference_number"></show-error>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-4 col-form-label text-right">{{trans('quotation.date')}}</label>
                        <div class="col-8">
                            <datepicker v-model="quotationForm.date" :bootstrapStyling="true" @selected="quotationForm.errors.clear('date')" :placeholder="trans('transaction.date')" @input="calculateExpiryDateDetail"></datepicker>
                            <show-error :form-name="quotationForm" prop-name="date"></show-error>
                        </div>
                    </div>
                    <div class="form-group row" v-if="quotationForm.date">
                        <label for="" class="col-4 col-form-label text-right">{{trans('quotation.expiry_date')}}</label>
                        <div class="col-8">
                            <v-select label="name" v-model="selected_expiry_date" name="expiry_date" id="expiry_date" :options="expiry_dates" :placeholder="trans('quotation.select_expiry_date')" @select="onExpiryDateSelect" @close="quotationForm.errors.clear('expiry_date')" @remove="quotationForm.expiry_date = ''"></v-select>
                            <show-error :form-name="quotationForm" prop-name="expiry_date"></show-error>
                        </div>
                    </div>
                    <div class="form-group row" v-if="quotationForm.date && selected_expiry_date && selected_expiry_date.id && quotationForm.expiry_date != -1">
                        <label for="" class="col-4 col-form-label text-right">{{trans('quotation.expiry_date')}}</label>
                        <div class="col-8">
                            <div v-if="quotationForm.expiry_date === 'expiry_on_date'">
                                <datepicker v-model="quotationForm.expiry_date_detail" name="expiry_date_detail" :bootstrapStyling="true" @selected="quotationForm.errors.clear('expiry_date_detail')" :placeholder="trans('transaction.expiry_date')"></datepicker>
                                <show-error :form-name="quotationForm" prop-name="expiry_date_detail"></show-error>
                            </div>
                            <div v-if="quotationForm.expiry_date !== 'expiry_on_date'">
                                <label class="col-form-label">{{expiry_date_detail}}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row m-t-20">
                <div class="col-12">
                    <div class="form-group">
                        <label for="">{{trans('quotation.subject')}}</label>
                        <autosize-textarea v-model="quotationForm.subject" rows="2" name="subject" :placeholder="trans('quotation.subject')"></autosize-textarea>
                        <show-error :form-name="quotationForm" prop-name="subject"></show-error>
                    </div>
                    <div class="form-group">
                        <html-editor name="description" :model.sync="quotationForm.description" :isUpdate="uuid ? true : false" @clearErrors="quotationForm.errors.clear('description')"></html-editor>
                        <show-error :form-name="quotationForm" prop-name="description"></show-error>
                    </div>
                </div>
            </div>
            <div class="table-responsive m-t-20" v-if="selected_item_type">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td class="text-strong">
                                <div class="dropdown m-b-10">
                                  <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-toggle="dropdown" id="quotation-dropdown"  aria-haspopup="true" aria-expanded="true">
                                    {{trans('quotation.option')}}
                                    <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu p-10" role="menu" aria-labelledby="quotation-dropdown">
                                    <li class="dropdown-header">{{trans('quotation.add_to_line_item')}}</li>
                                    <li><div class="checkbox"><input name="line_item_tax" type="checkbox" value="1" id="line_item_tax" v-model="quotationForm.show_line_item_tax" @change="calculate"> <label for="line_item_tax">{{trans('quotation.tax')}}</label></div></li>
                                    <li><div class="checkbox"><input name="line_item_discount" type="checkbox" value="1" id="line_item_discount" v-model="quotationForm.show_line_item_discount" @change="calculate"> <label for="line_item_discount">{{trans('quotation.discount')}}</label></div></li>
                                    <li><div class="checkbox"><input name="line_item_description" type="checkbox" value="1" id="line_item_description" v-model="quotationForm.show_line_item_description"> <label for="line_item_description">{{trans('quotation.description')}}</label></div></li>
                                    <li role="separator" class="divider"></li>
                                    <li class="dropdown-header">{{trans('quotation.add_to_subtotal')}}</li>
                                    <li><div class="checkbox"><input name="subtotal_tax" type="checkbox" value="1" id="subtotal_tax" v-model="quotationForm.show_sub_total_tax" @change="calculate"> <label for="subtotal_tax">{{trans('quotation.tax')}}</label></div></li>
                                    <li><div class="checkbox"><input name="subtotal_discount" type="checkbox" value="1" id="subtotal_discount" v-model="quotationForm.show_sub_total_discount" @change="calculate"> <label for="subtotal_discount">{{trans('quotation.discount')}}</label></div></li>
                                    <li><div class="checkbox"><input name="subtotal_shipping_and_handling" type="checkbox" value="1" id="subtotal_shipping_and_handling" v-model="quotationForm.show_sub_total_shipping_and_handling" @change="calculate"> <label for="subtotal_shipping_and_handling">{{trans('quotation.shipping_and_handling')}}</label></div></li>
                                  </ul>
                                </div>
                                <button class="btn btn-info btn-sm" @click="addRow" type="button">{{trans('quotation.add_row')}}</button>
                            </td>
                            <td class="text-strong">{{trans('quotation.item')}}</td>
                            <td class="text-strong text-right" v-show="show_quantity">{{trans('quotation.quantity_type',{type: trans('quotation.'+selected_item_type.id) })}}</td>
                            <td class="text-strong text-right">{{trans('quotation.unit_price_type',{type: trans('quotation.'+(selected_item_type.id === 'hour' ? 'rate' : 'unit_price'))})}} ({{selected_currency.symbol}})</td>
                            <td class="text-strong text-right" v-show="quotationForm.show_line_item_discount">
                                {{trans('quotation.discount')}}
                                <span v-if="quotationForm.line_item_discount_type">(%)</span>
                                <span v-else>({{selected_currency.symbol}})</span>
                                <div class="checkbox pull-right"><input name="line_item_discount_type" type="checkbox" value="1" id="line_item_discount_type" v-model="quotationForm.line_item_discount_type" @change="calculate"> <label for="line_item_discount_type">{{trans('quotation.percent')}}</label></div>
                            </td>
                            <td class="text-strong text-right" v-show="quotationForm.show_line_item_tax">{{trans('quotation.tax')}} (%)</td>
                            <td class="text-strong text-right" style="width:150px;">{{trans('quotation.amount')}} ({{selected_currency.symbol}})</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row,index) in quotationForm.rows">
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-danger btn-sm" type="button" :key="row.uuid" v-confirm="{ok: confirmRowDelete(row)}" v-tooltip="trans('quotation.delete_row')"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-info btn-sm" type="button" @click="customItemToggle(row)">
                                        <i class="fas fa-text-height" v-if="!row.show_custom_item" v-tooltip="trans('quotation.show_custom_item_box')"></i>
                                        <i class="fas fa-th" v-else v-tooltip="trans('quotation.show_item_list')"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <v-select label="name" v-model="row.selected_item" :name="'item_name_'+index" :id="'item_'+row.uuid" :options="items" :placeholder="trans('item.select_item')" @close="row.name = row.selected_item.name" @remove="row.name = ''" v-if="!row.show_custom_item" @select="onItemSelect"></v-select>
                                <input class="form-control quotation-input-left" :name="'item_name_'+index" v-model="row.custom_name" :placeholder="trans('quotation.custom_name')" v-if="row.show_custom_item">
                                <show-error :form-name="quotationForm" :prop-name="'item_name_'+index"></show-error>
                                <autosize-textarea v-if="quotationForm.show_line_item_description" row="1" class="form-control quotation-input-left" v-model="row.description" :placeholder="trans('quotation.description')" name="description"></autosize-textarea>
                            </td>
                            <td v-show="show_quantity">
                                <input class="form-control quotation-input" :name="'item_quantity_'+index" v-model="row.quantity" :placeholder="trans('quotation.quantity')" @change="calculate">
                                <show-error :form-name="quotationForm" :prop-name="'item_quantity_'+index"></show-error>
                            </td>
                            <td>
                                <input class="form-control quotation-input" :name="'item_unit_price_'+index" v-model="row.unit_price" :placeholder="trans('quotation.unit_price_type',{type: trans('quotation.'+(selected_item_type.id === 'hour' ? 'rate' : 'unit_price'))})" @change="calculate">
                                <show-error :form-name="quotationForm" :prop-name="'item_unit_price_'+index"></show-error>
                            </td>
                            <td v-show="quotationForm.show_line_item_discount">
                                <input class="form-control quotation-input" :name="'item_discount_'+index" v-model="row.discount" :placeholder="trans('quotation.discount')" @change="calculate">
                                <show-error :form-name="quotationForm" :prop-name="'item_discount_'+index"></show-error>
                            </td>
                            <td v-show="quotationForm.show_line_item_tax">
                                <input class="form-control quotation-input" :name="'item_tax_'+index" v-model="row.tax" :placeholder="trans('quotation.tax')" @change="calculate">
                                <show-error :form-name="quotationForm" :prop-name="'item_tax_'+index"></show-error>
                            </td>
                            <td class="text-right">
                                <span class="m-r-10">{{row.amount}}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pull-right" style="margin-top: -17px;" v-if="quotationForm.rows.length">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="text-strong">{{trans('quotation.subtotal')}} ({{selected_currency.symbol}})</td>
                                <td style="width:150px;" class="text-right"><span class="m-r-10">{{quotationForm.subtotal}}</span></td>
                            </tr>
                            <tr v-show="quotationForm.show_sub_total_discount">
                                <td class="text-strong">
                                    {{trans('quotation.subtotal_discount')}}
                                    <span v-if="quotationForm.subtotal_discount_type">(%)</span>
                                    <span v-else>({{selected_currency.symbol}})</span>
                                    <div class="checkbox"><input name="subtotal_discount_type" type="checkbox" value="1" id="subtotal_discount_type" v-model="quotationForm.subtotal_discount_type" @change="calculate"> <label for="subtotal_discount_type">{{trans('quotation.percent')}}</label></div>
                                </td>
                                <td>
                                    <input class="form-control quotation-input" name="subtotal_discount" v-model="quotationForm.subtotal_discount" :placeholder="trans('quotation.subtotal_discount')" @change="calculate">
                                    <show-error :form-name="quotationForm" prop-name="subtotal_discount"></show-error>
                                </td>
                            </tr>
                            <tr v-show="quotationForm.show_sub_total_tax">
                                <td class="text-strong">{{trans('quotation.subtotal_tax')}} (%)</td>
                                <td>
                                    <input class="form-control quotation-input" name="subtotal_shipping_and_handling" v-model="quotationForm.subtotal_tax" :placeholder="trans('quotation.subtotal_tax')" @change="calculate">
                                    <show-error :form-name="quotationForm" prop-name="subtotal_shipping_and_handling"></show-error>
                                </td>
                            </tr>
                            <tr v-show="quotationForm.show_sub_total_shipping_and_handling">
                                <td class="text-strong">{{trans('quotation.subtotal_shipping_and_handling')}}</td>
                                <td>
                                    <input class="form-control quotation-input" v-model="quotationForm.subtotal_shipping_and_handling" :placeholder="trans('quotation.subtotal_shipping_and_handling')" @change="calculate">
                                    <show-error :form-name="quotationForm" prop-name="subtotal_shipping_and_handling"></show-error>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-strong">{{trans('quotation.total')}} ({{selected_currency.symbol}})</td>
                                <td class="text-right"><span class="m-r-10">
                                    <span v-if="quotationForm.total < 0" class="text-strong has-error">
                                        {{quotationForm.total}}
                                    </span>
                                    <span v-else>
                                        {{quotationForm.total}}
                                    </span>
                                </span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="row m-t-40">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="">{{trans('quotation.tnc')}}</label>
                        <autosize-textarea v-model="quotationForm.tnc" rows="2" name="tnc" :placeholder="trans('quotation.tnc')"></autosize-textarea>
                        <show-error :form-name="quotationForm" prop-name="tnc"></show-error>
                    </div>
                    <div class="form-group">
                        <file-upload-input :button-text="trans('general.upload_document')" :token="quotationForm.upload_token" module="quotation" :clear-file="clear_quotation_attachment" :module-id="quotationForm.id"></file-upload-input>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="">{{trans('quotation.customer_note')}}</label>
                        <autosize-textarea v-model="quotationForm.customer_note" rows="2" name="customer_note" :placeholder="trans('quotation.customer_note')"></autosize-textarea>
                        <show-error :form-name="quotationForm" prop-name="customer_note"></show-error>
                    </div>
                    <div class="form-group">
                        <label for="">{{trans('quotation.memo')}}</label>
                        <autosize-textarea v-model="quotationForm.memo" rows="2" name="memo" :placeholder="trans('quotation.memo')"></autosize-textarea>
                        <show-error :form-name="quotationForm" prop-name="memo"></show-error>
                    </div>
                </div>
            </div>
            <div class="form-group pull-right">
                <button type="button" class="btn btn-info" @click.prevent="save">{{trans('quotation.save')}}</button>
                <button type="submit" class="btn btn-success" @click.prevent="send" v-if="quotationForm.rows.length && !uuid">{{trans('quotation.send')}}</button>
                <router-link to="/quotation" class="btn btn-danger">{{trans('quotation.cancel_button')}}</router-link>
            </div>
        </form>
    </div>
</template>

<script>
    import uuid from 'uuid/v4'
    import datepicker from 'vuejs-datepicker'
    import vSelect from 'vue-multiselect'
    import fileUploadInput from '../../components/file-upload-input'
    import currencyInput from '../../components/currency-input'
    import autosizeTextarea from '../../components/autosize-textarea'
    import htmlEditor from '../../components/html-editor'

    export default {
        components : { uuid,datepicker,vSelect,fileUploadInput,currencyInput,autosizeTextarea,htmlEditor },
        props: ['uuid'],
        data() {
            return {
                quotationForm: new Form({
                    id: '',
                    customer_id: null,
                    currency_id: null,
                    date: '',
                    expiry_date: '',
                    expiry_date_detail: '',
                    upload_token: '',
                    item_type: '',
                    rows: [],
                    subject: '',
                    description: '',
                    subtotal: 0,
                    subtotal_discount: 0,
                    line_item_discount_type: true,
                    subtotal_discount_type: false,
                    subtotal_tax: 0,
                    subtotal_shipping_and_handling: 0,
                    total: 0,
                    reference_number: this.newReferenceNumber(),
                    quotation_number: '',
                    quotation_prefix: '',
                    is_draft: 0,
                    show_sub_total_tax: true,
                    show_sub_total_discount: true,
                    show_sub_total_shipping_and_handling: true,
                    show_line_item_tax: true,
                    show_line_item_discount: true,
                    show_line_item_description: true,
                    customer_note: '',
                    memo: '',
                    tnc: ''
                },false),
                items: [],
                item_details: [],
                customers: [],
                selected_customer: null,
                currencies: [],
                selected_currency: null,
                default_currency: {},
                item_types: [],
                selected_item_type: null,
                expiry_dates: [],
                selected_expiry_date: null,
                expiry_date_detail: '',
                clear_quotation_attachment: false,
                show_quantity: true,
            };
        },
        mounted(){
            if(!this.uuid)
                this.quotationForm.upload_token = uuid();
            this.getPreRequisites();

            if(this.uuid)
                this.getQuotationDetail();
        },
        methods: {
            getPreRequisites(){
                axios.get('/api/quotation/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.currencies = response.currencies;
                    this.customers = response.customers;
                    this.default_currency = response.default_currency;
                    this.item_types = response.item_types;
                    if(!this.uuid){
                        this.selected_currency = this.default_currency;
                        this.quotationForm.currency_id = this.default_currency.id;
                        this.selected_item_type = response.default_item_type;
                        this.quotationForm.item_type = 'quantity';
                        this.quotationForm.quotation_number = helper.formatWithPadding(response.new_quotation_number,helper.getConfig('quotation_number_digit'));
                        this.quotationForm.quotation_prefix = helper.getConfig('quotation_prefix');
                    }
                    this.items = response.items;
                    this.item_details = response.item_details;
                    this.expiry_dates = response.expiry_dates;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
                this.addRow();
                if(!this.uuid){
                    this.quotationForm.show_sub_total_tax = helper.getConfig('quotation_default_subtotal_tax');
                    this.quotationForm.show_sub_total_discount = helper.getConfig('quotation_default_subtotal_discount');
                    this.quotationForm.show_sub_total_shipping_and_handling = helper.getConfig('quotation_default_subtotal_shipping_and_handling');
                    this.quotationForm.show_line_item_tax = helper.getConfig('quotation_default_line_item_tax');
                    this.quotationForm.show_line_item_discount = helper.getConfig('quotation_default_line_item_discount');
                    this.quotationForm.show_line_item_description = helper.getConfig('quotation_default_line_item_description');
                }
            },
            getQuotationDetail(){
                axios.get('/api/quotation/'+this.uuid+'/detail')
                    .then(response => response.data)
                    .then(response => {
                        this.quotationForm.id = response.quotation.id;
                        this.selected_currency = response.selected_currency;
                        this.quotationForm.currency_id = response.quotation.currency_id;
                        this.selected_customer = response.selected_customer;
                        this.quotationForm.customer_id = response.quotation.customer_id;
                        this.selected_item_type = response.selected_item_type;
                        this.quotationForm.item_type = response.quotation.item_type;
                        this.selected_expiry_date = response.selected_expiry_date;
                        this.quotationForm.date = response.quotation.date;
                        this.quotationForm.expiry_date = response.quotation.expiry_date;
                        this.quotationForm.expiry_date_detail = response.quotation.expiry_date_detail;
                        this.quotationForm.upload_token = response.quotation.upload_token;
                        this.quotationForm.rows = response.rows;
                        this.quotationForm.subtotal = helper.formatNumber(response.quotation.subtotal,this.selected_currency.decimal_place);
                        this.quotationForm.subtotal_discount = helper.formatNumber(response.quotation.subtotal_discount_amount,this.selected_currency.decimal_place);
                        this.quotationForm.line_item_discount_type = response.quotation.line_item_discount_type;
                        this.quotationForm.subtotal_discount_type = response.quotation.subtotal_discount_type;
                        this.quotationForm.subtotal_tax = helper.formatNumber(response.quotation.subtotal_tax_amount,this.selected_currency.decimal_place);
                        this.quotationForm.subtotal_shipping_and_handling = helper.formatNumber(response.quotation.subtotal_shipping_and_handling_amount,this.selected_currency.decimal_place);
                        this.quotationForm.total = helper.formatNumber(response.quotation.total,this.selected_currency.decimal_place);
                        this.quotationForm.reference_number = response.quotation.reference_number;
                        this.quotationForm.quotation_number = helper.formatWithPadding(response.quotation.number,helper.getConfig('quotation_number_digit'));
                        this.quotationForm.quotation_prefix = response.quotation.prefix;
                        this.quotationForm.is_draft = response.quotation.is_draft;
                        this.quotationForm.show_sub_total_tax = response.quotation.subtotal_tax;
                        this.quotationForm.show_sub_total_discount = response.quotation.subtotal_discount;
                        this.quotationForm.show_sub_total_shipping_and_handling = response.quotation.subtotal_shipping_and_handling;
                        this.quotationForm.show_line_item_tax = response.quotation.line_item_tax;
                        this.quotationForm.show_line_item_discount = response.quotation.line_item_discount;
                        this.quotationForm.show_line_item_description = response.quotation.line_item_description;
                        this.quotationForm.customer_note = response.quotation.customer_note;
                        this.quotationForm.memo = response.quotation.memo;
                        this.quotationForm.tnc = response.quotation.tnc;
                        this.quotationForm.description = response.quotation.description;
                        this.quotationForm.subject = response.quotation.subject;
                        this.calculateExpiryDateDetail();
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/quotation');
                    });
            },
            addRow(){
                this.quotationForm.rows.push({
                    uuid: uuid(),
                    name: '',
                    selected_item: null,
                    show_custom_item: false,
                    custom_name: '',
                    quantity: '1',
                    unit_price: '',
                    discount: '',
                    tax: '',
                    amount: '',
                    description: ''
                });
            },
            confirmRowDelete(row){
                return dialog => this.deleteRow(row);
            },
            deleteRow(row){
                let idx = this.quotationForm.rows.indexOf(row);
                if(idx > -1)
                    this.quotationForm.rows.splice(idx, 1);
                this.calculate();
            },
            onItemTypeSelect(selectedOption){
                this.quotationForm.item_type = selectedOption.id;
                if(selectedOption.id === 'amount')
                    this.show_quantity = false;
                else
                    this.show_quantity = true;
                this.calculate();
            },
            customItemToggle(row){
                row.show_custom_item = !row.show_custom_item;
                row.name = '';
                row.custom_name = '';
                row.selected_item = {};
            },
            onCurrencySelect(selectedOption){
                this.quotationForm.currency_id = selectedOption.id;
                this.selected_currency = selectedOption;
                this.quotationForm.errors.clear('currency_id');
                this.quotationForm.rows.forEach(row => {
                    if(!row.show_custom_item){
                        let item_id = row.selected_item.id;
                        this.updateItemPrice(row,item_id,this.selected_currency.id)
                    }
                })
                this.calculate();
            },
            newReferenceNumber(){
                return helper.getConfig('quotation_random_reference_number') ? helper.randomString(10).toUpperCase() : '';
            },
            updateItemPrice(row,item_id,currency_id){
                let item = this.item_details.find( o => o.id == item_id);
                row.tax = helper.formatNumber(item.taxation.value,helper.getConfig('quotation_line_item_tax_decimal_place'));
                row.discount = helper.formatNumber(item.discount,helper.getConfig('quotation_line_item_discount_decimal_place'));
                let item_price = item.item_price.find(o => o.currency_id == this.selected_currency.id)
                row.unit_price = (item_price) ? helper.formatNumber(item_price.unit_price,this.selected_currency.decimal_place) : 0;
            },
            onItemSelect(selectedOption,id){
                let row_id = id.split('_');
                let row = this.quotationForm.rows.find( o => o.uuid === row_id[1]);

                let idx = this.quotationForm.rows.indexOf(row);
                this.quotationForm.errors.clear('item_name_'+idx);
                this.quotationForm.errors.clear('item_quantity_'+idx);
                this.quotationForm.errors.clear('item_unit_price_'+idx);
                this.quotationForm.errors.clear('item_discount_'+idx);
                this.quotationForm.errors.clear('item_tax_'+idx);
                
                this.updateItemPrice(row,selectedOption.id,this.selected_currency.id);
                this.calculate();
            },
            calculateExpiryDateDetail(){
                if(this.quotationForm.date && this.quotationForm.expiry_date){
                    let date = moment(this.quotationForm.date).format('YYYY-MM-DD')
                    this.expiry_date_detail = moment(date).add(this.quotationForm.expiry_date,'days').format('D-MM-YYYY');
                }
            },
            calculate(){
                let total = 0;
                this.quotationForm.rows.forEach(row => {
                    let discount = this.quotationForm.show_line_item_discount ? row.discount || 0 : 0;
                    discount = helper.formatNumber(discount,helper.getConfig('quotation_line_item_discount_decimal_place'));
                    row.discount = discount;
                    let tax = this.quotationForm.show_line_item_tax ? row.tax || 0 : 0;
                    tax = helper.formatNumber(tax,helper.getConfig('quotation_line_item_tax_decimal_place'));
                    row.tax = tax;
                    let quantity = this.show_quantity ? row.quantity || 1 : 1;
                    quantity = helper.formatNumber(quantity,helper.getConfig('quotation_line_item_quantity_decimal_place'));
                    row.quantity = quantity;
                    let unit_price = row.unit_price || 0;
                    unit_price = helper.formatNumber(unit_price,this.selected_currency.decimal_place);
                    row.unit_price = unit_price;
                    let price = quantity * unit_price;
                    price = price - (this.quotationForm.line_item_discount_type ? (price * discount/100) : discount);
                    price = price + (price * tax/100);
                    let amount = helper.formatNumber(price,this.selected_currency.decimal_place);
                    row.amount = amount;
                    total = total + amount;
                });
                this.quotationForm.subtotal = helper.formatNumber(total);
                let subtotal_discount = this.quotationForm.show_sub_total_discount ? this.quotationForm.subtotal_discount || 0 : 0;
                subtotal_discount = helper.formatNumber(subtotal_discount,helper.getConfig('quotation_subtotal_discount_decimal_place'));
                this.quotationForm.subtotal_discount = subtotal_discount;
                let subtotal_tax = this.quotationForm.show_sub_total_tax ? this.quotationForm.subtotal_tax || 0 : 0;
                subtotal_tax = helper.formatNumber(subtotal_tax,helper.getConfig('quotation_subtotal_tax_decimal_place'));
                this.quotationForm.subtotal_tax = subtotal_tax;
                let subtotal_shipping_and_handling = this.quotationForm.show_sub_total_shipping_and_handling ? this.quotationForm.subtotal_shipping_and_handling || 0 : 0;
                subtotal_shipping_and_handling = helper.formatNumber(subtotal_shipping_and_handling,helper.getConfig('quotation_subtotal_shipping_and_handling_decimal_place'));
                this.quotationForm.subtotal_shipping_and_handling = subtotal_shipping_and_handling;
                total = total - (this.quotationForm.subtotal_discount_type ? (total * subtotal_discount/100) : subtotal_discount);
                total = total + (total * subtotal_tax/100);
                total = total + parseFloat(subtotal_shipping_and_handling);
                this.quotationForm.total = helper.formatNumber(total,this.selected_currency.decimal_place);
            },
            save(){
                this.quotationForm.is_draft = 1;
                this.submit();
            },
            send(){
                this.quotationForm.is_draft = 0;
                this.submit();
            },
            submit(){
                if(this.quotationForm.date)
                    this.quotationForm.date = moment(this.quotationForm.date).format('YYYY-MM-DD');
                if(this.quotationForm.expiry_date_detail)
                    this.quotationForm.expiry_date_detail = moment(this.quotationForm.expiry_date_detail).format('YYYY-MM-DD');
                let url = '/api/quotation';
                if(this.uuid)
                    url = '/api/quotation/'+this.uuid;
                this.quotationForm.post(url)
                    .then(response => {
                        toastr.success(response.message);
                        if(!response.quotation.is_draft)
                            this.$router.push('/quotation/'+response.quotation.uuid);
                        else if(!this.uuid)
                            this.$router.push('/quotation/'+response.quotation.uuid+'/edit');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            onCustomerSelect(selectedOption){
                this.quotationForm.customer_id = selectedOption.id;
            },
            onExpiryDateSelect(selectedOption){
                this.quotationForm.expiry_date = selectedOption.id;
                this.calculateExpiryDateDetail();
            }
        },
        watch: {
            '$route.params.uuid'(newVal, oldVal){
                this.getPreRequisites();
                this.getQuotationDetail();
            },
            defaultCurrency(val){
                if(!val){
                    toastr.error(i18n.currency.no_default_currency_found);
                    this.$router.push('/quotation');
                }
            }
        }
    }
</script>

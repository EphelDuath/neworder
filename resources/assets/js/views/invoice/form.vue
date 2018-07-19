<template>
    <div>
        <div class="pull-right">
            <router-link v-if="uuid" class="btn btn-success" :to="`/invoice/${uuid}`">{{trans('invoice.view_invoice')}}</router-link>
            <button type="button" class="btn btn-info" @click.prevent="save">{{trans('invoice.save')}}</button>
            <button type="submit" class="btn btn-success" @click.prevent="send" v-if="invoiceForm.rows.length && !uuid">{{trans('invoice.send')}}</button>
            <router-link to="/invoice" class="btn btn-danger">{{trans('invoice.cancel_button')}}</router-link>
        </div>
        <h4 class="card-title" v-if="!uuid">{{trans('invoice.create_invoice')}}</h4>
        <h4 class="card-title" v-else>{{trans('invoice.edit_invoice')}}</h4>
        <form @keydown="invoiceForm.errors.clear($event.target.name)">
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="">{{trans('user.customer')}}</label>
                        <v-select label="name" v-model="selected_customer" name="customer_id" id="customer_id" :options="customers" :placeholder="trans('user.select_customer')" @select="onCustomerSelect" @close="invoiceForm.errors.clear('customer_id')" @remove="invoiceForm.customer_id = null"></v-select>
                        <show-error :form-name="invoiceForm" prop-name="customer_id"></show-error>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">{{trans('invoice.item_type')}}</label>
                                <v-select label="name" v-model="selected_item_type" name="item_type" id="item_type" :options="item_types" :placeholder="trans('invoice.select_item_type')" @select="onItemTypeSelect" @close="invoiceForm.errors.clear('item_type')" @remove="invoiceForm.item_type = ''"></v-select>
                                <show-error :form-name="invoiceForm" prop-name="item_type"></show-error>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">{{trans('currency.currency')}}</label>
                                <v-select label="name" v-model="selected_currency" name="currency_id" id="currency_id" :options="currencies" :placeholder="trans('currency.select_currency')" @select="onCurrencySelect" @close="invoiceForm.errors.clear('currency_id')" @remove="invoiceForm.currency_id = ''"></v-select>
                                <show-error :form-name="invoiceForm" prop-name="currency_id"></show-error>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group m-t-20 row">
                        <label for="" class="col-4 col-form-label text-right">{{trans('invoice.invoice_number')}}</label>
                        <div class="col-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <input class="form-control" type="text" value="" v-model="invoiceForm.invoice_prefix" name="invoice_prefix" :placeholder="trans('transaction.invoice_prefix')">
                                    <show-error :form-name="invoiceForm" prop-name="invoice_prefix"></show-error>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" value="" v-model="invoiceForm.invoice_number" name="invoice_number" :placeholder="trans('transaction.invoice_number')">
                                    <show-error :form-name="invoiceForm" prop-name="invoice_number"></show-error>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-4 col-form-label text-right">{{trans('invoice.reference_number')}}</label>
                        <div class="col-8">
                            <input class="form-control" type="text" value="" v-model="invoiceForm.reference_number" name="reference_number" :placeholder="trans('transaction.reference_number')">
                            <show-error :form-name="invoiceForm" prop-name="reference_number"></show-error>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-4 col-form-label text-right">{{trans('invoice.date')}}</label>
                        <div class="col-8">
                            <datepicker v-model="invoiceForm.date" :bootstrapStyling="true" @selected="invoiceForm.errors.clear('date')" :placeholder="trans('transaction.date')" @input="calculateDueDateDetail"></datepicker>
                            <show-error :form-name="invoiceForm" prop-name="date"></show-error>
                        </div>
                    </div>
                    <div class="form-group row" v-if="invoiceForm.date">
                        <label for="" class="col-4 col-form-label text-right">{{trans('invoice.due_date')}}</label>
                        <div class="col-8">
                            <v-select label="name" v-model="selected_due_date" name="due_date" id="due_date" :options="due_dates" :placeholder="trans('invoice.select_due_date')" @select="onDueDateSelect" @close="invoiceForm.errors.clear('due_date')" @remove="invoiceForm.due_date = ''"></v-select>
                            <show-error :form-name="invoiceForm" prop-name="due_date"></show-error>
                        </div>
                    </div>
                    <div class="form-group row" v-if="invoiceForm.date && selected_due_date && selected_due_date.id && invoiceForm.due_date != -1">
                        <label for="" class="col-4 col-form-label text-right">{{trans('invoice.due_date')}}</label>
                        <div class="col-8">
                            <div v-if="invoiceForm.due_date === 'due_on_date'">
                                <datepicker v-model="invoiceForm.due_date_detail" :bootstrapStyling="true" name="due_date_detail" @selected="invoiceForm.errors.clear('due_date_detail')" :placeholder="trans('transaction.due_date')"></datepicker>
                                <show-error :form-name="invoiceForm" prop-name="due_date_detail"></show-error>
                            </div>
                            <div v-if="invoiceForm.due_date !== 'due_on_date'">
                                <label class="col-form-label">{{due_date_detail}}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive m-t-20" v-if="selected_item_type">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td class="text-strong">
                                <div class="dropdown m-b-10">
                                  <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-toggle="dropdown" id="invoice-dropdown"  aria-haspopup="true" aria-expanded="true">
                                    {{trans('invoice.option')}}
                                    <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu p-10" role="menu" aria-labelledby="invoice-dropdown">
                                    <li class="dropdown-header">{{trans('invoice.add_to_line_item')}}</li>
                                    <li><div class="checkbox"><input name="line_item_tax" type="checkbox" value="1" id="line_item_tax" v-model="invoiceForm.show_line_item_tax" @change="calculate"> <label for="line_item_tax">{{trans('invoice.tax')}}</label></div></li>
                                    <li><div class="checkbox"><input name="line_item_discount" type="checkbox" value="1" id="line_item_discount" v-model="invoiceForm.show_line_item_discount" @change="calculate"> <label for="line_item_discount">{{trans('invoice.discount')}}</label></div></li>
                                    <li><div class="checkbox"><input name="line_item_description" type="checkbox" value="1" id="line_item_description" v-model="invoiceForm.show_line_item_description"> <label for="line_item_description">{{trans('invoice.description')}}</label></div></li>
                                    <li role="separator" class="divider"></li>
                                    <li class="dropdown-header">{{trans('invoice.add_to_subtotal')}}</li>
                                    <li><div class="checkbox"><input name="subtotal_tax" type="checkbox" value="1" id="subtotal_tax" v-model="invoiceForm.show_subtotal_tax" @change="calculate"> <label for="subtotal_tax">{{trans('invoice.tax')}}</label></div></li>
                                    <li><div class="checkbox"><input name="subtotal_discount" type="checkbox" value="1" id="subtotal_discount" v-model="invoiceForm.show_subtotal_discount" @change="calculate"> <label for="subtotal_discount">{{trans('invoice.discount')}}</label></div></li>
                                    <li><div class="checkbox"><input name="subtotal_shipping_and_handling" type="checkbox" value="1" id="subtotal_shipping_and_handling" v-model="invoiceForm.show_subtotal_shipping_and_handling" @change="calculate"> <label for="subtotal_shipping_and_handling">{{trans('invoice.shipping_and_handling')}}</label></div></li>
                                  </ul>
                                </div>
                                <button class="btn btn-info btn-sm" @click="addRow" type="button">{{trans('invoice.add_row')}}</button>
                            </td>
                            <td class="text-strong">{{trans('invoice.item')}}</td>
                            <td class="text-strong text-right" v-show="show_quantity">{{trans('invoice.quantity_type',{type: trans('invoice.'+selected_item_type.id) })}}</td>
                            <td class="text-strong text-right">{{trans('invoice.unit_price_type',{type: trans('invoice.'+(selected_item_type.id === 'hour' ? 'rate' : 'unit_price'))})}} ({{selected_currency.symbol}})</td>
                            <td class="text-strong text-right" v-show="invoiceForm.show_line_item_discount">
                                {{trans('invoice.discount')}}
                                <span v-if="invoiceForm.line_item_discount_type">(%)</span>
                                <span v-else>({{selected_currency.symbol}})</span>
                                <div class="checkbox pull-right"><input name="line_item_discount_type" type="checkbox" value="1" id="line_item_discount_type" v-model="invoiceForm.line_item_discount_type" @change="calculate"> <label for="line_item_discount_type">{{trans('invoice.percent')}}</label></div>
                            </td>
                            <td class="text-strong text-right" v-show="invoiceForm.show_line_item_tax">{{trans('invoice.tax')}} (%)</td>
                            <td class="text-strong text-right" style="width:150px;">{{trans('invoice.amount')}} ({{selected_currency.symbol}})</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row,index) in invoiceForm.rows">
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-danger btn-sm" type="button" :key="row.uuid" v-confirm="{ok: confirmRowDelete(row)}" v-tooltip="trans('invoice.delete_row')"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-info btn-sm" type="button" @click="customItemToggle(row)">
                                        <i class="fas fa-text-height" v-if="!row.show_custom_item" v-tooltip="trans('invoice.show_custom_item_box')"></i>
                                        <i class="fas fa-th" v-else v-tooltip="trans('invoice.show_item_list')"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <v-select label="name" v-model="row.selected_item" :name="'item_name_'+index" :id="'item_'+row.uuid" :options="items" :placeholder="trans('item.select_item')" @close="row.name = row.selected_item.name" @remove="row.name = ''" v-if="!row.show_custom_item" @select="onItemSelect"></v-select>
                                <input class="form-control invoice-input-left" :name="'item_name_'+index" v-model="row.custom_name" :placeholder="trans('invoice.custom_name')" v-if="row.show_custom_item">
                                <show-error :form-name="invoiceForm" :prop-name="'item_name_'+index"></show-error>
                                <autosize-textarea v-if="invoiceForm.show_line_item_description" row="1" class="form-control invoice-input-left" v-model="row.description" :placeholder="trans('invoice.description')" name="description"></autosize-textarea>
                            </td>
                            <td v-show="show_quantity">
                                <input class="form-control invoice-input" :name="'item_quantity_'+index" v-model="row.quantity" :placeholder="trans('invoice.quantity')" @change="calculate">
                                <show-error :form-name="invoiceForm" :prop-name="'item_quantity_'+index"></show-error>
                            </td>
                            <td>
                                <input class="form-control invoice-input" :name="'item_unit_price_'+index" v-model="row.unit_price" :placeholder="trans('invoice.unit_price_type',{type: trans('invoice.'+(selected_item_type.id === 'hour' ? 'rate' : 'unit_price'))})" @change="calculate">
                                <show-error :form-name="invoiceForm" :prop-name="'item_unit_price_'+index"></show-error>
                            </td>
                            <td v-show="invoiceForm.show_line_item_discount">
                                <input class="form-control invoice-input" :name="'item_discount_'+index" v-model="row.discount" :placeholder="trans('invoice.discount')" @change="calculate">
                                <show-error :form-name="invoiceForm" :prop-name="'item_discount_'+index"></show-error>
                            </td>
                            <td v-show="invoiceForm.show_line_item_tax">
                                <input class="form-control invoice-input" :name="'item_tax_'+index" v-model="row.tax" :placeholder="trans('invoice.tax')" @change="calculate">
                                <show-error :form-name="invoiceForm" :prop-name="'item_tax_'+index"></show-error>
                            </td>
                            <td class="text-right">
                                <span class="m-r-10">{{row.amount}}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pull-right" style="margin-top: -17px;" v-if="invoiceForm.rows.length">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="text-strong">{{trans('invoice.subtotal')}} ({{selected_currency.symbol}})</td>
                                <td style="width:150px;" class="text-right"><span class="m-r-10">{{invoiceForm.subtotal}}</span></td>
                            </tr>
                            <tr v-show="invoiceForm.show_subtotal_discount">
                                <td class="text-strong">
                                    {{trans('invoice.subtotal_discount')}}
                                    <span v-if="invoiceForm.subtotal_discount_type">(%)</span>
                                    <span v-else>({{selected_currency.symbol}})</span>
                                    <div class="checkbox"><input name="subtotal_discount_type" type="checkbox" value="1" id="subtotal_discount_type" v-model="invoiceForm.subtotal_discount_type" @change="calculate"> <label for="subtotal_discount_type">{{trans('invoice.percent')}}</label></div>
                                </td>
                                <td>
                                    <input class="form-control invoice-input" name="subtotal_discount" v-model="invoiceForm.subtotal_discount" :placeholder="trans('invoice.subtotal_discount')" @change="calculate">
                                    <show-error :form-name="invoiceForm" prop-name="subtotal_discount"></show-error>
                                </td>
                            </tr>
                            <tr v-show="invoiceForm.show_subtotal_tax">
                                <td class="text-strong">{{trans('invoice.subtotal_tax')}} (%)</td>
                                <td>
                                    <input class="form-control invoice-input" name="subtotal_tax" v-model="invoiceForm.subtotal_tax" :placeholder="trans('invoice.subtotal_tax')" @change="calculate">
                                    <show-error :form-name="invoiceForm" prop-name="subtotal_tax"></show-error>
                                </td>
                            </tr>
                            <tr v-show="invoiceForm.show_subtotal_shipping_and_handling">
                                <td class="text-strong">{{trans('invoice.subtotal_shipping_and_handling')}}</td>
                                <td>
                                    <input class="form-control invoice-input" name="subtotal_shipping_and_handling" v-model="invoiceForm.subtotal_shipping_and_handling" :placeholder="trans('invoice.subtotal_shipping_and_handling')" @change="calculate">
                                    <show-error :form-name="invoiceForm" prop-name="subtotal_shipping_and_handling"></show-error>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-strong">{{trans('invoice.total')}} ({{selected_currency.symbol}})</td>
                                <td class="text-right"><span class="m-r-10">
                                    <span v-if="invoiceForm.total < 0" class="text-strong has-error">
                                        {{invoiceForm.total}}
                                    </span>
                                    <span v-else>
                                        {{invoiceForm.total}}
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
                        <label for="">{{trans('invoice.tnc')}}</label>
                        <autosize-textarea v-model="invoiceForm.tnc" rows="2" name="tnc" :placeholder="trans('invoice.tnc')"></autosize-textarea>
                        <show-error :form-name="invoiceForm" prop-name="tnc"></show-error>
                    </div>
                    <div class="form-group">
                        <file-upload-input :button-text="trans('general.upload_document')" :token="invoiceForm.upload_token" module="invoice" :clear-file="clear_invoice_attachment" :module-id="invoiceForm.id"></file-upload-input>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="">{{trans('invoice.customer_note')}}</label>
                        <autosize-textarea v-model="invoiceForm.customer_note" rows="2" name="customer_note" :placeholder="trans('invoice.customer_note')"></autosize-textarea>
                        <show-error :form-name="invoiceForm" prop-name="customer_note"></show-error>
                    </div>
                    <div class="form-group">
                        <label for="">{{trans('invoice.memo')}}</label>
                        <autosize-textarea v-model="invoiceForm.memo" rows="2" name="memo" :placeholder="trans('invoice.memo')"></autosize-textarea>
                        <show-error :form-name="invoiceForm" prop-name="memo"></show-error>
                    </div>
                </div>
            </div>
            <div class="form-group pull-right">
                <button type="button" class="btn btn-info" @click.prevent="save">{{trans('invoice.save')}}</button>
                <button type="submit" class="btn btn-success" @click.prevent="send" v-if="invoiceForm.rows.length && !uuid">{{trans('invoice.send')}}</button>
                <router-link to="/invoice" class="btn btn-danger">{{trans('invoice.cancel_button')}}</router-link>
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

    export default {
        components : { uuid,datepicker,vSelect,fileUploadInput,currencyInput,autosizeTextarea },
        props: ['uuid'],
        data() {
            return {
                invoiceForm: new Form({
                    id: '',
                    customer_id: null,
                    currency_id: null,
                    date: '',
                    due_date: '',
                    due_date_detail: '',
                    upload_token: '',
                    item_type: '',
                    rows: [],
                    subtotal: 0,
                    subtotal_discount: 0,
                    line_item_discount_type: true,
                    subtotal_discount_type: false,
                    subtotal_tax: 0,
                    subtotal_shipping_and_handling: 0,
                    total: 0,
                    reference_number: this.newReferenceNumber(),
                    invoice_number: '',
                    invoice_prefix: '',
                    is_draft: 0,
                    show_subtotal_tax: true,
                    show_subtotal_discount: true,
                    show_subtotal_shipping_and_handling: true,
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
                due_dates: [],
                selected_due_date: null,
                due_date_detail: '',
                clear_invoice_attachment: false,
                show_quantity: true,
            };
        },
        mounted(){
            if(!this.uuid) {
                this.invoiceForm.upload_token = uuid();
            }

            this.getPreRequisites();

            if(this.uuid)
                this.getInvoiceDetail();
        },
        methods: {
            getPreRequisites(){
                axios.get('/api/invoice/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.currencies = response.currencies;
                    this.customers = response.customers;
                    this.default_currency = response.default_currency;
                    this.item_types = response.item_types;
                    if(!this.uuid){
                        this.selected_currency = this.default_currency;
                        this.invoiceForm.currency_id = this.default_currency.id;
                        this.selected_item_type = response.default_item_type;
                        this.invoiceForm.item_type = 'quantity';
                        this.invoiceForm.invoice_number = helper.formatWithPadding(response.new_invoice_number,helper.getConfig('invoice_number_digit'));
                        this.invoiceForm.invoice_prefix = helper.getConfig('invoice_prefix');
                    }
                    this.items = response.items;
                    this.item_details = response.item_details;
                    this.due_dates = response.due_dates;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
                this.addRow();
                if(!this.uuid){
                    this.invoiceForm.show_subtotal_tax = helper.getConfig('invoice_default_subtotal_tax');
                    this.invoiceForm.show_subtotal_discount = helper.getConfig('invoice_default_subtotal_discount');
                    this.invoiceForm.show_subtotal_shipping_and_handling = helper.getConfig('invoice_default_subtotal_shipping_and_handling');
                    this.invoiceForm.show_line_item_tax = helper.getConfig('invoice_default_line_item_tax');
                    this.invoiceForm.show_line_item_discount = helper.getConfig('invoice_default_line_item_discount');
                    this.invoiceForm.show_line_item_description = helper.getConfig('invoice_default_line_item_description');
                }
            },
            getInvoiceDetail(){
                axios.get('/api/invoice/'+this.uuid+'/detail')
                    .then(response => response.data)
                    .then(response => {
                        this.invoiceForm.id = response.invoice.id;
                        this.selected_currency = response.selected_currency;
                        this.invoiceForm.currency_id = response.invoice.currency_id;
                        this.selected_customer = response.selected_customer;
                        this.invoiceForm.customer_id = response.invoice.customer_id;
                        this.selected_item_type = response.selected_item_type;
                        this.invoiceForm.item_type = response.invoice.item_type;
                        this.selected_due_date = response.selected_due_date;
                        this.invoiceForm.date = response.invoice.date;
                        this.invoiceForm.due_date = response.invoice.due_date;
                        this.invoiceForm.due_date_detail = response.invoice.due_date_detail;
                        this.invoiceForm.upload_token = response.invoice.upload_token;
                        this.invoiceForm.rows = response.rows;
                        this.invoiceForm.subtotal = helper.formatNumber(response.invoice.subtotal,this.selected_currency.decimal_place);
                        this.invoiceForm.subtotal_discount = helper.formatNumber(response.invoice.subtotal_discount_amount,this.selected_currency.decimal_place);
                        this.invoiceForm.line_item_discount_type = response.invoice.line_item_discount_type;
                        this.invoiceForm.subtotal_discount_type = response.invoice.subtotal_discount_type;
                        this.invoiceForm.subtotal_tax = helper.formatNumber(response.invoice.subtotal_tax_amount,this.selected_currency.decimal_place);
                        this.invoiceForm.subtotal_shipping_and_handling = helper.formatNumber(response.invoice.subtotal_shipping_and_handling_amount,this.selected_currency.decimal_place);
                        this.invoiceForm.total = helper.formatNumber(response.invoice.total,this.selected_currency.decimal_place);
                        this.invoiceForm.reference_number = response.invoice.reference_number;
                        this.invoiceForm.invoice_number = helper.formatWithPadding(response.invoice.number,helper.getConfig('invoice_number_digit'));
                        this.invoiceForm.invoice_prefix = response.invoice.prefix;
                        this.invoiceForm.is_draft = response.invoice.is_draft;
                        this.invoiceForm.show_subtotal_tax = response.invoice.subtotal_tax;
                        this.invoiceForm.show_subtotal_discount = response.invoice.subtotal_discount;
                        this.invoiceForm.show_subtotal_shipping_and_handling = response.invoice.subtotal_shipping_and_handling;
                        this.invoiceForm.show_line_item_tax = response.invoice.line_item_tax;
                        this.invoiceForm.show_line_item_discount = response.invoice.line_item_discount;
                        this.invoiceForm.show_line_item_description = response.invoice.line_item_description;
                        this.invoiceForm.customer_note = response.invoice.customer_note;
                        this.invoiceForm.memo = response.invoice.memo;
                        this.invoiceForm.tnc = response.invoice.tnc;
                        this.calculateDueDateDetail();
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/invoice');
                    });
            },
            addRow(){
                this.invoiceForm.rows.push({
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
                let idx = this.invoiceForm.rows.indexOf(row);
                if(idx > -1)
                    this.invoiceForm.rows.splice(idx, 1);
                this.calculate();
            },
            onItemTypeSelect(selectedOption){
                this.invoiceForm.item_type = selectedOption.id;
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
                this.invoiceForm.currency_id = selectedOption.id;
                this.selected_currency = selectedOption;
                this.invoiceForm.errors.clear('currency_id');
                this.invoiceForm.rows.forEach(row => {
                    if(!row.show_custom_item){
                        let item_id = row.selected_item.id;
                        this.updateItemPrice(row,item_id,this.selected_currency.id)
                    }
                })
                this.calculate();
            },
            newReferenceNumber(){
                return helper.getConfig('invoice_random_reference_number') ? helper.randomString(10).toUpperCase() : '';
            },
            updateItemPrice(row,item_id,currency_id){
                let item = this.item_details.find( o => o.id == item_id);

                if (!item) return;

                row.tax = helper.formatNumber(item.taxation.value,helper.getConfig('invoice_line_item_tax_decimal_place'));
                row.discount = helper.formatNumber(item.discount,helper.getConfig('invoice_line_item_discount_decimal_place'));
                let item_price = item.item_price.find(o => o.currency_id === this.selected_currency.id)
                row.unit_price = (item_price) ? helper.formatNumber(item_price.unit_price,this.selected_currency.decimal_place) : 0;
            },
            onItemSelect(selectedOption,id){
                let row_id = id.split('_');
                let row = this.invoiceForm.rows.find( o => o.uuid === row_id[1]);

                let idx = this.invoiceForm.rows.indexOf(row);
                this.invoiceForm.errors.clear('item_name_'+idx);
                this.invoiceForm.errors.clear('item_quantity_'+idx);
                this.invoiceForm.errors.clear('item_unit_price_'+idx);
                this.invoiceForm.errors.clear('item_discount_'+idx);
                this.invoiceForm.errors.clear('item_tax_'+idx);

                this.updateItemPrice(row,selectedOption.id,this.selected_currency.id);
                this.calculate();
            },
            calculateDueDateDetail(){
                if(this.invoiceForm.date && this.invoiceForm.due_date){
                    let date = moment(this.invoiceForm.date).format('YYYY-MM-DD')
                    this.due_date_detail = moment(date).add(this.invoiceForm.due_date,'days').format('D-MM-YYYY');
                }
            },
            calculate(){
                let total = 0;
                this.invoiceForm.rows.forEach(row => {
                    let discount = this.invoiceForm.show_line_item_discount ? row.discount || 0 : 0;
                    discount = helper.formatNumber(discount,helper.getConfig('invoice_line_item_discount_decimal_place'));
                    row.discount = discount;
                    let tax = this.invoiceForm.show_line_item_tax ? row.tax || 0 : 0;
                    tax = helper.formatNumber(tax,helper.getConfig('invoice_line_item_tax_decimal_place'));
                    row.tax = tax;
                    let quantity = this.show_quantity ? row.quantity || 1 : 1;
                    quantity = helper.formatNumber(quantity,helper.getConfig('invoice_line_item_quantity_decimal_place'));
                    row.quantity = quantity;
                    let unit_price = row.unit_price || 0;
                    unit_price = helper.formatNumber(unit_price,this.selected_currency.decimal_place);
                    row.unit_price = unit_price;
                    let price = quantity * unit_price;
                    price = price - (this.invoiceForm.line_item_discount_type ? (price * discount/100) : discount);
                    price = price + (price * tax/100);
                    let amount = helper.formatNumber(price,this.selected_currency.decimal_place);
                    row.amount = amount;
                    total = total + amount;
                });
                this.invoiceForm.subtotal = helper.formatNumber(total);
                let subtotal_discount = this.invoiceForm.show_subtotal_discount ? this.invoiceForm.subtotal_discount || 0 : 0;
                subtotal_discount = helper.formatNumber(subtotal_discount,helper.getConfig('invoice_subtotal_discount_decimal_place'));
                this.invoiceForm.subtotal_discount = subtotal_discount;
                let subtotal_tax = this.invoiceForm.show_subtotal_tax ? this.invoiceForm.subtotal_tax || 0 : 0;
                subtotal_tax = helper.formatNumber(subtotal_tax,helper.getConfig('invoice_subtotal_tax_decimal_place'));
                this.invoiceForm.subtotal_tax = subtotal_tax;
                let subtotal_shipping_and_handling = this.invoiceForm.show_subtotal_shipping_and_handling ? this.invoiceForm.subtotal_shipping_and_handling || 0 : 0;
                subtotal_shipping_and_handling = helper.formatNumber(subtotal_shipping_and_handling,helper.getConfig('invoice_subtotal_shipping_and_handling_decimal_place'));
                this.invoiceForm.subtotal_shipping_and_handling = subtotal_shipping_and_handling;
                total = total - (this.invoiceForm.subtotal_discount_type ? (total * subtotal_discount/100) : subtotal_discount);
                total = total + (total * subtotal_tax/100);
                total = total + parseFloat(subtotal_shipping_and_handling);
                this.invoiceForm.total = helper.formatNumber(total,this.selected_currency.decimal_place);
            },
            save(){
                this.invoiceForm.is_draft = 1;
                this.submit();
            },
            send(){
                this.invoiceForm.is_draft = 0;
                this.submit();
            },
            submit(){
                if(this.invoiceForm.date)
                    this.invoiceForm.date = moment(this.invoiceForm.date).format('YYYY-MM-DD');
                if(this.invoiceForm.due_date_detail)
                    this.invoiceForm.due_date_detail = moment(this.invoiceForm.due_date_detail).format('YYYY-MM-DD');
                let url = '/api/invoice';
                if(this.uuid)
                    url = '/api/invoice/'+this.uuid;
                this.invoiceForm.post(url)
                    .then(response => {
                        toastr.success(response.message);
                        if(!response.invoice.is_draft)
                            this.$router.push('/invoice/'+response.invoice.uuid);
                        else if(!this.uuid)
                            this.$router.push('/invoice/'+response.invoice.uuid+'/edit');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            onCustomerSelect(selectedOption){
                this.invoiceForm.customer_id = selectedOption.id;
            },
            onDueDateSelect(selectedOption){
                this.invoiceForm.due_date = selectedOption.id;
                this.calculateDueDateDetail();
            }
        },
        watch: {
            '$route.params.uuid'(newVal, oldVal){
                this.getPreRequisites();
                this.getInvoiceDetail();
            },
            defaultCurrency(val){
                if(!val){
                    toastr.error(i18n.currency.no_default_currency_found);
                    this.$router.push('/invoice');
                }
            }
        }
    }
</script>

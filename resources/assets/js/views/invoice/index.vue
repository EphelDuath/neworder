<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('invoice.invoice')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('invoice.invoice')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <transition name="fade">
                    <div class="card" v-if="showFilterPanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showFilterPanel" @click="showFilterPanel = !showFilterPanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('general.filter')}}</h4>
                            <div class="row">
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('invoice.prefix')}}</label>
                                        <input class="form-control" name="prefix" v-model="filterInvoiceForm.prefix">
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('invoice.invoice_number')}}</label>
                                        <input class="form-control" name="number" v-model="filterInvoiceForm.number">
                                    </div>
                                </div>
                                <div class="col-3 col-md-3" v-if="!hasRole('customer')">
                                    <div class="form-group">
                                        <label for="">{{trans('user.customer')}}</label>
                                        <v-select label="name" track-by="id" v-model="selected_customer" name="customer_id" id="customer_id" :options="customers" :placeholder="trans('user.select_customer')" @select="onCustomerSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onCustomerRemove">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('invoice.status')}}</label>
                                        <select name="status" class="form-control" v-model="filterInvoiceForm.status" :placeholder="trans('general.select_one')">
                                            <option value="">{{trans('general.select_one')}}</option>
                                            <option value="draft">{{trans('invoice.status_draft')}}</option>
                                            <option value="unpaid">{{trans('invoice.status_unpaid')}}</option>
                                            <option value="partially_paid">{{trans('invoice.status_partially_paid')}}</option>
                                            <option value="paid">{{trans('invoice.status_paid')}}</option>
                                            <option value="overdue">{{trans('invoice.status_overdue')}}</option>
                                            <option value="cancelled">{{trans('invoice.status_cancelled')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <date-range-picker :start-date.sync="filterInvoiceForm.date_start_date" :end-date.sync="filterInvoiceForm.date_end_date" :label="trans('invoice.date')"></date-range-picker>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <date-range-picker :start-date.sync="filterInvoiceForm.due_date_start_date" :end-date.sync="filterInvoiceForm.due_date_end_date" :label="trans('invoice.due_date')"></date-range-picker>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="sort_by" class="form-control" v-model="filterInvoiceForm.sort_by">
                                            <option value="number">{{trans('invoice.invoice_number')}}</option>
                                            <option value="date">{{trans('invoice.date')}}</option>
                                            <option value="due_date_detail">{{trans('invoice.due_date')}}</option>
                                            <option value="total">{{trans('invoice.amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterInvoiceForm.order">
                                            <option value="asc">{{trans('general.ascending')}}</option>
                                            <option value="desc">{{trans('general.descending')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12">
                                <router-link v-if="invoices.total && hasPermission('create-invoice')" to="/invoice/create" class="btn btn-sm btn-info pull-right"><i class="fas fa-plus"></i> {{trans('invoice.create_invoice')}}</router-link>
                                <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                                <h4 class="card-title">{{trans('invoice.invoice_list')}}</h4>
                                <h6 class="card-subtitle" v-if="invoices">{{trans('general.total_result_found',{'count' : invoices.total})}}</h6>
                                <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                <div class="table-responsive" v-if="invoices.total">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{trans('invoice.invoice_number')}}</th>
                                                <th>{{trans('user.customer')}}</th>
                                                <th>{{trans('invoice.amount')}}</th>
                                                <th>{{trans('invoice.date')}}</th>
                                                <th>{{trans('invoice.due_date')}}</th>
                                                <th>{{trans('invoice.status')}}</th>
                                                <th class="table-option">{{trans('general.action')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="invoice in invoices.data">
                                                <td v-text="getInvoiceNumber(invoice)"></td>
                                                <td>
                                                    <span v-if="invoice.customer">{{invoice.customer.profile.first_name+' '+invoice.customer.profile.last_name}}</span>
                                                </td>
                                                <td v-text="formatCurrency(invoice.total,invoice.currency)"></td>
                                                <td>
                                                    <span v-if="invoice.date">{{invoice.date || moment}}</span>
                                                </td>
                                                <td>
                                                    <span v-if="invoice.date">{{(invoice.due_date_detail) ? invoice.due_date_detail || moment : ''}}</span>
                                                </td>
                                                <td>
                                                    <span v-for="status in getInvoiceStatus(invoice)" :class="['label','label-'+status.color,'m-r-5']">{{status.label}}</span>
                                                </td>
                                                <td class="table-option">
                                                    <div class="btn-group">
                                                        <router-link :to="`/invoice/${invoice.uuid}`" class="btn btn-success btn-sm" v-tooltip="trans('invoice.view_invoice')"><i class="fas fa-arrow-circle-right"></i></router-link>
                                                        <button v-if="hasPermission('edit-invoice') && !invoice.is_cancelled" class="btn btn-info btn-sm" v-tooltip="trans('invoice.edit_invoice')" @click.prevent="editInvoice(invoice)"><i class="fas fa-edit"></i></button>
                                                        <button v-if="hasPermission('delete-invoice')" class="btn btn-danger btn-sm" :key="invoice.id" v-confirm="{ok: confirmDelete(invoice)}" v-tooltip="trans('invoice.delete_invoice')"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <module-info v-if="!invoices.total" module="invoice" title="module_info_title" description="module_info_description" icon="list-alt">
                                    <div slot="btn">
                                        <router-link v-if="!invoices.total && hasPermission('create-invoice')" to="/invoice/create" class="btn btn-md btn-info">{{trans('invoice.create_invoice')}}</router-link>
                                    </div>
                                </module-info>
                                <pagination-record :page-length.sync="filterInvoiceForm.page_length" :records="invoices" @updateRecords="getInvoices"></pagination-record>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import vSelect from 'vue-multiselect'
    import dateRangePicker from '../../components/date-range-picker'

    export default {
        components : { vSelect,dateRangePicker },
        data() {
            return {
                invoices: {
                    total: 0,
                    data: []
                },
                filterInvoiceForm: {
                    prefix: '',
                    number: '',
                    date_start_date: '',
                    date_end_date: '',
                    customer_id: [],
                    due_date_start_date: '',
                    due_date_end_date: '',
                    status: '',
                    sort_by: 'date',
                    order: 'desc',
                    page_length: helper.getConfig('page_length')
                },
                showFilterPanel: false,
                customers: [],
                selected_customer: ''
            };
        },
        mounted(){
            if(!helper.hasPermission('list-invoice')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            if(!this.hasRole('customer'))
            axios.get('/api/invoice/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.customers = response.customers;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });

            this.getInvoices();
        },
        methods: {
            hasRole(role){
                return helper.hasRole(role);
            },
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getInvoices(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterInvoiceForm);
                axios.get('/api/invoice?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.invoices = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editInvoice(invoice){
                this.$router.push('/invoice/'+invoice.uuid+'/edit');
            },
            confirmDelete(invoice){
                return dialog => this.deleteInvoice(invoice);
            },
            deleteInvoice(invoice){
                axios.delete('/api/invoice/'+invoice.uuid)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getInvoices();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            formatCurrency(number,currency){
                return helper.formatCurrency(number,currency);
            },
            formatNumber(number,decimal_place){
                if(decimal_place === undefined)
                    decimal_place = 2;
                return helper.formatNumber(number,decimal_place);
            },
            getInvoiceNumber(invoice){
                return invoice.prefix+''+helper.formatWithPadding(invoice.number,helper.getConfig('invoice_number_digit'));
            },
            getInvoiceStatus(invoice){
                return helper.getInvoiceStatus(invoice);
            },
            onCustomerSelect(selectedOption){
                this.filterInvoiceForm.customer_id.push(selectedOption.id);
            },
            onCustomerRemove(removedOption){
                this.filterInvoiceForm.customer_id.splice(this.filterInvoiceForm.customer_id.indexOf(removedOption.id), 1);
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          }
        },
        watch: {
            filterInvoiceForm: {
                handler(val){
                    this.getInvoices();
                },
                deep: true
            }
        }
    }
</script>

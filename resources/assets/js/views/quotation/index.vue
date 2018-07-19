<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('quotation.quotation')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('quotation.quotation')}}</li>
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
                                        <label for="">{{trans('quotation.prefix')}}</label>
                                        <input class="form-control" name="prefix" v-model="filterQuotationForm.prefix">
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('quotation.quotation_number')}}</label>
                                        <input class="form-control" name="number" v-model="filterQuotationForm.number">
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
                                        <label for="">{{trans('quotation.status')}}</label>
                                        <select name="status" class="form-control" v-model="filterQuotationForm.status" :placeholder="trans('general.select_one')">
                                            <option value="">{{trans('general.select_one')}}</option>
                                            <option value="draft">{{trans('quotation.status_draft')}}</option>
                                            <option value="sent">{{trans('quotation.status_sent')}}</option>
                                            <option value="accepted">{{trans('quotation.status_accepted')}}</option>
                                            <option value="rejected">{{trans('quotation.status_rejected')}}</option>
                                            <option value="expired">{{trans('quotation.status_expired')}}</option>
                                            <option value="invoiced">{{trans('quotation.status_invoiced')}}</option>
                                            <option value="cancelled">{{trans('quotation.status_cancelled')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <date-range-picker :start-date.sync="filterQuotationForm.date_start_date" :end-date.sync="filterQuotationForm.date_end_date" :label="trans('quotation.date')"></date-range-picker>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <date-range-picker :start-date.sync="filterQuotationForm.expiry_date_start_date" :end-date.sync="filterQuotationForm.expiry_date_end_date" :label="trans('quotation.expiry_date')"></date-range-picker>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="sort_by" class="form-control" v-model="filterQuotationForm.sort_by">
                                            <option value="number">{{trans('quotation.quotation_number')}}</option>
                                            <option value="date">{{trans('quotation.date')}}</option>
                                            <option value="expiry_date_detail">{{trans('quotation.expiry_date')}}</option>
                                            <option value="total">{{trans('quotation.amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterQuotationForm.order">
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
                                <router-link v-if="quotations.total && hasPermission('create-quotation')" to="/quotation/create" class="btn btn-sm btn-info pull-right"><i class="fas fa-plus"></i> {{trans('quotation.create_quotation')}}</router-link>
                                <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                                <h4 class="card-title">{{trans('quotation.quotation_list')}}</h4>
                                <h6 class="card-subtitle" v-if="quotations">{{trans('general.total_result_found',{'count' : quotations.total})}}</h6>
                                <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                <div class="table-responsive" v-if="quotations.total">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{trans('quotation.quotation_number')}}</th>
                                                <th>{{trans('user.customer')}}</th>
                                                <th>{{trans('quotation.amount')}}</th>
                                                <th>{{trans('quotation.date')}}</th>
                                                <th>{{trans('quotation.expiry_date')}}</th>
                                                <th>{{trans('quotation.status')}}</th>
                                                <th class="table-option">{{trans('general.action')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="quotation in quotations.data">
                                                <td v-text="getQuotationNumber(quotation)"></td>
                                                <td>
                                                    <span v-if="quotation.customer">{{quotation.customer.profile.first_name+' '+quotation.customer.profile.last_name}}</span>
                                                </td>
                                                <td v-text="formatCurrency(quotation.total,quotation.currency)"></td>
                                                <td>
                                                    <span v-if="quotation.date">{{quotation.date || moment}}</span>
                                                </td>
                                                <td>
                                                    <span v-if="quotation.date">{{(quotation.expiry_date_detail) ? quotation.expiry_date_detail || moment : ''}}</span>
                                                </td>
                                                <td>
                                                    <span v-for="status in getQuotationStatus(quotation)" :class="['label','label-'+status.color,'m-r-5']">{{status.label}}</span>
                                                </td>
                                                <td class="table-option">
                                                    <div class="btn-group">
                                                        <router-link :to="`/quotation/${quotation.uuid}`" class="btn btn-success btn-sm" v-tooltip="trans('quotation.view_quotation')"><i class="fas fa-arrow-circle-right"></i></router-link>
                                                        <button v-if="hasPermission('edit-quotation') && !quotation.is_cancelled" class="btn btn-info btn-sm" v-tooltip="trans('quotation.edit_quotation')" @click.prevent="editQuotation(quotation)"><i class="fas fa-edit"></i></button>
                                                        <button v-if="hasPermission('delete-quotation')" class="btn btn-danger btn-sm" :key="quotation.id" v-confirm="{ok: confirmDelete(quotation)}" v-tooltip="trans('quotation.delete_quotation')"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <module-info v-if="!quotations.total" module="quotation" title="module_info_title" description="module_info_description" icon="file-alt">
                                    <div slot="btn">
                                        <router-link v-if="!quotations.total && hasPermission('create-quotation')" to="/quotation/create" class="btn btn-md btn-info">{{trans('quotation.create_quotation')}}</router-link>
                                    </div>
                                </module-info>
                                <pagination-record :page-length.sync="filterQuotationForm.page_length" :records="quotations" @updateRecords="getQuotations"></pagination-record>
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
                quotations: {
                    total: 0,
                    data: []
                },
                filterQuotationForm: {
                    prefix: '',
                    number: '',
                    date_start_date: '',
                    date_end_date: '',
                    customer_id: [],
                    expiry_date_start_date: '',
                    expiry_date_end_date: '',
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
            if(!helper.hasPermission('list-quotation')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            
            if(!this.hasRole('customer'))
            axios.get('/api/quotation/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.customers = response.customers;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });

            this.getQuotations();
        },
        methods: {
            hasRole(role){
                return helper.hasRole(role);
            },
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getQuotations(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterQuotationForm);
                axios.get('/api/quotation?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.quotations = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editQuotation(quotation){
                this.$router.push('/quotation/'+quotation.uuid+'/edit');
            },
            confirmDelete(quotation){
                return dialog => this.deleteQuotation(quotation);
            },
            deleteQuotation(quotation){
                axios.delete('/api/quotation/'+quotation.uuid)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getQuotations();
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
            getQuotationNumber(quotation){
                return quotation.prefix+''+helper.formatWithPadding(quotation.number,helper.getConfig('quotation_number_digit'));
            },
            getQuotationStatus(quotation){
                return helper.getQuotationStatus(quotation);
            },
            onCustomerSelect(selectedOption){
                this.filterQuotationForm.customer_id.push(selectedOption.id);
            },
            onCustomerRemove(removedOption){
                this.filterQuotationForm.customer_id.splice(this.filterQuotationForm.customer_id.indexOf(removedOption.id), 1);
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          }
        },
        watch: {
            filterQuotationForm: {
                handler(val){
                    this.getQuotations();
                },
                deep: true
            }
        }
    }
</script>

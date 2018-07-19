<template>
    <div class="card" v-if="is_staff && showPanel">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-4 col-md-4 col-sm-4">
                    <h4 class="card-title">{{trans('invoice.recurring_invoice')}}</h4>
                    <form @submit.prevent="updateRecurrence" @keydown="recurrenceForm.errors.clear($event.target.name)">
                        <div class="form-group">
                            <switches class="m-l-20" v-model="recurrenceForm.is_recurring" theme="bootstrap" color="success"></switches> {{trans('invoice.recurring')}}
                        </div>
                        <div v-if="recurrenceForm.is_recurring">
                            <div class="form-group">
                                <label>{{trans('invoice.next_recurring_date')}}</label>
                                <span v-if="next_recurring_date">{{next_recurring_date | moment}}</span>
                                <span v-else class="has-error"><strong>-</strong></span>
                            </div>
                            <div class="form-group">
                                <label for="">{{trans('invoice.recurrence_start_date')}}</label>
                                <datepicker v-model="recurrenceForm.recurrence_start_date" :bootstrapStyling="true" @selected="recurrenceForm.errors.clear('recurrence_start_date')" :placeholder="trans('invoice.recurrence_start_date')"></datepicker>
                                <show-error :form-name="recurrenceForm" prop-name="recurrence_start_date"></show-error>
                            </div>
                            <div class="form-group">
                                <label for="">{{trans('invoice.recurrence_end_date')}}</label>
                                <datepicker v-model="recurrenceForm.recurrence_end_date" :bootstrapStyling="true" @selected="recurrenceForm.errors.clear('recurrence_end_date')" :placeholder="trans('invoice.recurrence_end_date')"></datepicker>
                                <show-error :form-name="recurrenceForm" prop-name="recurrence_end_date"></show-error>
                            </div>
                            <div class="form-group">
                                <label for="">{{trans('invoice.recurring_frequency')}}</label>
                                <v-select label="name" v-model="selected_recurring_frequency" name="recurring_frequency" id="recurring_frequency" :options="recurring_frequencies" :placeholder="trans('invoice.select_frequency')" @select="onRecurringFrequencySelect" @close="recurrenceForm.errors.clear('recurring_frequency')" @remove="recurrenceForm.recurring_frequency = ''"></v-select>
                                <show-error :form-name="recurrenceForm" prop-name="recurring_frequency"></show-error>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">{{trans('general.save')}}</button>
                    </form>
                </div>
                <div class="col-12 col-lg-8 col-md-8 col-sm-8">
                    <button class="btn btn-info btn-sm pull-right" v-if="showPanel" @click="toggle">{{trans('general.hide')}}</button>
                    <h4 class="card-title">{{trans('invoice.recurring_invoice_log')}}</h4>
                    <h6 class="card-subtitle" v-if="recurring_invoices">{{trans('general.total_result_found',{'count' : recurring_invoices.total})}}</h6>
                    <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" v-if="recurring_invoices.total">
                            <thead>
                                <tr>
                                    <th>{{trans('invoice.invoice_number')}}</th>
                                    <th>{{trans('invoice.date')}}</th>
                                    <th>{{trans('invoice.status')}}</th>
                                    <th class="table-option-sm">{{trans('general.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="recurring_invoice in recurring_invoices.data">
                                    <td v-text="getInvoiceNumber(recurring_invoice)"></td>
                                    <td>{{recurring_invoice.date | moment}}</td>
                                    <td>
                                        <span v-for="status in getInvoiceStatus(recurring_invoice)" :class="['label','label-'+status.color,'m-r-5']">{{status.label}}</span>
                                    </td>
                                    <td class="table-option-sm"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <pagination-record :page-length.sync="filterRecurringInvoiceForm.page_length" :records="recurring_invoices" @updateRecords="getRecurringInvoices" @change.native="getRecurringInvoices"></pagination-record>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import switches from 'vue-switches'
    import vSelect from 'vue-multiselect'
    import datepicker from 'vuejs-datepicker'

    export default {
        components: {switches,vSelect,datepicker},
        props: {
            uuid:{
                required: true,
            },
            showPanel:{
                required: true,
            }
        },
        data(){
            return {
                is_customer: false,
                is_staff: false,
                recurrenceForm: new Form({
                    is_recurring: false,
                    recurrence_start_date: '',
                    recurring_frequency: '',
                    recurrence_end_date: ''
                },false),
                next_recurring_date: '',
                recurring_frequencies: [],
                selected_recurring_frequency: null,
                recurring_invoices: {
                    total: 0,
                    data: []
                },
                filterRecurringInvoiceForm: {
                    page_length: helper.getConfig('page_length')
                }
            }
        },
        watch: {
            showPanel(val){
                if(val)
                    this.getRecurringInvoices();
            },
            uuid(val){
                this.getRecurringInvoices();
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
            onRecurringFrequencySelect(selectedOption){
                this.recurrenceForm.recurring_frequency = selectedOption.id;
            },
            updateRecurrence(){
                this.recurrenceForm.recurrence_start_date = moment(this.recurrenceForm.recurrence_start_date).format('YYYY-MM-DD');
                this.recurrenceForm.recurrence_end_date = moment(this.recurrenceForm.recurrence_end_date).format('YYYY-MM-DD');
                this.recurrenceForm.post('/api/invoice/'+this.uuid+'/recurring')
                    .then(response => {
                        this.getRecurringInvoices();
                        toastr.success(response.message);
                    }).catch(error => {
                        helper.showErrorMsg(error);
                    });

            },
            toggle(){
                this.$emit('toggle');
            },
            getRecurringInvoices(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterEmailLogForm);
                axios.get('/api/invoice/'+this.uuid+'/recurring?page=' + page + url)
                    .then(response => response.data)
                    .then(response => {
                        this.recurrenceForm.is_recurring = response.invoice.is_recurring;
                        this.recurrenceForm.recurrence_start_date = response.invoice.recurrence_start_date;
                        this.recurrenceForm.recurrence_end_date = response.invoice.recurrence_end_date;
                        this.recurrenceForm.recurring_frequency = response.invoice.recurring_frequency;
                        this.recurring_frequencies = response.recurring_frequencies;
                        this.selected_recurring_frequency = response.selected_recurring_frequency;
                        this.recurring_invoices = response.recurring_invoices;
                        this.next_recurring_date = response.next_recurring_date;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getInvoiceStatus(invoice){
                return helper.getInvoiceStatus(invoice);
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

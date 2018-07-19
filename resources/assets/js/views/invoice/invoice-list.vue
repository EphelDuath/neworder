<template>
    <div>
        <h6 class="card-subtitle" v-if="invoices">{{trans('general.total_result_found',{'count' : invoices.total})}}</h6>
        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
        <div class="table-responsive">
            <table class="table" v-if="invoices.total">
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
                        <td><span v-if="invoice.date">{{invoice.date || moment}}</span></td>
                        <td><span v-if="invoice.date">{{(invoice.due_date_detail) ? invoice.due_date_detail || moment : ''}}</span></td>
                        <td><span v-for="status in getInvoiceStatus(invoice)" :class="['label','label-'+status.color,'m-r-5']">{{status.label}}</span></td>
                        <td class="table-option">
                            <div class="btn-group">
                                <router-link :to="`/invoice/${invoice.uuid}`" class="btn btn-success btn-sm" v-tooltip="trans('invoice.view_invoice')"><i class="fas fa-arrow-circle-right"></i></router-link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['option'],
        data(){
            return {
                invoices: []
            }
        },
        mounted(){
            this.getInvoices(this.option);
        },
        methods: {
            getInvoices(val){
                let url = '';
                if(val === 'unpaid' || val === 'partially_paid' || val === 'overdue')
                    url = '/api/invoice?page=1&page_length=5&sort_by=created_at&order=desc&status='+val;
                else if(val === 'recurring')
                    url = '/api/invoice?page=1&page_length=5&sort_by=created_at&order=desc&is_recurring=1';
                else
                    url = '/api/invoice?page=1&page_length=5&sort_by=created_at&order=desc';
                axios.get(url)
                    .then(response => response.data)
                    .then(response => this.invoices = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getInvoiceNumber(invoice){
                return invoice.prefix+''+helper.formatWithPadding(invoice.number,helper.getConfig('invoice_number_digit'));
            },
            getInvoiceStatus(invoice){
                return helper.getInvoiceStatus(invoice);
            },
            formatCurrency(number,currency){
                return helper.formatCurrency(number,currency);
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          }
        }
    }
</script>

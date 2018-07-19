<template>
    <div>
        <h6 class="card-subtitle" v-if="quotations">{{trans('general.total_result_found',{'count' : quotations.total})}}</h6>
        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
        <div class="table-responsive">
            <table class="table" v-if="quotations.total">
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
                        <td><span v-if="quotation.date">{{quotation.date || moment}}</span></td>
                        <td><span v-if="quotation.date">{{(quotation.expiry_date_detail) ? quotation.expiry_date_detail || moment : ''}}</span></td>
                        <td><span v-for="status in getQuotationStatus(quotation)" :class="['label','label-'+status.color,'m-r-5']">{{status.label}}</span></td>
                        <td class="table-option">
                            <div class="btn-group">
                                <router-link :to="`/quotation/${quotation.uuid}`" class="btn btn-success btn-sm" v-tooltip="trans('quotation.view_quotation')"><i class="fas fa-arrow-circle-right"></i></router-link>
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
                quotations: []
            }
        },
        mounted(){
            this.getQuotations(this.option);
        },
        methods: {
            getQuotations(val){
                let url = '';
                if(val === 'accepted' || val === 'rejected' || val === 'expired' || val === 'invoiced')
                    url = '/api/quotation?page=1&page_length=5&sort_by=created_at&order=desc&status='+val;
                else
                    url = '/api/quotation?page=1&page_length=5&sort_by=created_at&order=desc';
                axios.get(url)
                    .then(response => response.data)
                    .then(response => this.quotations = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getQuotationNumber(quotation){
                return quotation.prefix+''+helper.formatWithPadding(quotation.number,helper.getConfig('quotation_number_digit'));
            },
            getQuotationStatus(quotation){
                return helper.getQuotationStatus(quotation);
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

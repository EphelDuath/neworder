<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('currency.currency')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="currency"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">
                                <show-tip module="currency" tip="tip_currency_name" ></show-tip> 
                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('currency.add_new_currency')}}</h4>
                                        <currency-form @completed="getCurrencies"></currency-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('currency.currency_list')}}</h4>
                                        <h6 class="card-subtitle" v-if="currencies">{{trans('general.total_result_found',{'count' : currencies.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive" v-if="currencies.total">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('currency.name')}}</th>
                                                        <th>{{trans('currency.symbol')}}</th>
                                                        <th>{{trans('currency.position')}}</th>
                                                        <th>{{trans('currency.decimal_place')}}</th>
                                                        <th>{{trans('currency.default')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="currency in currencies.data">
                                                        <td v-text="currency.name"></td>
                                                        <td v-text="currency.symbol"></td>
                                                        <td>{{trans('currency.'+currency.position)}}</td>
                                                        <td v-text="currency.decimal_place"></td>
                                                        <td v-html="getDefaultStatus(currency)"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('currency.edit_currency')" @click.prevent="editCurrency(currency)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="currency.id" v-confirm="{ok: confirmDelete(currency)}" v-tooltip="trans('currency.delete_currency')"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!currencies.total" module="currency" title="module_info_title" description="module_info_description" icon="money"></module-info>
                                        <pagination-record :page-length.sync="filterCurrencyForm.page_length" :records="currencies" @updateRecords="getCurrencies" @change.native="getCurrencies"></pagination-record>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import configurationSidebar from '../module-config-sidebar'
    import currencyForm from './form'

    export default {
        components : { configurationSidebar,currencyForm },
        data() {
            return {
                currencies: {
                    total: 0,
                    data: []
                },
                filterCurrencyForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getCurrencies();
        },
        methods: {
            getCurrencies(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterCurrencyForm);
                axios.get('/api/currency?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.currencies = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editCurrency(currency){
                this.$router.push('/configuration/currency/'+currency.id+'/edit');
            },
            confirmDelete(currency){
                return dialog => this.deleteCurrency(currency);
            },
            deleteCurrency(currency){
                axios.delete('/api/currency/'+currency.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getCurrencies();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getDefaultStatus(currency){
                return (currency.is_default) ? '<span class="label label-success">'+i18n.currency.default+'</span>' : '-';
            }
        }
    }
</script>

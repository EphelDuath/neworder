<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('payment.payment_method')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="payment-method"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">

                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('payment.add_new_payment_method')}}</h4>
                                        <payment-method-form @completed="getPaymentMethods"></payment-method-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('payment.payment_method_list')}}</h4>
                                        <h6 class="card-subtitle" v-if="payment_methods">{{trans('general.total_result_found',{'count' : payment_methods.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive" v-if="payment_methods.total">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('payment.payment_method_name')}}</th>
                                                        <th>{{trans('payment.payment_method_type')}}</th>
                                                        <th>{{trans('payment.payment_method_description')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="payment_method in payment_methods.data">
                                                        <td v-text="payment_method.name"></td>
                                                        <td>{{trans('transaction.'+payment_method.type)}}</td>
                                                        <td v-text="payment_method.description"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('payment.edit_payment_method')" @click.prevent="editPaymentMethod(payment_method)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="payment_method.id" v-confirm="{ok: confirmDelete(payment_method)}" v-tooltip="trans('payment.delete_payment_method')"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!payment_methods.total" module="payment" title="module_info_payment_method_title" description="module_info_payment_method_description" icon="money-bill-alt"></module-info>
                                        <pagination-record :page-length.sync="filterPaymentMethodForm.page_length" :records="payment_methods" @updateRecords="getPaymentMethods" @change.native="getPaymentMethods"></pagination-record>
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
    import paymentMethodForm from './form'

    export default {
        components : { configurationSidebar,paymentMethodForm },
        data() {
            return {
                payment_methods: {
                    total: 0,
                    data: []
                },
                filterPaymentMethodForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getPaymentMethods();
        },
        methods: {
            getPaymentMethods(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterPaymentMethodForm);
                axios.get('/api/payment-method?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.payment_methods = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editPaymentMethod(payment_method){
                this.$router.push('/configuration/payment-method/'+payment_method.id+'/edit');
            },
            confirmDelete(payment_method){
                return dialog => this.deletePaymentMethod(payment_method);
            },
            deletePaymentMethod(payment_method){
                axios.delete('/api/payment-method/'+payment_method.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getPaymentMethods();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        }
    }
</script>

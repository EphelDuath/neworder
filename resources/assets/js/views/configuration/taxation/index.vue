<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('taxation.taxation')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="taxation"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">

                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('taxation.add_new_taxation')}}</h4>
                                        <taxation-form @completed="getTaxations"></taxation-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('taxation.taxation_list')}}</h4>
                                        <h6 class="card-subtitle" v-if="taxations">{{trans('general.total_result_found',{'count' : taxations.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive" v-if="taxations.total">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('taxation.name')}}</th>
                                                        <th>{{trans('taxation.value')}}</th>
                                                        <th>{{trans('taxation.description')}}</th>
                                                        <th>{{trans('taxation.default')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="taxation in taxations.data">
                                                        <td v-text="taxation.name"></td>
                                                        <td>{{formatNumber(taxation.value)}}%</td>
                                                        <td v-text="taxation.description"></td>
                                                        <td v-html="getDefaultStatus(taxation)"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('taxation.edit_taxation')" @click.prevent="editTaxation(taxation)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="taxation.id" v-confirm="{ok: confirmDelete(taxation)}" v-tooltip="trans('taxation.delete_taxation')"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!taxations.total" module="taxation" title="module_info_title" description="module_info_description" icon="money-bill-alt"></module-info>
                                        <pagination-record :page-length.sync="filterTaxationForm.page_length" :records="taxations" @updateRecords="getTaxations" @change.native="getTaxations"></pagination-record>
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
    import taxationForm from './form'

    export default {
        components : { configurationSidebar,taxationForm },
        data() {
            return {
                taxations: {
                    total: 0,
                    data: []
                },
                filterTaxationForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getTaxations();
        },
        methods: {
            getTaxations(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterTaxationForm);
                axios.get('/api/taxation?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.taxations = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editTaxation(taxation){
                this.$router.push('/configuration/taxation/'+taxation.id+'/edit');
            },
            confirmDelete(taxation){
                return dialog => this.deleteTaxation(taxation);
            },
            deleteTaxation(taxation){
                axios.delete('/api/taxation/'+taxation.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getTaxations();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getDefaultStatus(taxation){
                return (taxation.is_default) ? '<span class="label label-success">'+i18n.taxation.default+'</span>' : '-';
            },
            formatNumber(number){
                return helper.formatNumber(number);
            }
        }
    }
</script>

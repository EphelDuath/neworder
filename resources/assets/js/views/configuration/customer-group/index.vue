<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('customer_group.customer_group')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="customer-group"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">

                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('customer_group.add_new_customer_group')}}</h4>
                                        <customer-group-form @completed="getCustomerGroups"></customer-group-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('customer_group.customer_group_list')}}</h4>
                                        <h6 class="card-subtitle" v-if="customer_groups">{{trans('general.total_result_found',{'count' : customer_groups.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive" v-if="customer_groups.total">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('customer_group.name')}}</th>
                                                        <th>{{trans('customer_group.description')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="customer_group in customer_groups.data">
                                                        <td v-text="customer_group.name"></td>
                                                        <td v-text="customer_group.description"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('customer_group.edit_customer_group')" @click.prevent="editCustomerGroup(customer_group)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="customer_group.id" v-confirm="{ok: confirmDelete(customer_group)}" v-tooltip="trans('customer_group.delete_customer_group')"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!customer_groups.total" module="customer_group" title="module_info_title" description="module_info_description" icon="object-group"></module-info>
                                        <pagination-record :page-length.sync="filterCustomerGroupForm.page_length" :records="customer_groups" @updateRecords="getCustomerGroups" @change.native="getCustomerGroups"></pagination-record>
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
    import customerGroupForm from './form'

    export default {
        components : { configurationSidebar,customerGroupForm },
        data() {
            return {
                customer_groups: {
                    total: 0,
                    data: []
                },
                filterCustomerGroupForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getCustomerGroups();
        },
        methods: {
            getCustomerGroups(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterCustomerGroupForm);
                axios.get('/api/customer-group?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.customer_groups = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editCustomerGroup(customer_group){
                this.$router.push('/configuration/customer-group/'+customer_group.id+'/edit');
            },
            confirmDelete(customer_group){
                return dialog => this.deleteCustomerGroup(customer_group);
            },
            deleteCustomerGroup(customer_group){
                axios.delete('/api/customer-group/'+customer_group.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getCustomerGroups();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        }
    }
</script>

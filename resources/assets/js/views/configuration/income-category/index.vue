<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('transaction.transaction_category',{type: trans('transaction.income')})}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="income-category"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">

                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('transaction.add_new_transaction_category',{type: trans('transaction.income')})}}</h4>
                                        <income-category-form @completed="getIncomeCategories"></income-category-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('transaction.transaction_category_list',{type: trans('transaction.income')})}}</h4>
                                        <h6 class="card-subtitle" v-if="income_categories">{{trans('general.total_result_found',{'count' : income_categories.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive" v-if="income_categories.total">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('transaction.transaction_category_name')}}</th>
                                                        <th>{{trans('transaction.transaction_category_description')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="income_category in income_categories.data">
                                                        <td v-text="income_category.name"></td>
                                                        <td v-text="income_category.description"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('transaction.edit_transaction_category',{type: trans('transaction.income')})" @click.prevent="editIncomeCategory(income_category)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="income_category.id" v-confirm="{ok: confirmDelete(income_category)}" v-tooltip="trans('transaction.delete_transaction_category',{type: trans('transaction.income')})"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!income_categories.total" module="transaction" title="module_info_income_category_title" description="module_info_income_category_description" icon="money-bill-alt"></module-info>
                                        <pagination-record :page-length.sync="filterIncomeCategoryForm.page_length" :records="income_categories" @updateRecords="getIncomeCategories" @change.native="getIncomeCategories"></pagination-record>
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
    import incomeCategoryForm from './form'

    export default {
        components : { configurationSidebar,incomeCategoryForm },
        data() {
            return {
                income_categories: {
                    total: 0,
                    data: []
                },
                filterIncomeCategoryForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getIncomeCategories();
        },
        methods: {
            getIncomeCategories(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterIncomeCategoryForm);
                axios.get('/api/income-category?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.income_categories = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editIncomeCategory(income_category){
                this.$router.push('/configuration/income-category/'+income_category.id+'/edit');
            },
            confirmDelete(income_category){
                return dialog => this.deleteIncomeCategory(income_category);
            },
            deleteIncomeCategory(income_category){
                axios.delete('/api/income-category/'+income_category.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getIncomeCategories();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        }
    }
</script>

<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('transaction.transaction_category',{type: trans('transaction.expense')})}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="expense-category"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">

                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('transaction.add_new_transaction_category',{type: trans('transaction.expense')})}}</h4>
                                        <expense-category-form @completed="getExpenseCategories"></expense-category-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('transaction.transaction_category_list',{type: trans('transaction.expense')})}}</h4>
                                        <h6 class="card-subtitle" v-if="expense_categories">{{trans('general.total_result_found',{'count' : expense_categories.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive" v-if="expense_categories.total">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('transaction.transaction_category_name')}}</th>
                                                        <th>{{trans('transaction.transaction_category_description')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="expense_category in expense_categories.data">
                                                        <td v-text="expense_category.name"></td>
                                                        <td v-text="expense_category.description"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('transaction.edit_transaction_category',{type: trans('transaction.expense')})" @click.prevent="editExpenseCategory(expense_category)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="expense_category.id" v-confirm="{ok: confirmDelete(expense_category)}" v-tooltip="trans('transaction.delete_transaction_category',{type: trans('transaction.expense')})"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!expense_categories.total" module="transaction" title="module_info_expense_category_title" description="module_info_expense_category_description" icon="money-bill-alt"></module-info>
                                        <pagination-record :page-length.sync="filterExpenseCategoryForm.page_length" :records="expense_categories" @updateRecords="getExpenseCategories" @change.native="getExpenseCategories"></pagination-record>
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
    import expenseCategoryForm from './form'

    export default {
        components : { configurationSidebar,expenseCategoryForm },
        data() {
            return {
                expense_categories: {
                    total: 0,
                    data: []
                },
                filterExpenseCategoryForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getExpenseCategories();
        },
        methods: {
            getExpenseCategories(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterExpenseCategoryForm);
                axios.get('/api/expense-category?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.expense_categories = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editExpenseCategory(expense_category){
                this.$router.push('/configuration/expense-category/'+expense_category.id+'/edit');
            },
            confirmDelete(expense_category){
                return dialog => this.deleteExpenseCategory(expense_category);
            },
            deleteExpenseCategory(expense_category){
                axios.delete('/api/expense-category/'+expense_category.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getExpenseCategories();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        }
    }
</script>

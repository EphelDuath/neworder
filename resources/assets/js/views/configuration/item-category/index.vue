<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('item.item_category')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="item-category"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">

                                <div class="row">
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <h4 class="card-title">{{trans('item.add_new_item_category')}}</h4>
                                        <item-category-form @completed="getItemCategories"></item-category-form>

                                    </div>
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <h4 class="card-title">{{trans('item.item_category_list')}}</h4>
                                        <h6 class="card-subtitle" v-if="item_categories">{{trans('general.total_result_found',{'count' : item_categories.total})}}</h6>
                                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                                        <div class="table-responsive">
                                            <table class="table" v-if="item_categories.total">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('item.item_category_name')}}</th>
                                                        <th>{{trans('item.item_type')}}</th>
                                                        <th>{{trans('item.item_category_description')}}</th>
                                                        <th class="table-option">{{trans('general.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="item_category in item_categories.data">
                                                        <td v-text="item_category.name"></td>
                                                        <td>{{trans('item.'+item_category.type)}}</td>
                                                        <td v-text="item_category.description"></td>
                                                        <td class="table-option">
                                                            <div class="btn-group">
                                                                <button class="btn btn-info btn-sm" v-tooltip="trans('item.edit_item_category')" @click.prevent="editItemCategory(item_category)"><i class="fas fa-edit"></i></button>
                                                                <button class="btn btn-danger btn-sm" :key="item_category.id" v-confirm="{ok: confirmDelete(item_category)}" v-tooltip="trans('item.delete_item_category')"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <module-info v-if="!item_categories.total" module="item" title="module_info_item_category_title" description="module_info_item_category_description" icon="th-large"></module-info>
                                        <pagination-record :page-length.sync="filterItemCategoryForm.page_length" :records="item_categories" @updateRecords="getItemCategories" @change.native="getItemCategories"></pagination-record>
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
    import itemCategoryForm from './form'

    export default {
        components : { configurationSidebar,itemCategoryForm },
        data() {
            return {
                item_categories: {
                    total: 0,
                    data: []
                },
                filterItemCategoryForm: {
                    page_length: helper.getConfig('page_length')
                }
            };
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getItemCategories();
        },
        methods: {
            getItemCategories(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterItemCategoryForm);
                axios.get('/api/item-category?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.item_categories = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editItemCategory(item_category){
                this.$router.push('/configuration/item-category/'+item_category.id+'/edit');
            },
            confirmDelete(item_category){
                return dialog => this.deleteItemCategory(item_category);
            },
            deleteItemCategory(item_category){
                axios.delete('/api/item-category/'+item_category.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getItemCategories();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        }
    }
</script>

<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('item.item')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('item.item')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <transition name="fade">
                    <div class="card" v-if="showFilterPanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showFilterPanel" @click="showFilterPanel = !showFilterPanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('general.filter')}}</h4>
                            <div class="row">
                                <div class="col-4 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('item.item_name')}}</label>
                                        <input class="form-control" name="name" v-model="filterItemForm.name">
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('item.item_code')}}</label>
                                        <input class="form-control" name="code" v-model="filterItemForm.code">
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('item.item_category')}}</label>
                                        <select v-model="filterItemForm.item_category_id" class="custom-select col-12">
                                          <option value="">{{trans('general.select_one')}}</option>
                                          <option v-for="item_category in item_categories" v-bind:value="item_category.id">
                                            {{ item_category.name }}
                                          </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('taxation.taxation')}}</label>
                                        <select v-model="filterItemForm.taxation_id" class="custom-select col-12">
                                          <option value="">{{trans('general.select_one')}}</option>
                                          <option v-for="taxation in taxations" v-bind:value="taxation.id">
                                            {{ taxation.name }}
                                          </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="order" class="form-control" v-model="filterItemForm.sort_by">
                                            <option value="name">{{trans('item.item_name')}}</option>
                                            <option value="code">{{trans('item.item_code')}}</option>
                                            <option value="item_category_id">{{trans('item.item_category')}}</option>
                                            <option value="taxation_id">{{trans('taxation.taxation')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterItemForm.order">
                                            <option value="asc">{{trans('general.ascending')}}</option>
                                            <option value="desc">{{trans('general.descending')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

                <transition name="fade" v-if="hasPermission('create-item')">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('item.add_new_item')}}</h4>
                            <item-form @completed="getItems"></item-form>
                        </div>
                    </div>
                </transition>

                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="items.total && hasPermission('create-item') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                        <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                        <h4 class="card-title">{{trans('item.item_list')}}</h4>
                        <h6 class="card-subtitle" v-if="items">{{trans('general.total_result_found',{'count' : items.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="items.total && hasPermission('list-item')">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('item.item_name')}}</th>
                                        <th>{{trans('item.item_code')}}</th>
                                        <th>{{trans('item.item_category')}}</th>
                                        <th>{{trans('item.item_price')}}</th>
                                        <th>{{trans('taxation.taxation')}}</th>
                                        <th>{{trans('item.discount')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items.data">
                                        <td v-text="item.name"></td>
                                        <td v-text="item.code"></td>
                                        <td v-text="item.item_category.name"></td>
                                        <td>
                                            <ol>
                                                <li v-for="item_price in item.item_price">
                                                    {{formatCurrency(item_price)}}
                                                </li>
                                            </ol>
                                        </td>
                                        <td v-text="item.taxation.name+' ('+formatNumber(item.taxation.value)+'%)'"></td>
                                        <td>{{formatNumber(item.discount)}}</td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-item')" v-tooltip="trans('item.edit_item')" @click.prevent="editItem(item)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-item')" :key="item.id" v-confirm="{ok: confirmDelete(item)}" v-tooltip="trans('item.delete_item')"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!items.total" module="item" title="module_info_item_title" description="module_info_item_description" icon="th-large">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-item') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterItemForm.page_length" :records="items" @updateRecords="getItems" @change.native="getItems"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import itemForm from './form'

    export default {
        components : { itemForm },
        data() {
            return {
                items: {
                    total: 0,
                    data: []
                },
                filterItemForm: {
                    name: '',
                    code: '',
                    item_category_id: '',
                    taxation_id: '',
                    sort_by : 'name',
                    order: 'asc',
                    page_length: helper.getConfig('page_length')
                },
                item_categories: [],
                taxations: [],
                showCreatePanel: false,
                showFilterPanel: false
            };
        },
        mounted(){
            if(!helper.hasPermission('list-item') && !helper.hasPermission('create-item')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getItems();

            axios.get('/api/item/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.item_categories = response.item_categories;
                    this.taxations = response.taxations;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getItems(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterItemForm);
                axios.get('/api/item?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.items = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editItem(item){
                this.$router.push('/item/'+item.id+'/edit');
            },
            confirmDelete(item){
                return dialog => this.deleteItem(item);
            },
            deleteItem(item){
                axios.delete('/api/item/'+item.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getItems();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            formatCurrency(item_price){
                return helper.formatCurrency(item_price.unit_price,item_price.currency);
            },
            formatNumber(number){
                return helper.formatNumber(number);
            }
        },
        watch: {
            filterItemForm: {
                handler(val){
                    this.getItems();
                },
                deep: true
            }
        }
    }
</script>

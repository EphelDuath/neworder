<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('supplier.supplier')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('supplier.supplier')}}</li>
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
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('supplier.name')}}</label>
                                        <input class="form-control" name="name" v-model="filterSupplierForm.name">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('supplier.email')}}</label>
                                        <input class="form-control" name="email" v-model="filterSupplierForm.email">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('supplier.phone')}}</label>
                                        <input class="form-control" name="phone" v-model="filterSupplierForm.phone">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="order" class="form-control" v-model="filterSupplierForm.sort_by">
                                            <option value="name">{{trans('supplier.name')}}</option>
                                            <option value="email">{{trans('supplier.email')}}</option>
                                            <option value="phone">{{trans('supplier.phone')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterSupplierForm.order">
                                            <option value="asc">{{trans('general.ascending')}}</option>
                                            <option value="desc">{{trans('general.descending')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
                <transition name="fade" v-if="hasPermission('create-supplier')">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('supplier.add_new_supplier')}}</h4>
                            <supplier-form @completed="getSuppliers"></supplier-form>
                        </div>
                    </div>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="suppliers.total && hasPermission('create-supplier') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                        <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                        <h4 class="card-title">{{trans('supplier.supplier_list')}}</h4>
                        <h6 class="card-subtitle" v-if="suppliers">{{trans('general.total_result_found',{'count' : suppliers.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="hasPermission('list-supplier') && suppliers.total">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('supplier.name')}}</th>
                                        <th>{{trans('supplier.email')}}</th>
                                        <th>{{trans('company.company')}}</th>
                                        <th>{{trans('supplier.phone')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="supplier in suppliers.data">
                                        <td v-text="supplier.name"></td>
                                        <td v-text="supplier.email"></td>
                                        <td v-text="supplier.company_id ? supplier.company.name : ''"></td>
                                        <td v-text="supplier.phone"></td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-supplier')" v-tooltip="trans('supplier.edit_supplier')" @click.prevent="editSupplier(supplier)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-supplier')"  :key="supplier.id" v-confirm="{ok: confirmDelete(supplier)}" v-tooltip="trans('supplier.delete_supplier')"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!suppliers.total" module="supplier" title="module_info_title" description="module_info_description" icon="address-book">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-supplier') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterSupplierForm.page_length" :records="suppliers" @updateRecords="getSuppliers"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import supplierForm from './form'

    export default {
        components : { supplierForm },
        data() {
            return {
                suppliers: {
                    total: 0,
                    data: []
                },
                filterSupplierForm: {
                    name: '',
                    phone: '',
                    email: '',
                    sort_by : 'name',
                    order: 'asc',
                    page_length: helper.getConfig('page_length')
                },
                showCreatePanel: false,
                showFilterPanel: false
            };
        },
        mounted(){
            if(!helper.hasPermission('list-supplier') && !helper.hasPermission('create-supplier')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getSuppliers();
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getSuppliers(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterSupplierForm);
                axios.get('/api/supplier?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.suppliers = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editSupplier(supplier){
                this.$router.push('/supplier/'+supplier.id+'/edit');
            },
            confirmDelete(supplier){
                return dialog => this.deleteSupplier(supplier);
            },
            deleteSupplier(supplier){
                axios.delete('/api/supplier/'+supplier.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getSuppliers();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        },
        watch: {
            filterSupplierForm: {
                handler(val){
                    this.getSuppliers();
                },
                deep: true
            }
        }
    }
</script>

<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('company.company')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('company.company')}}</li>
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
                                        <label for="">{{trans('company.name')}}</label>
                                        <input class="form-control" name="name" v-model="filterCompanyForm.name">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('company.email')}}</label>
                                        <input class="form-control" name="email" v-model="filterCompanyForm.email">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('company.website')}}</label>
                                        <input class="form-control" name="website" v-model="filterCompanyForm.website">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('company.phone')}}</label>
                                        <input class="form-control" name="phone" v-model="filterCompanyForm.phone">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.sort_by')}}</label>
                                        <select name="order" class="form-control" v-model="filterCompanyForm.sort_by">
                                            <option value="name">{{trans('company.name')}}</option>
                                            <option value="email">{{trans('company.email')}}</option>
                                            <option value="website">{{trans('company.website')}}</option>
                                            <option value="phone">{{trans('company.phone')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-group">
                                        <label for="">{{trans('general.order')}}</label>
                                        <select name="order" class="form-control" v-model="filterCompanyForm.order">
                                            <option value="asc">{{trans('general.ascending')}}</option>
                                            <option value="desc">{{trans('general.descending')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
                <transition name="fade" v-if="hasPermission('create-company')">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('company.add_new_company')}}</h4>
                            <company-form @completed="getCompanies"></company-form>
                        </div>
                    </div>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="companies.total && hasPermission('create-company') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                        <button class="btn btn-info btn-sm pull-right m-r-5" v-if="!showFilterPanel" @click="showFilterPanel = !showFilterPanel"><i class="fas fa-filter"></i> {{trans('general.filter')}}</button>
                        <h4 class="card-title">{{trans('company.company_list')}}</h4>
                        <h6 class="card-subtitle" v-if="companies">{{trans('general.total_result_found',{'count' : companies.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="hasPermission('list-company') && companies.total">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('company.name')}}</th>
                                        <th>{{trans('company.email')}}</th>
                                        <th>{{trans('company.website')}}</th>
                                        <th>{{trans('company.phone')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="company in companies.data">
                                        <td v-text="company.name"></td>
                                        <td v-text="company.email"></td>
                                        <td v-text="company.website"></td>
                                        <td v-text="company.phone"></td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-company')" v-tooltip="trans('company.edit_company')" @click.prevent="editCompany(company)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-company')"  :key="company.id" v-confirm="{ok: confirmDelete(company)}" v-tooltip="trans('company.delete_company')"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!companies.total" module="company" title="module_info_title" description="module_info_description" icon="building">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-company') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterCompanyForm.page_length" :records="companies" @updateRecords="getCompanies"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import companyForm from './form'

    export default {
        components : { companyForm },
        data() {
            return {
                companies: {
                    total: 0,
                    data: []
                },
                filterCompanyForm: {
                    name: '',
                    website: '',
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
            if(!helper.hasPermission('list-company') && !helper.hasPermission('create-company')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.getCompanies();
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getCompanies(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterCompanyForm);
                axios.get('/api/company?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.companies = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editCompany(company){
                this.$router.push('/company/'+company.id+'/edit');
            },
            confirmDelete(company){
                return dialog => this.deleteCompany(company);
            },
            deleteCompany(company){
                axios.delete('/api/company/'+company.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getCompanies();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            }
        },
        watch: {
            filterCompanyForm: {
                handler(val){
                    this.getCompanies();
                },
                deep: true
            }
        }
    }
</script>

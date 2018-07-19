<template>
    <form @submit.prevent="proceed" @keydown="supplierForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('supplier.name')}}</label>
                    <input class="form-control" type="text" value="" v-model="supplierForm.name" name="name" :placeholder="trans('supplier.name')">
                    <show-error :form-name="supplierForm" prop-name="name"></show-error>
                </div>
                <div class="form-group">
                    <label for="">{{trans('company.company')}}</label>
                    <v-select label="name" v-model="selected_company" name="company_id" id="company_id" :options="companies" :placeholder="trans('company.select_company')" @select="onCompanySelect" @close="supplierForm.errors.clear('company_id')" @remove="supplierForm.company_id = ''"></v-select>
                    <show-error :form-name="supplierForm" prop-name="company_id"></show-error>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.email')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.email" name="email" :placeholder="trans('supplier.email')">
                            <show-error :form-name="supplierForm" prop-name="email"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.phone')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.phone" name="phone" :placeholder="trans('supplier.phone')">
                            <show-error :form-name="supplierForm" prop-name="phone"></show-error>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.address_line_1')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.address_line_1" name="address_line_1" :placeholder="trans('supplier.address_line_1')">
                            <show-error :form-name="supplierForm" prop-name="address_line_1"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.address_line_2')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.address_line_2" name="address_line_2" :placeholder="trans('supplier.address_line_2')">
                            <show-error :form-name="supplierForm" prop-name="address_line_2"></show-error>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.city')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.city" name="city" :placeholder="trans('supplier.city')">
                            <show-error :form-name="supplierForm" prop-name="city"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.state')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.state" name="state" :placeholder="trans('supplier.state')">
                            <show-error :form-name="supplierForm" prop-name="state"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.zipcode')}}</label>
                            <input class="form-control" type="text" value="" v-model="supplierForm.zipcode" name="zipcode" :placeholder="trans('supplier.zipcode')">
                            <show-error :form-name="supplierForm" prop-name="zipcode"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('supplier.country')}}</label>
                            <select class="form-control" name="country_id" v-model="supplierForm.country_id">
                                <option value="">{{trans('supplier.country')}}</option>
                                <option v-for="country in countries" :value="country.value">{{country.text}}</option>
                            </select>
                            <show-error :form-name="supplierForm" prop-name="country_id"></show-error>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/supplier" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import vSelect from 'vue-multiselect'

    export default {
        components: {vSelect},
        data() {
            return {
                supplierForm: new Form({
                    name : '',
                    company_id: '',
                    email : '',
                    phone: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    zipcode: '',
                    country_id: ''
                }),
                countries: [],
                companies: [],
                selected_company: null
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getSupplier();
            axios.get('/api/supplier/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.countries = response.countries;
                    this.companies = response.companies;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateSupplier();
                else
                    this.storeSupplier();
            },
            storeSupplier(){
                this.supplierForm.post('/api/supplier')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed')
                        this.selected_company = null;
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getSupplier(){
                axios.get('/api/supplier/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.supplierForm.name = response.supplier.name;
                        this.supplierForm.email = response.supplier.email;
                        this.supplierForm.phone = response.supplier.phone;
                        this.supplierForm.company_id = response.supplier.company_id;
                        this.supplierForm.address_line_1 = response.supplier.address_line_1;
                        this.supplierForm.address_line_2 = response.supplier.address_line_2;
                        this.supplierForm.city = response.supplier.city;
                        this.supplierForm.state = response.supplier.state;
                        this.supplierForm.zipcode = response.supplier.zipcode;
                        this.supplierForm.country_id = response.supplier.country_id;
                        this.selected_company = response.selected_company;
                    })
                    .catch(error => {
                        this.$router.push('/supplier');
                    });
            },
            updateSupplier(){
                this.supplierForm.patch('/api/supplier/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/supplier');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            onCompanySelect(selectedOption){
                this.supplierForm.company_id = selectedOption.id;
            }
        }
    }
</script>

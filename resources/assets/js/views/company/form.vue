<template>
    <form @submit.prevent="proceed" @keydown="companyForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.name')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.name" name="name" :placeholder="trans('company.name')">
                            <show-error :form-name="companyForm" prop-name="name"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.email')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.email" name="email" :placeholder="trans('company.email')">
                            <show-error :form-name="companyForm" prop-name="email"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.website')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.website" name="website" :placeholder="trans('company.website')">
                            <show-error :form-name="companyForm" prop-name="website"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.phone')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.phone" name="phone" :placeholder="trans('company.phone')">
                            <show-error :form-name="companyForm" prop-name="phone"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.fax')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.fax" name="fax" :placeholder="trans('company.fax')">
                            <show-error :form-name="companyForm" prop-name="fax"></show-error>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.address_line_1')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.address_line_1" name="address_line_1" :placeholder="trans('company.address_line_1')">
                            <show-error :form-name="companyForm" prop-name="address_line_1"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.address_line_2')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.address_line_2" name="address_line_2" :placeholder="trans('company.address_line_2')">
                            <show-error :form-name="companyForm" prop-name="address_line_2"></show-error>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.city')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.city" name="city" :placeholder="trans('company.city')">
                            <show-error :form-name="companyForm" prop-name="city"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.state')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.state" name="state" :placeholder="trans('company.state')">
                            <show-error :form-name="companyForm" prop-name="state"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.zipcode')}}</label>
                            <input class="form-control" type="text" value="" v-model="companyForm.zipcode" name="zipcode" :placeholder="trans('company.zipcode')">
                            <show-error :form-name="companyForm" prop-name="zipcode"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('company.country')}}</label>
                            <select class="form-control" name="country_id" v-model="companyForm.country_id" @change="companyForm.errors.clear('country_id')">
                                <option value="">{{trans('company.country')}}</option>
                                <option v-for="country in countries" :value="country.value">{{country.text}}</option>
                            </select>
                            <show-error :form-name="companyForm" prop-name="country_id"></show-error>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/company" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    export default {
        data() {
            return {
                companyForm: new Form({
                    name : '',
                    email : '',
                    website: '',
                    phone: '',
                    fax: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    zipcode: '',
                    country_id: ''
                }),
                countries: []
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getCompany();
            axios.get('/api/fetch/lists?lists=country')
                .then(response => response.data)
                .then(response => {
                    this.countries = response.data.country;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateCompany();
                else
                    this.storeCompany();
            },
            storeCompany(){
                this.companyForm.post('/api/company')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed')
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getCompany(){
                axios.get('/api/company/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.companyForm.name = response.name;
                        this.companyForm.email = response.email;
                        this.companyForm.website = response.website;
                        this.companyForm.phone = response.phone;
                        this.companyForm.fax = response.fax;
                        this.companyForm.address_line_1 = response.address_line_1;
                        this.companyForm.address_line_2 = response.address_line_2;
                        this.companyForm.city = response.city;
                        this.companyForm.state = response.state;
                        this.companyForm.zipcode = response.zipcode;
                        this.companyForm.country_id = response.country_id;
                    })
                    .catch(error => {
                        this.$router.push('/company');
                    });
            },
            updateCompany(){
                this.companyForm.patch('/api/company/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/company');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

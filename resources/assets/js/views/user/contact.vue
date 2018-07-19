<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('user.user_detail',{type: trans('user.'+type)})}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link :to="`/user/${type}`">{{trans('user.'+type)}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('user.contact')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-8 col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <user-sidebar menu="contact" :type="type" :id="id"></user-sidebar>
                            <div class="col-9 col-lg-9 col-md-9">
                                <h4 class="card-title">{{trans('user.contact')}}</h4>
                                <form @submit.prevent="submit" @keydown="userForm.errors.clear($event.target.name)">
                                    <div class="col-12 col-md-12">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.work_email')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.work_email" name="work_email" :placeholder="trans('user.work_email')">
                                                    <show-error :form-name="userForm" prop-name="work_email"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.phone')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.phone" name="phone" :placeholder="trans('user.phone')">
                                                    <show-error :form-name="userForm" prop-name="phone"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.work_phone')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.work_phone" name="work_phone" :placeholder="trans('user.work_phone')">
                                                    <show-error :form-name="userForm" prop-name="work_phone"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.work_phone_extension')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.work_phone_extension" name="work_phone_extension" :placeholder="trans('user.work_phone_extension')">
                                                    <show-error :form-name="userForm" prop-name="work_phone_extension"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.address_line_1')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.address_line_1" name="address_line_1" :placeholder="trans('user.address_line_1')">
                                                    <show-error :form-name="userForm" prop-name="address_line_1"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.address_line_2')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.address_line_2" name="address_line_2" :placeholder="trans('user.address_line_2')">
                                                    <show-error :form-name="userForm" prop-name="address_line_2"></show-error>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.city')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.city" name="city" :placeholder="trans('user.city')">
                                                    <show-error :form-name="userForm" prop-name="city"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.state')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.state" name="state" :placeholder="trans('user.state')">
                                                    <show-error :form-name="userForm" prop-name="state"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.zipcode')}}</label>
                                                    <input class="form-control" type="text" value="" v-model="userForm.zipcode" name="zipcode" :placeholder="trans('user.zipcode')">
                                                    <show-error :form-name="userForm" prop-name="zipcode"></show-error>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">{{trans('user.country')}}</label>
                                                    <select class="form-control" name="country_id" v-model="userForm.country_id">
                                                        <option value="">{{trans('user.country')}}</option>
                                                        <option v-for="country in countries" v-bind:value="country.value">{{country.text}}</option>
                                                    </select>
                                                    <show-error :form-name="userForm" prop-name="country_id"></show-error>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-info waves-effect waves-light m-t-10 pull-right">{{trans('general.save')}}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <user-summary :user="user" :type="type"></user-summary>
        </div>
    </div>
</template>


<script>
    import userSidebar from './user-sidebar'
    import userSummary from './summary'

    export default {
        components : { userSidebar,userSummary },
        data() {
            return {
                id:this.$route.params.id,
                type:this.$route.params.type,
                userForm: new Form({
                    phone: '',
                    work_phone: '',
                    work_phone_extension: '',
                    work_email: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    zipcode: '',
                    country_id: ''
                },false),
                countries: [],
                user: ''
            };
        },
        mounted(){
            if(this.type !== 'customer' && this.type !== 'staff')
                this.$router.push('/user/staff');

            if(!helper.hasPermission('edit-'+this.type)){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            axios.get('/api/user/pre-requisite/')
                .then(response => response.data)
                .then(response => {
                    this.countries = response.countries;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error)
                });
                
            axios.get('/api/user/'+this.type+'/'+this.id)
                .then(response => response.data)
                .then(response => {
                    this.user = response.user;
                    this.userForm.phone = response.user.profile.phone;
                    this.userForm.work_phone = response.user.profile.work_phone;
                    this.userForm.work_phone_extension = response.user.profile.work_phone_extension;
                    this.userForm.work_email = response.user.profile.work_email;
                    this.userForm.address_line_1 = response.user.profile.address_line_1;
                    this.userForm.address_line_2 = response.user.profile.address_line_2;
                    this.userForm.city = response.user.profile.city;
                    this.userForm.state = response.user.profile.state;
                    this.userForm.zipcode = response.user.profile.zipcode;
                    this.userForm.country_id = response.user.profile.country_id || '';
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                    this.$router.push('/user/staff');
                })
        },
        methods: {
            submit(){
                this.userForm.patch('/api/user/'+this.type+'/'+this.id+'/contact')
                    .then(response => {
                        toastr.success(response.message);
                        this.user.profile.phone = this.userForm.phone;
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

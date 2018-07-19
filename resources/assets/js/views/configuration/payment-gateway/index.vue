<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/basic">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('payment.payment_gateway')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="payment-gateway"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">
                                <h4 class="card-title">{{trans('payment.payment_gateway')}}</h4>
                                <form @submit.prevent="submit" @keydown="configForm.errors.clear($event.target.name)">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Paypal</label>
                                                <div>
                                                    <switches class="" v-model="configForm.paypal" name="paypal" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group" v-if="configForm.paypal">
                                                <label for="">Paypal Mode</label>
                                                <div>
                                                    <switches class="" v-model="configForm.paypal_mode" name="paypal_mode" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group" v-if="configForm.paypal">
                                                <label for="">Paypal Client Id</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.paypal_client_id" name="paypal_client_id" placeholder="Paypal Client Id">
                                            </div>
                                            <div class="form-group" v-if="configForm.paypal">
                                                <label for="">Paypal Client Secret</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.paypal_client_secret" name="paypal_client_secret" placeholder="Paypal Client Secret">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Stripe</label>
                                                <div>
                                                    <switches class="" v-model="configForm.stripe" name="stripe" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group" v-if="configForm.stripe">
                                                <label for="">Stripe Mode</label>
                                                <div>
                                                    <switches class="" v-model="configForm.stripe_mode" name="stripe_mode" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group" v-if="configForm.stripe">
                                                <label for="">Stripe Publishable Key</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.stripe_publishable_key" name="stripe_publishable_key" placeholder="Stripe Publishable Key">
                                            </div>
                                            <div class="form-group" v-if="configForm.stripe">
                                                <label for="">Stripe Private Key</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.stripe_private_key" name="stripe_private_key" placeholder="Stripe Private Key">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-info waves-effect waves-light pull-right m-t-10">{{trans('general.save')}}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import configurationSidebar from '../system-config-sidebar'
    import switches from 'vue-switches'

    export default {
        components : { configurationSidebar,switches },
        data() {
            return {
                configForm: new Form({
                    config_type: '',
                    paypal: 0,
                    stripe: 0,
                    paypal_mode: 0,
                    stripe_mode: 0,
                    paypal_client_id: '',
                    paypal_client_secret: '',
                    stripe_publishable_key: '',
                    stripe_private_key: ''
                },false)
            }
        },
        mounted(){
            if(!helper.hasPermission('access-configuration')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            axios.get('/api/configuration')
                .then(response => response.data)
                .then(response => {
                    this.configForm = helper.formAssign(this.configForm, response);
                }).catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            submit(){
                this.configForm.config_type = 'payment_gateway';
                this.configForm.paypal = (this.configForm.paypal) ? 1 : 0;
                this.configForm.stripe = (this.configForm.stripe) ? 1 : 0;
                this.configForm.paypal_mode = (this.configForm.paypal_mode) ? 1 : 0;
                this.configForm.stripe_mode = (this.configForm.stripe_mode) ? 1 : 0;
                this.configForm.post('/api/configuration')
                    .then(response => {
                        this.$store.dispatch('setConfig',this.configForm);
                        toastr.success(response.message);
                    }).catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('configuration.module_configuration')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/configuration/module">{{trans('configuration.configuration')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('quotation.quotation')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <configuration-sidebar menu="quotation"></configuration-sidebar>
                            <div class="col-10 col-lg-10 col-md-10">
                                <form @submit.prevent="submit" @keydown="configForm.errors.clear($event.target.name)">
                                    <h4 class="card-title">{{trans('quotation.quotation')}}</h4>
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.default_line_item_discount')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_default_line_item_discount" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.default_line_item_tax')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_default_line_item_tax" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.default_line_item_description')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_default_line_item_description" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.default_subtotal_discount')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_default_subtotal_discount" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.default_subtotal_tax')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_default_subtotal_tax" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.default_subtotal_shipping_and_handling')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_default_subtotal_shipping_and_handling" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.random_reference_number')}}</label>
                                                <div>
                                                    <switches class="" v-model="configForm.quotation_random_reference_number" theme="bootstrap" color="success"></switches>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.prefix')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_prefix" name="quotation_prefix" :placeholder="trans('quotation.prefix')">
                                                <show-error :form-name="configForm" prop-name="quotation_prefix"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.number_digit')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_number_digit" name="quotation_number_digit" :placeholder="trans('quotation.number_digit')">
                                                <show-error :form-name="configForm" prop-name="quotation_number_digit"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.start_number')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_start_number" name="quotation_start_number" :placeholder="trans('quotation.start_number')">
                                                <show-error :form-name="configForm" prop-name="quotation_start_number"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.line_item_quantity_decimal_place')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_line_item_quantity_decimal_place" name="quotation_line_item_quantity_decimal_place" :placeholder="trans('quotation.line_item_quantity_decimal_place')">
                                                <show-error :form-name="configForm" prop-name="quotation_line_item_quantity_decimal_place"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.line_item_tax_decimal_place')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_line_item_tax_decimal_place" name="quotation_line_item_tax_decimal_place" :placeholder="trans('quotation.line_item_tax_decimal_place')">
                                                <show-error :form-name="configForm" prop-name="quotation_line_item_tax_decimal_place"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.line_item_discount_decimal_place')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_line_item_discount_decimal_place" name="quotation_line_item_discount_decimal_place" :placeholder="trans('quotation.line_item_discount_decimal_place')">
                                                <show-error :form-name="configForm" prop-name="quotation_line_item_discount_decimal_place"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.subtotal_discount_decimal_place')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_subtotal_discount_decimal_place" name="quotation_subtotal_discount_decimal_place" :placeholder="trans('quotation.subtotal_discount_decimal_place')">
                                                <show-error :form-name="configForm" prop-name="quotation_subtotal_discount_decimal_place"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.subtotal_tax_decimal_place')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_subtotal_tax_decimal_place" name="quotation_subtotal_tax_decimal_place" :placeholder="trans('quotation.subtotal_tax_decimal_place')">
                                                <show-error :form-name="configForm" prop-name="quotation_subtotal_tax_decimal_place"></show-error>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{trans('quotation.subtotal_shipping_and_handling_decimal_place')}}</label>
                                                <input class="form-control" type="text" value="" v-model="configForm.quotation_subtotal_shipping_and_handling_decimal_place" name="quotation_subtotal_shipping_and_handling_decimal_place" :placeholder="trans('quotation.subtotal_shipping_and_handling_decimal_place')">
                                                <show-error :form-name="configForm" prop-name="quotation_subtotal_shipping_and_handling_decimal_place"></show-error>
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
        </div>
    </div>
</template>


<script>
    import configurationSidebar from '../module-config-sidebar'
    import switches from 'vue-switches'

    export default {
        components : { configurationSidebar,switches },
        data() {
            return {
                configForm: new Form({
                    quotation_default_line_item_discount: '',
                    quotation_default_line_item_tax: '',
                    quotation_default_line_item_description: '',
                    quotation_default_subtotal_discount: '',
                    quotation_default_subtotal_tax: '',
                    quotation_default_subtotal_shipping_and_handling: '',
                    quotation_random_reference_number: '',
                    quotation_prefix: '',
                    quotation_number_digit: '',
                    quotation_start_number: '',
                    quotation_line_item_quantity_decimal_place: '',
                    quotation_line_item_tax_decimal_place: '',
                    quotation_line_item_discount_decimal_place: '',
                    quotation_subtotal_discount_decimal_place: '',
                    quotation_subtotal_tax_decimal_place: '',
                    quotation_subtotal_shipping_and_handling_decimal_place: '',
                    config_type: ''
                }, false),
            };
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
                this.configForm.config_type = 'quotation';
                this.configForm.quotation_default_line_item_discount = (this.configForm.quotation_default_line_item_discount) ? 1 : 0;
                this.configForm.quotation_default_line_item_tax = (this.configForm.quotation_default_line_item_tax) ? 1 : 0;
                this.configForm.quotation_default_line_item_description = (this.configForm.quotation_default_line_item_description) ? 1 : 0;
                this.configForm.quotation_default_subtotal_discount = (this.configForm.quotation_default_subtotal_discount) ? 1 : 0;
                this.configForm.quotation_default_subtotal_tax = (this.configForm.quotation_default_subtotal_tax) ? 1 : 0;
                this.configForm.quotation_default_subtotal_shipping_and_handling = (this.configForm.quotation_default_subtotal_shipping_and_handling) ? 1 : 0;
                this.configForm.quotation_random_reference_number = (this.configForm.quotation_random_reference_number) ? 1 : 0;
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

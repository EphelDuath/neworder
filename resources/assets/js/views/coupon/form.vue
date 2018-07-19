<template>
    <form @submit.prevent="proceed" @keydown="couponForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('coupon.code')}}</label>
                    <input class="form-control" type="text" value="" v-model="couponForm.code" name="code" :placeholder="trans('coupon.code')">
                    <show-error :form-name="couponForm" prop-name="code"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('coupon.discount')}}</label>
                    <div class="input-group">
                        <input class="form-control" type="text" value="" v-model="couponForm.discount" name="discount" :placeholder="trans('coupon.discount')">
                        <div class="input-group-append" id="basic-addon1"><span class="input-group-text">%</span></div>
                    </div>
                    <show-error :form-name="couponForm" prop-name="discount"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <switches class="m-l-20" v-model="couponForm.new_user" theme="bootstrap" color="success"></switches> {{trans('coupon.new_user_label')}}
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('coupon.valid_start_date')}}</label>
                    <datepicker v-model="couponForm.valid_start_date" :bootstrapStyling="true" @selected="couponForm.errors.clear('valid_start_date')" :placeholder="trans('coupon.valid_start_date')"></datepicker>
                    <show-error :form-name="couponForm" prop-name="valid_start_date"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('coupon.valid_end_date')}}</label>
                    <datepicker v-model="couponForm.valid_end_date" :bootstrapStyling="true" @selected="couponForm.errors.clear('valid_end_date')" :placeholder="trans('coupon.valid_end_date')"></datepicker>
                    <show-error :form-name="couponForm" prop-name="valid_end_date"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('coupon.valid_days')}}</label>
                    <v-select label="name" track-by="id" v-model="selected_days" name="valid_days" id="valid_days" :options="days" :placeholder="trans('general.select_days')" @select="onDaySelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onDayRemove" :selected="selected_days">
                    </v-select>
                    <show-error :form-name="couponForm" prop-name="valid_days"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="">{{trans('coupon.max_use_count')}}</label>
                    <input class="form-control" type="text" value="" v-model="couponForm.max_use_count" name="max_use_count" :placeholder="trans('coupon.max_use_count')">
                    <show-error :form-name="couponForm" prop-name="max_use_count"></show-error>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/coupon" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import datepicker from 'vuejs-datepicker'
    import switches from 'vue-switches'
    import vSelect from 'vue-multiselect'
    import currencyInput from '../../components/currency-input'

    export default {
        components: {datepicker,switches,vSelect,currencyInput},
        data() {
            return {
                couponForm: new Form({
                    code : '',
                    discount : '',
                    valid_start_date: '',
                    valid_end_date: '',
                    valid_days: [],
                    new_user: '',
                    max_use_count: '',
                    dollar: 'sss'
                }),
                days: [],
                selected_days: ''
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getCoupon();
            axios.get('/api/fetch/lists?lists=days')
                .then(response => response.data)
                .then(response => {
                    this.days = response.data.days;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateCoupon();
                else
                    this.storeCoupon();
            },
            storeCoupon(){
                this.couponForm.valid_start_date = moment(this.couponForm.valid_start_date).format('YYYY-MM-DD');
                this.couponForm.valid_end_date = moment(this.couponForm.valid_end_date).format('YYYY-MM-DD');
                this.couponForm.post('/api/coupon')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed')
                        this.selected_days = null;
                        this.couponForm.valid_days = [];
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getCoupon(){
                axios.get('/api/coupon/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.couponForm.code = response.coupon.code;
                        this.couponForm.discount = helper.formatNumber(response.coupon.discount);
                        this.couponForm.valid_start_date = response.coupon.valid_start_date;
                        this.couponForm.valid_end_date = response.coupon.valid_end_date;
                        this.couponForm.new_user = response.coupon.new_user;
                        this.couponForm.max_use_count = response.coupon.max_use_count;
                        this.couponForm.valid_days = response.valid_days;
                        this.selected_days = response.selected_days;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/coupon');
                    });
            },
            updateCoupon(){
                this.couponForm.valid_start_date = moment(this.couponForm.valid_start_date).format('YYYY-MM-DD');
                this.couponForm.valid_end_date = moment(this.couponForm.valid_end_date).format('YYYY-MM-DD');
                this.couponForm.patch('/api/coupon/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/coupon');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            onDaySelect(selectedOption){
                this.couponForm.errors.clear('valid_days');
                this.couponForm.valid_days.push(selectedOption.id);
            },
            onDayRemove(removedOption){
                this.couponForm.valid_days.splice(this.couponForm.valid_days.indexOf(removedOption.id), 1);
            }
        }
    }
</script>

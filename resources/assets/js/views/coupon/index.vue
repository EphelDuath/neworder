<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('coupon.coupon')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('coupon.coupon')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <transition name="fade" v-if="hasPermission('create-coupon')">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('coupon.add_new_coupon')}}</h4>
                            <coupon-form @completed="getCoupons"></coupon-form>
                        </div>
                    </div>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="coupons.total && hasPermission('create-coupon') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                        <h4 class="card-title">{{trans('coupon.coupon_list')}}</h4>
                        <h6 class="card-subtitle" v-if="coupons">{{trans('general.total_result_found',{'count' : coupons.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="coupons.total && hasPermission('list-coupon')">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('coupon.code')}}</th>
                                        <th>{{trans('coupon.discount')}}</th>
                                        <th>{{trans('coupon.validity')}}</th>
                                        <th>{{trans('coupon.valid_days')}}</th>
                                        <th>{{trans('coupon.new_user')}}</th>
                                        <th>{{trans('coupon.max_use_count')}}</th>
                                        <th>{{trans('coupon.use_count')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="coupon in coupons.data">
                                        <td v-text="coupon.code"></td>
                                        <td v-text="formatNumber(coupon.discount)"></td>
                                        <td>{{ coupon.valid_start_date | moment}} {{trans('general.to')}} {{ coupon.valid_end_date | moment}}</td>
                                        <td>
                                            <ul style="list-style:none;padding:0px;">
                                                <li v-for="day in coupon.valid_days.split(',')">{{toWord(day)}}</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <i class="fas fa-check" v-if="coupon.new_user"></i>
                                            <i class="fas fa-times" v-else></i>
                                        </td>
                                        <td v-text="coupon.max_use_count"></td>
                                        <td v-text="coupon.use_count"></td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-coupon')" v-tooltip="trans('coupon.edit_coupon')" @click.prevent="editCoupon(coupon)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-coupon')" :key="coupon.id" v-confirm="{ok: confirmDelete(coupon)}" v-tooltip="trans('coupon.delete_coupon')"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!coupons.total" module="coupon" title="module_info_title" description="module_info_description" icon="gift">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-coupon') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterCouponForm.page_length" :records="coupons" @updateRecords="getCoupons"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import couponForm from './form'

    export default {
        components : { couponForm },
        data() {
            return {
                coupons: {
                    total: 0,
                    data: []
                },
                filterCouponForm: {
                    page_length: helper.getConfig('page_length')
                },
                showCreatePanel: false
            };
        },
        mounted(){

            if(!helper.featureAvailable('coupon')){
                helper.featureNotAvailableMsg();
                this.$router.push('/home');
            }

            if(!helper.hasPermission('list-coupon') && !helper.hasPermission('create-coupon')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            
            this.getCoupons();
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getCoupons(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterCouponForm);
                axios.get('/api/coupon?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.coupons = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editCoupon(coupon){
                this.$router.push('/coupon/'+coupon.id+'/edit');
            },
            confirmDelete(coupon){
                return dialog => this.deleteCoupon(coupon);
            },
            deleteCoupon(coupon){
                axios.delete('/api/coupon/'+coupon.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getCoupons();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            formatNumber(number){
                return helper.formatNumber(number);
            },
            toWord(word){
                return helper.toWord(word);
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          }
        }
    }
</script>

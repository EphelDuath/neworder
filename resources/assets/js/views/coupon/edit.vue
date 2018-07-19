<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('coupon.edit_coupon')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/coupon">{{trans('coupon.coupon')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('coupon.edit_coupon')}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('coupon.edit_coupon')}}</h4>
                        <coupon-form :id="id"></coupon-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import couponForm from './form';

    export default {
        components : { couponForm },
        data() {
            return {
                id:this.$route.params.id
            }
        },
        mounted(){
            if(!helper.featureAvailable('coupon')){
                helper.featureNotAvailableMsg();
                this.$router.push('/home');
            }
            
            if(!helper.hasPermission('edit-coupon')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
        }
    }
</script>

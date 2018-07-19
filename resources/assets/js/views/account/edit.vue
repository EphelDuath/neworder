<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('account.edit_account')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/account">{{trans('account.account')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('account.edit_account')}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('account.edit_account')}}</h4>
                        <account-form :id="id"></account-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import accountForm from './form';

    export default {
        components : { accountForm },
        data() {
            return {
                id:this.$route.params.id
            }
        },
        mounted(){
            if(!helper.hasPermission('edit-account')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
        }
    }
</script>

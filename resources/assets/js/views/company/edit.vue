<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('company.edit_company')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/company">{{trans('company.company')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('company.edit_company')}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('company.edit_company')}}</h4>
                        <company-form :id="id"></company-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import companyForm from './form';

    export default {
        components : { companyForm },
        data() {
            return {
                id:this.$route.params.id
            }
        },
        mounted(){
            if(!helper.hasPermission('edit-company')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
        }
    }
</script>

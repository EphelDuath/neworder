<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('designation.edit_designation')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/designation">{{trans('designation.designation')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('designation.edit_designation')}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('designation.edit_designation')}}</h4>
                        <designation-form :id="id"></designation-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import designationForm from './form';

    export default {
        components : { designationForm },
        data() {
            return {
                id:this.$route.params.id
            }
        },
        mounted(){
            if(!helper.hasPermission('edit-designation')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
        }
    }
</script>

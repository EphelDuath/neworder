<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('quotation.edit_quotation')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/quotation">{{trans('quotation.quotation')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('quotation.edit_quotation')}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <quotation-form :uuid="uuid"></quotation-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import quotationForm from './form';

    export default {
        components : { quotationForm },
        data() {
            return {
                uuid:this.$route.params.uuid
            }
        },
        mounted(){
            if(!helper.hasPermission('edit-quotation')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
        }
    }
</script>

<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('invoice.invoice')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/invoice">{{trans('invoice.invoice')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('invoice.create_invoice')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12">
                                <invoice-form @completed=""></invoice-form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import invoiceForm from './form'

    export default {
        components: {invoiceForm},
        mounted(){
            if(!helper.hasPermission('create-invoice')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
        }
    }
</script>

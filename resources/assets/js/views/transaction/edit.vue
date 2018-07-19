<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('transaction.edit_transaction',{type: trans('transaction.'+type)})}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('transaction.edit_transaction',{type: trans('transaction.'+type)})}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('transaction.edit_transaction',{type: trans('transaction.'+type)})}}</h4>
                        <transaction-form :uuid="uuid" :type="type"></transaction-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import transactionForm from './form';

    export default {
        components : { transactionForm },
        data() {
            return {
                type: this.$route.params.type,
                uuid: this.$route.params.uuid
            }
        },
        mounted(){
            if(!helper.hasPermission('list-'+this.type)){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }
            this.validateType();
        },
        methods: {
            validateType(){
                if(this.type !== 'income' && this.type !== 'expense' && this.type !== 'account-transfer')
                    this.$router.push('/transaction/income');
            }
        }
    }
</script>

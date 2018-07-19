<template>
    <form @submit.prevent="proceed" @keydown="customerGroupForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('customer_group.name')}}</label>
            <input class="form-control" type="text" value="" v-model="customerGroupForm.name" name="name" :placeholder="trans('customer_group.name')">
            <show-error :form-name="customerGroupForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('customer_group.description')}}</label>
            <textarea class="form-control" type="text" value="" v-model="customerGroupForm.description" rows="2" name="description" :placeholder="trans('customer_group.description')"></textarea>
            <show-error :form-name="customerGroupForm" prop-name="description"></show-error>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/customer-group" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import switches from 'vue-switches'

    export default {
        components: {switches},
        data() {
            return {
                customerGroupForm: new Form({
                    name : '',
                    description : ''
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getCustomerGroup();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateCustomerGroup();
                else
                    this.storeCustomerGroup();
            },
            storeCustomerGroup(){
                this.customerGroupForm.post('/api/customer-group')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getCustomerGroup(){
                axios.get('/api/customer-group/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.customerGroupForm.name = response.name;
                        this.customerGroupForm.description = response.description;
                    })
                    .catch(error => {
                        this.$router.push('/configuration/customer-group');
                    });
            },
            updateCustomerGroup(){
                this.customerGroupForm.patch('/api/customer-group/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/customer-group');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

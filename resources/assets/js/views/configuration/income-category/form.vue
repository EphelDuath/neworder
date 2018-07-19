<template>
    <form @submit.prevent="proceed" @keydown="incomeCategoryForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('transaction.transaction_category_name')}}</label>
            <input class="form-control" type="text" value="" v-model="incomeCategoryForm.name" name="name" :placeholder="trans('transaction.transaction_category_name')">
            <show-error :form-name="incomeCategoryForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('transaction.transaction_category_description')}}</label>
            <textarea class="form-control" type="text" value="" v-model="incomeCategoryForm.description" rows="2" name="description" :placeholder="trans('transaction.transaction_category_description')"></textarea>
            <show-error :form-name="incomeCategoryForm" prop-name="description"></show-error>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/income-category" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    export default {
        components: {},
        data() {
            return {
                incomeCategoryForm: new Form({
                    name : '',
                    description : ''
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getIncomeCategory();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateIncomeCategory();
                else
                    this.storeIncomeCategory();
            },
            storeIncomeCategory(){
                this.incomeCategoryForm.post('/api/income-category')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getIncomeCategory(){
                axios.get('/api/income-category/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.incomeCategoryForm.name = response.name;
                        this.incomeCategoryForm.description = response.description;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/configuration/income-category');
                    });
            },
            updateIncomeCategory(){
                this.incomeCategoryForm.patch('/api/income-category/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/income-category');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

<template>
    <form @submit.prevent="proceed" @keydown="expenseCategoryForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('transaction.transaction_category_name')}}</label>
            <input class="form-control" type="text" value="" v-model="expenseCategoryForm.name" name="name" :placeholder="trans('transaction.transaction_category_name')">
            <show-error :form-name="expenseCategoryForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('transaction.transaction_category_description')}}</label>
            <textarea class="form-control" type="text" value="" v-model="expenseCategoryForm.description" rows="2" name="description" :placeholder="trans('transaction.transaction_category_description')"></textarea>
            <show-error :form-name="expenseCategoryForm" prop-name="description"></show-error>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/expense-category" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    export default {
        components: {},
        data() {
            return {
                expenseCategoryForm: new Form({
                    name : '',
                    description : ''
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getExpenseCategory();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateExpenseCategory();
                else
                    this.storeExpenseCategory();
            },
            storeExpenseCategory(){
                this.expenseCategoryForm.post('/api/expense-category')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getExpenseCategory(){
                axios.get('/api/expense-category/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.expenseCategoryForm.name = response.name;
                        this.expenseCategoryForm.description = response.description;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/configuration/expense-category');
                    });
            },
            updateExpenseCategory(){
                this.expenseCategoryForm.patch('/api/expense-category/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/expense-category');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

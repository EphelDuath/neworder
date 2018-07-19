<template>
    <form @submit.prevent="proceed" @keydown="itemCategoryForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('item.item_category_name')}}</label>
            <input class="form-control" type="text" value="" v-model="itemCategoryForm.name" name="name" :placeholder="trans('item.item_category_name')">
            <show-error :form-name="itemCategoryForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <div class="radio radio-success">
                <input type="radio" value="product" id="type_product" v-model="itemCategoryForm.type" :checked="itemCategoryForm.type === 'product'" name="type" @click="itemCategoryForm.errors.clear('type')">
                <label for="type_product">{{trans('item.product')}}</label>
            </div>
            <div class="radio radio-success">
                <input type="radio" value="service" id="type_service" v-model="itemCategoryForm.type" :checked="itemCategoryForm.type === 'service'" name="type" @click="itemCategoryForm.errors.clear('type')">
                <label for="type_service">{{trans('item.service')}}</label>
            </div>
            <show-error :form-name="itemCategoryForm" prop-name="type"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('item.item_category_description')}}</label>
            <textarea class="form-control" type="text" value="" v-model="itemCategoryForm.description" rows="2" name="description" :placeholder="trans('item.item_category_description')"></textarea>
            <show-error :form-name="itemCategoryForm" prop-name="description"></show-error>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/item-category" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    export default {
        components: {},
        data() {
            return {
                itemCategoryForm: new Form({
                    name : '',
                    type : '',
                    description : ''
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getItemCategory();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateItemCategory();
                else
                    this.storeItemCategory();
            },
            storeItemCategory(){
                this.itemCategoryForm.post('/api/item-category')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getItemCategory(){
                axios.get('/api/item-category/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.itemCategoryForm.name = response.name;
                        this.itemCategoryForm.description = response.description;
                        this.itemCategoryForm.type = response.type;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/configuration/item-category');
                    });
            },
            updateItemCategory(){
                this.itemCategoryForm.patch('/api/item-category/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/item-category');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

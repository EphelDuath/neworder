<template>
    <form @submit.prevent="proceed" @keydown="itemForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('item.item_name')}}</label>
                            <input class="form-control" type="text" value="" v-model="itemForm.name" name="name" :placeholder="trans('item.item_name')">
                            <show-error :form-name="itemForm" prop-name="name"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('item.item_code')}}</label>
                            <input class="form-control" type="text" value="" v-model="itemForm.code" name="code" :placeholder="trans('item.item_code')">
                            <show-error :form-name="itemForm" prop-name="code"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-12">
                        <div class="form-group">
                            <label for="">{{trans('item.item_category')}}</label>
                            <v-select label="name" v-model="selected_item_category" name="item_category_id" id="item_category_id" :options="item_categories" :placeholder="trans('item.select_item_category')" @select="onItemCategorySelect" @close="itemForm.errors.clear('item_category_id')" @remove="itemForm.item_category_id = ''"></v-select>
                            <show-error :form-name="itemForm" prop-name="item_category_id"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('taxation.taxation')}}</label>
                            <v-select label="name" v-model="selected_taxation" name="taxation_id" id="taxation_id" :options="taxations" :placeholder="trans('taxation.select_taxation')" @select="onTaxationSelect" @close="itemForm.errors.clear('taxation_id')" @remove="itemForm.taxation_id = ''"></v-select>
                            <show-error :form-name="itemForm" prop-name="taxation_id"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('item.discount')}}</label>
                            <input class="form-control" type="text" value="" v-model="itemForm.discount" name="discount" :placeholder="trans('item.discount')">
                            <show-error :form-name="itemForm" prop-name="discount"></show-error>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">{{trans('item.item_description')}}</label>
                    <textarea class="form-control" type="text" value="" v-model="itemForm.description" rows="2" name="description" :placeholder="trans('item.item_description')"></textarea>
                    <show-error :form-name="itemForm" prop-name="description"></show-error>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6" v-for="price in itemForm.price">
                        <div class="form-group">
                            <label for="">{{price.currency.name}}</label>
                            <currency-input :position="price.currency.position" :symbol="price.currency.symbol" :name="'item_price_'+price.id" :placeholder="trans('item.price_in',{currency:price.currency.name})" v-model="price.unit_price" @input.native="itemForm.errors.clear('item_price_'+price.id)"></currency-input>
                            <show-error :form-name="itemForm" :prop-name="'item_price_'+price.id"></show-error>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/item" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import vSelect from 'vue-multiselect'
    import currencyInput from '../../components/currency-input'

    export default {
        components: {vSelect,currencyInput},
        data() {
            return {
                itemForm: new Form({
                    name : '',
                    code : '',
                    item_category_id: '',
                    taxation_id: '',
                    discount: 0,
                    price: [],
                    description: ''
                }),
                currencies: [],
                item_categories: [],
                taxations: [],
                selected_item_category: null,
                selected_taxation: null,
                prices: {}
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getItem();
            axios.get('/api/item/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.item_categories = response.item_categories;
                    this.taxations = response.taxations;
                    this.currencies = response.currencies;
                    this.initPrice();
                    this.prices = response.fields;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            initPrice(){
                this.currencies.forEach(currency => {
                    this.itemForm.price.push({
                        id: currency.id,
                        currency: currency,
                        unit_price: ''
                    });
                });
            },
            onItemCategorySelect(selectedOption){
                this.itemForm.item_category_id = selectedOption.id;
            },
            onTaxationSelect(selectedOption){
                this.itemForm.taxation_id = selectedOption.id;
            },
            proceed(){
                if(this.id)
                    this.updateItem();
                else
                    this.storeItem();
            },
            storeItem(){
                this.itemForm.discount = (this.itemForm.discount) || 0;
                this.itemForm.post('/api/item')
                    .then(response => {
                        toastr.success(response.message);
                        this.selected_taxation = '';
                        this.selected_item_category = '';
                        this.itemForm.price = [];
                        this.initPrice();
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getItem(){
                axios.get('/api/item/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.itemForm.name = response.item.name;
                        this.itemForm.code = response.item.code;
                        this.itemForm.item_category_id = response.item.item_category_id;
                        this.itemForm.taxation_id = response.item.taxation_id;
                        this.itemForm.discount = helper.formatNumber(response.item.discount);
                        this.itemForm.description = response.item.description;
                        this.selected_item_category = response.selected_item_category;
                        this.selected_taxation = response.selected_taxation;
                        this.itemForm.price.forEach(function(price) {
                            let pricing = response.item.item_price.filter(pricing => pricing.currency_id == price.id);
                            if(pricing.length)
                                price.unit_price = helper.formatNumber(pricing[0].unit_price);
                        });
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/item');
                    });
            },
            updateItem(){
                this.itemForm.discount = (this.itemForm.discount) || 0;
                this.itemForm.patch('/api/item/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/item');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        },
        watch: {
            currencies(val){
                if(!val.length){
                    toastr.error(i18n.currency.no_currency_found);
                    this.$router.push('/home');
                }
            }
        }
    }
</script>

<template>
    <form @submit.prevent="proceed" @keydown="currencyForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('currency.name')}} </label>
            <input class="form-control" type="text" value="" v-model="currencyForm.name" name="name" :placeholder="trans('currency.name')">
            <show-error :form-name="currencyForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('currency.symbol')}}</label>
            <input class="form-control" type="text" value="" v-model="currencyForm.symbol" name="symbol" :placeholder="trans('currency.symbol')">
            <show-error :form-name="currencyForm" prop-name="symbol"></show-error>
        </div>
        <div class="form-group">
            <div class="radio radio-success">
                <input type="radio" value="prefix" id="position_prefix" v-model="currencyForm.position" :checked="currencyForm.position === 'prefix'" name="position" @click="currencyForm.errors.clear('position')">
                <label for="position_prefix">{{trans('currency.prefix')}}</label>
            </div>
            <div class="radio radio-success">
                <input type="radio" value="suffix" id="position_suffix" v-model="currencyForm.position" :checked="currencyForm.position === 'suffix'" name="position" @click="currencyForm.errors.clear('position')">
                <label for="position_suffix">{{trans('currency.suffix')}}</label>
            </div>
            <show-error :form-name="currencyForm" prop-name="position"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('currency.decimal_place')}}</label>
            <input class="form-control" type="number" value="" v-model="currencyForm.decimal_place" name="decimal_place" :placeholder="trans('currency.decimal_place')">
            <show-error :form-name="currencyForm" prop-name="decimal_place"></show-error>
        </div>
        <div class="form-group">
            <switches class="m-l-20" v-model="currencyForm.is_default" theme="bootstrap" color="success"></switches> {{trans('currency.default')}}
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/currency" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import switches from 'vue-switches'

    export default {
        components: {switches},
        data() {
            return {
                currencyForm: new Form({
                    name : '',
                    symbol : '',
                    position: '',
                    decimal_place: '',
                    is_default: false
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getCurrency();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateCurrency();
                else
                    this.storeCurrency();
            },
            storeCurrency(){
                this.currencyForm.post('/api/currency')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getCurrency(){
                axios.get('/api/currency/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.currencyForm.name = response.name;
                        this.currencyForm.symbol = response.symbol;
                        this.currencyForm.position = response.position;
                        this.currencyForm.decimal_place = response.decimal_place;
                        this.currencyForm.is_default = response.is_default;
                    })
                    .catch(error => {
                        this.$router.push('/configuration/currency');
                    });
            },
            updateCurrency(){
                this.currencyForm.patch('/api/currency/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/currency');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

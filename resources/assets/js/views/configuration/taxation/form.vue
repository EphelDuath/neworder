<template>
    <form @submit.prevent="proceed" @keydown="taxationForm.errors.clear($event.target.name)">
        <div class="form-group">
            <label for="">{{trans('taxation.name')}}</label>
            <input class="form-control" type="text" value="" v-model="taxationForm.name" name="name" :placeholder="trans('taxation.name')">
            <show-error :form-name="taxationForm" prop-name="name"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('taxation.value')}}</label>
            <div class="input-group">
                <input class="form-control" type="text" value="" v-model="taxationForm.value" name="value" :placeholder="trans('taxation.value')">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
            </div>
            <show-error :form-name="taxationForm" prop-name="value"></show-error>
        </div>
        <div class="form-group">
            <label for="">{{trans('taxation.description')}}</label>
            <textarea class="form-control" type="text" value="" v-model="taxationForm.description" rows="2" name="description" :placeholder="trans('taxation.description')"></textarea>
            <show-error :form-name="taxationForm" prop-name="description"></show-error>
        </div>
        <div class="form-group">
            <switches class="m-l-20" v-model="taxationForm.is_default" theme="bootstrap" color="success"></switches> {{trans('taxation.default')}}
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/configuration/taxation" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import switches from 'vue-switches'

    export default {
        components: {switches},
        data() {
            return {
                taxationForm: new Form({
                    name : '',
                    description : '',
                    value: '',
                    is_default: false
                })
            };
        },
        props: ['id'],
        mounted() {
            if(this.id)
                this.getTaxation();
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateTaxation();
                else
                    this.storeTaxation();
            },
            storeTaxation(){
                this.taxationForm.post('/api/taxation')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getTaxation(){
                axios.get('/api/taxation/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.taxationForm.name = response.name;
                        this.taxationForm.description = response.description;
                        this.taxationForm.value = helper.formatNumber(response.value);
                        this.taxationForm.is_default = response.is_default;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                        this.$router.push('/configuration/taxation');
                    });
            },
            updateTaxation(){
                this.taxationForm.patch('/api/taxation/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/configuration/taxation');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

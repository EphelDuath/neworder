<template>
    <div class="input-group">
        <div class="input-group-prepend" v-if="position === 'prefix'"><span class="input-group-text">{{symbol}}</span></div>
        <input class="form-control" type="text" :name="name" :placeholder="placeholder" ref="input" :value="value" @input="updateValue">
        <div class="input-group-append" v-if="position === 'suffix'"><span class="input-group-text">{{symbol}}</span></div>
    </div>
</template>

<script>
    export default {
        props: {
            position:{
                required: true
            },
            symbol:{
                required: true
            },
            name:{
                required: true
            },
            placeholder:{
                default: ''
            },
            value:{

            }
        },
        mounted(){
        },
        methods: {
            updateValue(){
                this.$emit('input',this.$refs.input.value);
            }
        }
    }
</script>

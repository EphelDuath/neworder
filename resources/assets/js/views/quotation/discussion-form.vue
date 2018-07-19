<template>
    <div class="row">
        <div class="col-12">
            <form @submit.prevent="submit" @keydown="quotationDiscussionForm.errors.clear($event.target.name)" class="">
                <div class="form-group">
                    <input class="form-control" type="text" value="" v-model="quotationDiscussionForm.comment" name="comment" :placeholder="(type === 'reply') ? trans('quotation.type_comment',{type:trans('quotation.reply')}) : trans('quotation.type_comment',{type:trans('quotation.comment')})">
                    <show-error :form-name="quotationDiscussionForm" prop-name="comment"></show-error>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        components: {},
        data(){
            return {
                comments: {},
                quotationDiscussionForm: new Form({
                    comment: '',
                    comment_id: null
                })
            }
        },
        props: ['uuid','id','type'],
        mounted(){
        },
        methods: {
            submit(){
                this.quotationDiscussionForm.comment_id = this.id;
                this.quotationDiscussionForm.post('/api/quotation/'+this.uuid+'/discussion')
                    .then(response => {
                        toastr.success(response.message);
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            }
        }
    }
</script>

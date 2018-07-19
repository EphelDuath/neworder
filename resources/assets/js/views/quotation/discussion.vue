<template>
    <div class="card" v-if="showPanel">
        <div class="card-body">
            <button class="btn btn-info btn-sm pull-right" v-if="showPanel" @click="toggle">{{trans('general.hide')}}</button>
            <h4 class="card-title">{{trans('quotation.discussion')}}</h4>
            <discussion-form :uuid="uuid" @completed="getComments"></discussion-form>

            <div class="comment-widgets message-scroll" v-if="comments.length">
                <div class="d-flex flex-row comment-row" v-for="comment in comments">
                    <div class="p-2"><span class="round"><img :src="getAvatar(comment.user)" alt="user" width="50"></span></div>
                    <div class="comment-text w-100">
                        <span class="pull-right" style="cursor: pointer;margin-left: 15px;" v-if="comment.user_id == getAuthUser('id')" :key="comment.id" v-confirm="{ok: confirmDelete(comment)}" v-tooltip="trans('quotation.delete_comment')"><i class="fas fa-trash"></i></span>
                        <span class="text-muted pull-right" style="font-size: 12px;"><i class="far fa-clock"></i> {{comment.created_at | momentFromNow}}</span>
                        <h5>{{comment.user.profile.first_name+' '+comment.user.profile.last_name}}</h5>
                        <p class="m-b-5" v-html="comment.comment"></p>
                        <div class="" style="padding-left:25px;margin-bottom:-30px;">
                            <discussion-form :uuid="uuid" :id="comment.id" @completed="getComments" type="reply"></discussion-form>
                        </div>
                        <div class="d-flex flex-row comment-row" v-if="comment.reply" v-for="reply in orderedReplies(comment.reply)" style="margin-bottom: -40px;padding-right: 0;">
                            <div class="p-2"><span class="round"><img :src="getAvatar(reply.user)" alt="user" width="50"></span></div>
                            <div class="comment-text w-100">
                                <span class="pull-right" style="cursor: pointer;margin-left: 15px;" v-if="comment.user_id == getAuthUser('id')" :key="reply.id" v-confirm="{ok: confirmDelete(reply)}" v-tooltip="trans('quotation.delete_comment')"><i class="fas fa-trash"></i></span>
                                <span class="text-muted pull-right" style="font-size: 12px;"><i class="far fa-clock"></i> {{reply.created_at | momentFromNow}}</span>
                                <h5>{{reply.user.profile.first_name+' '+reply.user.profile.last_name}}</h5>
                                <p class="m-b-5" v-html="reply.comment"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h6 class="card-subtitle" v-if="!comments.length">{{trans('general.no_result_found')}}</h6>
        </div>
    </div>
</template>

<script>
    import discussionForm from './discussion-form'

    export default {
        components: {discussionForm},
        data(){
            return {
                comments: {}
            }
        },
        props: ['uuid','showPanel'],
        mounted(){
            this.getComments();
        },
        methods: {
            toggle(){
                this.$emit('toggle');
            },
            getComments(){
                axios.get('/api/quotation/'+this.uuid+'/discussion')
                    .then(response => response.data)
                    .then(response => {
                        this.comments = response;
                    })
                    .catch(error => {

                    });
            },
            getAvatar(user){
                if(user.profile.avatar)
                    return '/'+user.profile.avatar;
                else
                    return '/images/avatar.png';
            },
            orderedReplies(replies) {
                return _orderBy(replies, 'created_at','desc')
            },
            confirmDelete(comment){
                return dialog => this.deleteComment(comment);
            },
            deleteComment(comment){
                axios.delete('/api/quotation/'+this.uuid+'/discussion/'+comment.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getComments();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getAuthUser(name){
                return helper.getAuthUser(name);
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          },
          momentDateTime(datetime) {
            return helper.formatDateTime(datetime);
          },
          momentFromNow(datetime){
            return helper.formatDateTimeFromNow(datetime);
          }
        },
        computed: {
        },
        watch: {

        }
    }
</script>

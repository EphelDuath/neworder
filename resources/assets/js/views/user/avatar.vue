<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('user.user_detail',{type: trans('user.'+type)})}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item"><router-link :to="`/user/${type}`">{{trans('user.'+type)}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('user.avatar')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-8 col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <user-sidebar menu="avatar" :type="type" :id="id"></user-sidebar>
                            <div class="col-9 col-lg-9 col-md-9">
                                <h4 class="card-title">{{trans('user.avatar')}}</h4>
                                <upload-image id="avatar" :upload-path="`/user/profile/avatar/${id}`" :remove-path="`/user/profile/avatar/remove/${id}`" :image-source="avatar.user" @uploaded="updateAvatar" @removed="updateAvatar"></upload-image>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <user-summary :user="user" :type="type"></user-summary>
        </div>
    </div>
</template>


<script>
    import userSidebar from './user-sidebar'
    import uploadImage from '../../components/upload-image'
    import userSummary from './summary'

    export default {
        components : { userSidebar,uploadImage,userSummary },
        data() {
            return {
                id:this.$route.params.id,
                type:this.$route.params.type,
                user: '',
                avatar: {
                    user: ''
                }
            };
        },
        mounted(){
            if(this.type !== 'customer' && this.type !== 'staff')
                this.$router.push('/user/staff');

            if(!helper.hasPermission('edit-'+this.type)){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            axios.get('/api/user/'+this.type+'/'+this.id)
                .then(response => response.data)
                .then(response => {
                    this.user = response.user;
                    this.avatar.user = response.user.profile.avatar;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                    this.$router.push('/user/staff');
                })
        },
        methods: {
            updateAvatar(val){
                if(helper.getAuthUser('id') == this.id){
                    this.$store.dispatch('setAuthUserDetail',{
                        avatar: val
                    });
                }
                this.user.profile.avatar = val;
            }
        }
    }
</script>

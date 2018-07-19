<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('announcement.announcement')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">{{trans('general.home')}}</router-link></li>
                    <li class="breadcrumb-item active">{{trans('announcement.announcement')}}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <transition name="fade" v-if="hasPermission('create-announcement')">
                    <div class="card" v-if="showCreatePanel">
                        <div class="card-body">
                            <button class="btn btn-info btn-sm pull-right" v-if="showCreatePanel" @click="showCreatePanel = !showCreatePanel">{{trans('general.hide')}}</button>
                            <h4 class="card-title">{{trans('announcement.add_new_announcement')}}</h4>
                            <announcement-form @completed="getAnnouncements"></announcement-form>
                        </div>
                    </div>
                </transition>
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-info btn-sm pull-right" v-if="announcements.total && !showCreatePanel && hasPermission('create-announcement')" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                        <h4 class="card-title">{{trans('announcement.announcement_list')}}</h4>
                        <h6 class="card-subtitle" v-if="announcements">{{trans('general.total_result_found',{'count' : announcements.total})}}</h6>
                        <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="hasPermission('list-announcement') && announcements.total">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('announcement.title')}}</th>
                                        <th>{{trans('announcement.audience')}}</th>
                                        <th>{{trans('announcement.duration')}}</th>
                                        <th class="table-option">{{trans('general.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="announcement in announcements.data">
                                        <td v-text="announcement.title"></td>
                                        <td>
                                            {{getAudience(announcement)}}
                                            <ul v-if="announcement.audience === 'staff'">
                                                <li v-for="designation in announcement.designation">{{designation.name}}</li>
                                            </ul>
                                        </td>
                                        <td>{{ announcement.start_date | moment}} {{trans('general.to')}} {{ announcement.end_date | moment}}</td>
                                        <td class="table-option">
                                            <div class="btn-group">
                                                <button class="btn btn-success btn-sm" v-tooltip="trans('announcement.view_announcement')" @click="id = announcement.id" data-toggle="modal" data-target=".announcement-detail"><i class="fas fa-arrow-circle-right"></i></button>
                                                <button class="btn btn-info btn-sm" v-if="hasPermission('edit-announcement')" v-tooltip="trans('announcement.edit_announcement')" @click.prevent="editAnnouncement(announcement)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" v-if="hasPermission('delete-announcement')" :key="announcement.id" v-confirm="{ok: confirmDelete(announcement)}" v-tooltip="trans('announcement.delete_announcement')"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <module-info v-if="!announcements.total" module="announcement" title="module_info_title" description="module_info_description" icon="bullhorn">
                            <div slot="btn">
                                <button class="btn btn-info btn-md" v-if="hasPermission('create-announcement') && !showCreatePanel" @click="showCreatePanel = !showCreatePanel"><i class="fas fa-plus"></i> {{trans('general.add_new')}}</button>
                            </div>
                        </module-info>
                        <pagination-record :page-length.sync="filterAnnouncementForm.page_length" :records="announcements" @updateRecords="getAnnouncements"></pagination-record>
                    </div>
                </div>
            </div>
        </div>
        <announcement-detail :id="id"></announcement-detail>
    </div>
</template>


<script>
    import announcementForm from './form'
    import announcementDetail from './detail'

    export default {
        components : { announcementForm,announcementDetail },
        data() {
            return {
                announcements: {
                    total: 0,
                    data: []
                },
                filterAnnouncementForm: {
                    page_length: helper.getConfig('page_length')
                },
                id: '',
                showCreatePanel: false
            };
        },
        mounted(){
            if(!helper.featureAvailable('announcement')){
                helper.featureNotAvailableMsg();
                this.$router.push('/home');
            }
            
            if(!helper.hasPermission('list-announcement') && !helper.hasPermission('create-announcement')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            this.getAnnouncements();
        },
        methods: {
            hasPermission(permission){
                return helper.hasPermission(permission);
            },
            getAnnouncements(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterAnnouncementForm);
                axios.get('/api/announcement?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.announcements = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            editAnnouncement(announcement){
                this.$router.push('/announcement/'+announcement.id+'/edit');
            },
            confirmDelete(announcement){
                return dialog => this.deleteAnnouncement(announcement);
            },
            deleteAnnouncement(announcement){
                axios.delete('/api/announcement/'+announcement.id)
                    .then(response => response.data)
                    .then(response => {
                        toastr.success(response.message);
                        this.getAnnouncements();
                    }).catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            getAudience(announcement){
                return helper.ucword(announcement.audience);
            }
        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          }
        }
    }
</script>

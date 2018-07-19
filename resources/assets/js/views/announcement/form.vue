<template>
    <form @submit.prevent="proceed" @keydown="announcementForm.errors.clear($event.target.name)">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">{{trans('announcement.title')}}</label>
                    <input class="form-control" type="text" value="" v-model="announcementForm.title" name="title" :placeholder="trans('announcement.title')">
                    <show-error :form-name="announcementForm" prop-name="title"></show-error>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('announcement.start_date')}}</label>
                            <datepicker v-model="announcementForm.start_date" :bootstrapStyling="true" @selected="announcementForm.errors.clear('start_date')" :placeholder="trans('announcement.start_date')"></datepicker>
                            <show-error :form-name="announcementForm" prop-name="start_date"></show-error>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">{{trans('announcement.end_date')}}</label>
                            <datepicker v-model="announcementForm.end_date" :bootstrapStyling="true" @selected="announcementForm.errors.clear('end_date')" :placeholder="trans('announcement.end_date')"></datepicker>
                            <show-error :form-name="announcementForm" prop-name="end_date"></show-error>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="radio radio-success">
                        <input type="radio" value="customer" id="audience_customer" v-model="announcementForm.audience" :checked="announcementForm.audience === 'customer'" name="audience" @click="announcementForm.errors.clear('audience')">
                        <label for="audience_customer">{{trans('announcement.customer')}}</label>
                    </div>
                    <div class="radio radio-success">
                        <input type="radio" value="staff" id="audience_staff" v-model="announcementForm.audience" :checked="announcementForm.audience === 'staff'" name="audience" @click="announcementForm.errors.clear('audience')">
                        <label for="audience_staff">{{trans('announcement.staff')}}</label>
                    </div>
                    <show-error :form-name="announcementForm" prop-name="audience"></show-error>
                </div>
                <div class="form-group" v-if="announcementForm.audience === 'staff' ">
                    <label for="">{{trans('designation.designation')}}</label>
                    <v-select label="name" track-by="id" v-model="selected_designations" name="designation_id" id="designation_id" :options="designations" :placeholder="trans('designation.select_designation')" @select="onDesignationSelect" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" @remove="onDesignationRemove" :selected="selected_designations">
                    </v-select>
                    <show-error :form-name="announcementForm" prop-name="designation_id"></show-error>
                </div>
                <div class="form-group">
                    <file-upload-input :button-text="trans('general.upload_document')" :token="announcementForm.upload_token" module="announcement" :clear-file="clear_announcement_attachment" :module-id="id || ''"></file-upload-input>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <html-editor name="description" :model.sync="announcementForm.description" :isUpdate="id ? true : false" @clearErrors="announcementForm.errors.clear('description')"></html-editor>
                <show-error :form-name="announcementForm" prop-name="description"></show-error>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light pull-right">
            <span v-if="id">{{trans('general.update')}}</span>
            <span v-else>{{trans('general.save')}}</span>
        </button>
        <router-link to="/announcement" class="btn btn-danger waves-effect waves-light pull-right m-r-10" v-show="id">{{trans('general.cancel')}}</router-link>
    </form>
</template>


<script>
    import uuid from 'uuid/v4'
    import htmlEditor from '../../components/html-editor'
    import fileUploadInput from '../../components/file-upload-input'
    import datepicker from 'vuejs-datepicker'
    import vSelect from 'vue-multiselect'

    export default {
        components: {datepicker,vSelect,htmlEditor,fileUploadInput},
        data() {
            return {
                announcementForm: new Form({
                    title : '',
                    description : '',
                    start_date: '',
                    end_date: '',
                    audience: '',
                    designation_id: [],
                    upload_token: ''
                }),
                designations: [],
                selected_designations: '',
                clear_announcement_attachment: false
            };
        },
        props: ['id'],
        mounted() {
            if(!this.id)
            this.announcementForm.upload_token = uuid();
            if(this.id)
                this.getAnnouncement();
            axios.get('/api/announcement/pre-requisite')
                .then(response => response.data)
                .then(response => {
                    this.designations = response;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                });
        },
        methods: {
            proceed(){
                if(this.id)
                    this.updateAnnouncement();
                else
                    this.storeAnnouncement();
            },
            storeAnnouncement(){
                this.announcementForm.start_date = moment(this.announcementForm.start_date).format('YYYY-MM-DD');
                this.announcementForm.end_date = moment(this.announcementForm.end_date).format('YYYY-MM-DD');
                this.announcementForm.post('/api/announcement')
                    .then(response => {
                        toastr.success(response.message);
                        this.announcementForm.description = '';
                        this.announcementForm.upload_token = uuid();
                        this.clear_announcement_attachment = true;
                        this.selected_designations = null;
                        this.announcementForm.designation_id = [];
                        this.$emit('completed');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            getAnnouncement(){
                axios.get('/api/announcement/'+this.id)
                    .then(response => response.data)
                    .then(response => {
                        this.announcementForm.title = response.announcement.title;
                        this.announcementForm.description = response.announcement.description;
                        this.announcementForm.start_date = response.announcement.start_date;
                        this.announcementForm.end_date = response.announcement.end_date;
                        this.announcementForm.audience = response.announcement.audience;
                        this.announcementForm.upload_token = response.announcement.upload_token;
                        this.announcementForm.designation_id = response.designation_id;
                        this.selected_designations = response.selected_designations;
                    })
                    .catch(error => {
                        this.$router.push('/announcement');
                    });
            },
            updateAnnouncement(){
                this.announcementForm.start_date = moment(this.announcementForm.start_date).format('YYYY-MM-DD');
                this.announcementForm.end_date = moment(this.announcementForm.end_date).format('YYYY-MM-DD');
                this.announcementForm.patch('/api/announcement/'+this.id)
                    .then(response => {
                        toastr.success(response.message);
                        this.$router.push('/announcement');
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            onDesignationSelect(selectedOption){
                this.announcementForm.errors.clear('designation_id');
                this.announcementForm.designation_id.push(selectedOption.id);
            },
            onDesignationRemove(removedOption){
                this.announcementForm.designation_id.splice(this.announcementForm.designation_id.indexOf(removedOption.id), 1);
            }
        }
    }
</script>

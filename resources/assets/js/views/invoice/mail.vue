<template>
    <div class="card" v-if="is_staff && showPanel && getConfig('email_template')">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 col-md-6 col-sm-6">
                    <h4 class="card-title">{{trans('invoice.email')}}</h4>
                    <form @submit.prevent="sendEmail" @keydown="emailForm.errors.clear($event.target.name)">
                        <div class="form-group">
                            <label for="">{{trans('template.template')}}</label>
                            <v-select label="name" v-model="selected_template" name="template_id" id="template_id" :options="templates" :placeholder="trans('template.select_template')" @select="getTemplateContent" @close="emailForm.errors.clear('template_id')" @remove="emailForm.template_id = ''"></v-select>
                            <show-error :form-name="emailForm" prop-name="template_id"></show-error>
                        </div>
                        <div class="form-group">
                            <label for="">{{trans('template.subject')}}</label>
                            <input class="form-control" type="text" value="" v-model="emailForm.subject" name="subject" :placeholder="trans('user.subject')">
                            <show-error :form-name="emailForm" prop-name="subject"></show-error>
                        </div>
                        <div class="form-group">
                            <html-editor name="body" :model.sync="emailForm.body" isUpdate="true" :reload-content="reload_content" @clearErrors="emailForm.errors.clear('body')"></html-editor>
                            <show-error :form-name="emailForm" prop-name="body"></show-error>
                        </div>
                        <button type="submit" class="btn btn-sm btn-info pull-right">{{trans('message.send')}}</button>
                    </form>
                </div>
                <div class="col-12 col-lg-6 col-md-6 col-sm-6">
                    <button class="btn btn-info btn-sm pull-right" v-if="showPanel" @click="toggle">{{trans('general.hide')}}</button>
                    <h4 class="card-title">{{trans('mail.email_log')}}</h4>
                    <h6 class="card-subtitle" v-if="email_logs">{{trans('general.total_result_found',{'count' : email_logs.total})}}</h6>
                    <h6 class="card-subtitle" v-else>{{trans('general.no_result_found')}}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" v-if="email_logs.total">
                            <thead>
                                <tr>
                                    <th>{{trans('mail.receiver')}}</th>
                                    <th>{{trans('mail.subject')}}</th>
                                    <th>{{trans('mail.sent_at')}}</th>
                                    <th class="table-option-sm">{{trans('general.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="email_log in email_logs.data">
                                    <td v-text="email_log.from_address"></td>
                                    <td v-text="email_log.subject"></td>
                                    <td>{{email_log.created_at | momentDateTime}}</td>
                                    <td class="table-option-sm"><button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".email-log-detail" @click="fetchEmailDetail(email_log)" v-tooltip="trans('mail.view')"><i class="fas fa-arrow-circle-right"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <pagination-record :page-length.sync="filterEmailLogForm.page_length" :records="email_logs" @updateRecords="getEmailLogs" @change.native="getEmailLogs"></pagination-record>
                </div>
            </div>
        </div>
        <div class="modal fade email-log-detail" tabindex="-1" role="dialog" aria-labelledby="emailLogDetail" aria-hidden="true" style="display: none;" v-if="is_staff">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="emailLogDetail">{{trans('mail.email')}}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body" v-if="email_log">
                        <h4>{{email_log.subject}}
                            <span class="pull-right">{{email_log.created_at | momentDateTime}}</span>
                        </h4>
                        <p>{{trans('mail.sender')+': '+email_log.from_address}}</p>
                        <p>{{trans('mail.receiver')+': '+email_log.to_address}}</p>
                        <div v-html="email_log.body"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">{{trans('general.close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import htmlEditor from '../../components/html-editor'
    import vSelect from 'vue-multiselect'

    export default {
        components: {vSelect,htmlEditor},
        props: {
            uuid:{
                required: true,
            },
            showPanel:{
                required: true,
            }
        },
        data(){
            return {
                is_customer: false,
                is_staff: false,
                templates: [],
                emailForm: new Form({
                    template_id: '',
                    subject: '',
                    body: ''
                }),
                selected_template: null,
                reload_content: false,
                email_log: '',
                email_logs: {
                    total: 0,
                    data: []
                },
                filterEmailLogForm: {
                    page_length: helper.getConfig('page_length')
                }
            }
        },
        watch: {
            showPanel(val){
                if(val){
                    this.getInvoiceEmailTemplate();
                    this.getEmailLogs();
                }
            }
        },
        mounted(){
            if(!helper.hasPermission('list-invoice')){
                helper.notAccessibleMsg();
                this.$router.push('/home');
            }

            if(helper.hasRole('customer'))
                this.is_customer = true;
            else
                this.is_staff = true;

        },
        methods: {
            getInvoiceEmailTemplate(){
                if(helper.getConfig('email_template'))
                axios.get('/api/email-template/invoice/lists')
                    .then(response => response.data)
                    .then(response => {
                        this.templates = response;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    })
            },
            getTemplateContent(selectedOption){
                this.emailForm.template_id = selected_template.id;
                axios.get('/api/email-template/'+selectedOption.id+'/content?invoice_uuid='+this.uuid)
                    .then(response => response.data)
                    .then(response => {
                        this.emailForm.subject = response.mail_data.subject;
                        this.reload_content = true;
                        this.emailForm.body = response.mail_data.body;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    });
            },
            sendEmail(){
                this.emailForm.post('/api/invoice/'+this.uuid+'/email')
                    .then(response => {
                        toastr.success(response.message);
                        this.selected_template= '';
                        this.getEmailLogs();
                    })
                    .catch(error => {
                        helper.showErrorMsg(error);
                    });
            },
            toggle(){
                this.$emit('toggle');
            },
            getEmailLogs(page){
                if (typeof page !== 'number') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterEmailLogForm);
                axios.get('/api/invoice/'+this.uuid+'/email-log?page=' + page + url)
                    .then(response => response.data)
                    .then(response => this.email_logs = response)
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    })
            },
            fetchEmailDetail(email_log){
                axios.post('/api/invoice/'+this.uuid+'/email-detail',{
                        id: email_log.id
                    })
                    .then(response => response.data)
                    .then(response => {
                        this.email_log = response;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    })
            },
            getConfig(config){
                return helper.getConfig(config);
            }

        },
        filters: {
          moment(date) {
            return helper.formatDate(date);
          },
          momentDateTime(datetime){
            return helper.formatDateTime(datetime);
          }
        },
        computed: {

        }
    }
</script>

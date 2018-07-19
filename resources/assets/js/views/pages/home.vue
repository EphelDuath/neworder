<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">{{trans('general.home')}}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">{{trans('general.home')}}</li>
                </ol>
            </div>
        </div>

        <div class="row" v-if="hasAdminRole">
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">{{trans('user.customer')}}</h4>
                                <div class="text-right">
                                    <h2 class="font-light m-b-0"><i class="fas fa-users fa-lg pull-right text-success"></i> <span class="pull-left">{{stats.customers}}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">{{trans('invoice.invoice')}}</h4>
                                <div class="text-right">
                                    <h2 class="font-light m-b-0"><i class="fas fa-list-alt fa-lg pull-right text-info"></i> <span class="pull-left">{{stats.invoices}}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">{{trans('quotation.quotation')}}</h4>
                                <div class="text-right">
                                    <h2 class="font-light m-b-0"><i class="fas fa-file-alt fa-lg pull-right text-warning"></i> <span class="pull-left">{{stats.quotations}}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">{{trans('transaction.transaction')}}</h4>
                                <div class="text-right">
                                    <h2 class="font-light m-b-0"><i class="fas fa-industry fa-lg pull-right text-danger"></i> <span class="pull-left">{{stats.transactions}}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('account.account')}}</h4>
                        <h6 class="card-subtitle" v-if="!accounts.length">{{trans('general.no_result_found')}}</h6>
                        <div class="table-responsive" v-if="accounts.length">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{trans('account.name')}}</th>
                                        <th>{{trans('account.type')}}</th>
                                        <th>{{trans('account.balance')}}</th>
                                        <th class="table-option">{{trans('account.last_transaction')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account in accounts">
                                        <td v-text="account.name"></td>
                                        <td v-text="account.type"></td>
                                        <td v-text="account.balance"></td>
                                        <td class="table-option" v-text="account.last_transaction"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="hasAdminRole">
            <div class="col-12 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('invoice.invoice')+' '+trans('general.statistics')}}</h4>
                        <doughnut-graph :graph="graph.invoice_status" v-show="graph.invoice_status.labels.length"></doughnut-graph>
                        <h6 class="card-subtitle" v-if="!graph.invoice_status.labels.length">{{trans('general.no_result_found')}}</h6>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('quotation.quotation')+' '+trans('general.statistics')}}</h4>
                        <doughnut-graph :graph="graph.quotation_status" v-show="graph.quotation_status.labels.length"></doughnut-graph>
                        <h6 class="card-subtitle" v-if="!graph.quotation_status.labels.length">{{trans('general.no_result_found')}}</h6>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{trans('transaction.transaction')+' '+trans('general.statistics')}}</h4>
                        <doughnut-graph :graph="graph.transaction_category" v-show="graph.transaction_category.labels.length"></doughnut-graph>
                        <h6 class="card-subtitle" v-if="!graph.transaction_category.labels.length">{{trans('general.no_result_found')}}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#recent-invoice" role="tab" @click="showHideTabs('showRecentInvoiceTab')">{{trans('invoice.recent')+' '+trans('invoice.invoice')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#unpaid" role="tab" @click="showHideTabs('showUnpaidTab')">{{trans('invoice.status_unpaid')+' '+trans('invoice.invoice')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#partially_paid" role="tab" @click="showHideTabs('showPartiallyPaidTab')">{{trans('invoice.status_partially_paid')+' '+trans('invoice.invoice')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#overdue" role="tab" @click="showHideTabs('showOverdueTab')">{{trans('invoice.status_overdue')+' '+trans('invoice.invoice')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#recurring" role="tab" @click="showHideTabs('showRecurringTab')">{{trans('invoice.recurring')+' '+trans('invoice.invoice')}}</a> </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="recent-invoice" role="tabpanel">
                            <div class="card-body" v-if="tabs.showRecentInvoiceTab">
                                <invoice-list option="recent"></invoice-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="unpaid" role="tabpanel">
                            <div class="card-body" v-if="tabs.showUnpaidTab">
                                <invoice-list option="unpaid"></invoice-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="partially_paid" role="tabpanel">
                            <div class="card-body" v-if="tabs.showPartiallyPaidTab">
                                <invoice-list option="partially_paid"></invoice-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="overdue" role="tabpanel">
                            <div class="card-body" v-if="tabs.showOverdueTab">
                                <invoice-list option="overdue"></invoice-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="recurring" role="tabpanel">
                            <div class="card-body" v-if="tabs.showRecurringTab">
                                <invoice-list option="recurring"></invoice-list>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#recent-quotation" role="tab" @click="showHideQuotationTabs('showRecentQuotationTab')">{{trans('quotation.recent')+' '+trans('quotation.quotation')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#accepted" role="tab" @click="showHideQuotationTabs('showAcceptedTab')">{{trans('quotation.status_accepted')+' '+trans('quotation.quotation')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#rejected" role="tab" @click="showHideQuotationTabs('showRejectedTab')">{{trans('quotation.status_rejected')+' '+trans('quotation.quotation')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#expired" role="tab" @click="showHideQuotationTabs('showExpiredTab')">{{trans('quotation.status_expired')+' '+trans('quotation.quotation')}}</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#invoiced" role="tab" @click="showHideQuotationTabs('showInvoicedTab')">{{trans('quotation.status_invoiced')+' '+trans('quotation.quotation')}}</a> </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="recent-quotation" role="tabpanel">
                            <div class="card-body" v-if="quotation_tabs.showRecentQuotationTab">
                                <quotation-list option="recent"></quotation-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="accepted" role="tabpanel">
                            <div class="card-body" v-if="quotation_tabs.showAcceptedTab">
                                <quotation-list option="accepted"></quotation-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="rejected" role="tabpanel">
                            <div class="card-body" v-if="quotation_tabs.showRejectedTab">
                                <quotation-list option="rejected"></quotation-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="expired" role="tabpanel">
                            <div class="card-body" v-if="quotation_tabs.showExpiredTab">
                                <quotation-list option="expired"></quotation-list>
                            </div>
                        </div>
                        <div class="tab-pane" id="invoiced" role="tabpanel">
                            <div class="card-body" v-if="quotation_tabs.showInvoicedTab">
                                <quotation-list option="invoiced"></quotation-list>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="getConfig('todo')">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="message-scroll">
                            <router-link to="/todo" class="btn btn-success btn-xs pull-right">{{trans('general.view_all')}}</router-link>
                            <h4 class="card-title">{{trans('todo.pending_todo')}}</h4>
                            <h6 class="card-subtitle" v-if="!pending_todos.length">{{trans('general.no_result_found')}}</h6>
                            <div class="table-responsive" v-if="pending_todos.length">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{trans('todo.title')}}</th>
                                            <th class="table-option">{{trans('todo.due_date')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="todo in pending_todos">
                                            <td>
                                                <button class="btn btn-xs btn-danger m-r-5" @click="toggleTodo(todo)"><i class="fas fa-times"></i></button> {{todo.title}}
                                            </td>
                                            <td class="table-option">{{todo.date | moment}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="message-scroll">
                            <router-link to="/todo" class="btn btn-success btn-xs pull-right">{{trans('general.view_all')}}</router-link>
                            <h4 class="card-title">{{trans('todo.recently_completed_todo')}}</h4>
                            <h6 class="card-subtitle" v-if="!completed_todos.length">{{trans('general.no_result_found')}}</h6>
                            <div class="table-responsive" v-if="completed_todos.length">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{trans('todo.title')}}</th>
                                            <th>{{trans('todo.due_date')}}</th>
                                            <th class="table-option">{{trans('todo.completed_at')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="todo in completed_todos">
                                            <td>
                                                <button class="btn btn-xs btn-success m-r-5" @click="toggleTodo(todo)"><i class="fas fa-check"></i></button> <span style="text-decoration: line-through;">{{todo.title}}</span>
                                            </td>
                                            <td>{{todo.date | moment}}</td>
                                            <td class="table-option">{{todo.completed_at | momentDateTime}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6" v-if="getConfig('announcement')">
                <div class="card">
                    <div class="card-body">
                        <div class="message-scroll">
                            <h4 class="card-title">{{trans('announcement.announcement')}}</h4>
                            <div class="comment-widgets">
                                <div class="d-flex flex-row comment-row" v-for="announcement in announcements">
                                    <div class="p-2"><span class="round"><img :src="getAvatar(announcement.user_added)" alt="user" width="50"></span></div>
                                    <div class="comment-text w-100" style="cursor: pointer;" @click="announcement_id = announcement.id" data-toggle="modal" data-target=".announcement-detail">
                                        <h5>{{announcement.user_added.profile.first_name+' '+announcement.user_added.profile.last_name+' ('+announcement.user_added.profile.designation.name+' '+announcement.user_added.profile.designation.department.name+')'}}</h5>
                                        <p class="m-b-5">{{announcement.title}}</p>
                                        <div class="comment-footer">
                                            <span class="text-muted pull-right"><small>{{trans('announcement.posted_on')}}: {{announcement.created_at | momentDateTime}}</small></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h6 class="card-subtitle" v-if="!announcements.length">{{trans('general.no_result_found')}}</h6>
                        </div>
                        <announcement-detail :id="announcement_id"></announcement-detail>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6" v-if="getConfig('activity_log')">
                <div class="card">
                    <div class="card-body">
                        <div class="message-scroll">
                            <h4 class="card-title">{{trans('activity.activity_log')}}</h4>
                            <h6 class="card-subtitle" v-if="!activity_logs.length">{{trans('general.no_result_found')}}</h6>
                            <div class="table-responsive" v-if="activity_logs.length">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th v-if="hasAdminRole">{{trans('user.user')}}</th>
                                            <th>{{trans('activity.activity')}}</th>
                                            <th class="table-option">{{trans('activity.date_time')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="activity_log in activity_logs">
                                            <td v-if="hasAdminRole" v-text="activity_log.user.profile.first_name+' '+activity_log.user.profile.last_name"></td>
                                            <td>{{trans('activity.'+activity_log.activity,{activity: trans('activity.'+activity_log.module)})}}</td>
                                            <td class="table-option">{{activity_log.created_at | momentDateTime }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import announcementDetail from '../announcement/detail'
    import invoiceList from '../invoice/invoice-list'
    import quotationList from '../quotation/quotation-list'
    import doughnutGraph from '../graph/doughnut-graph'

    export default {
        components: {announcementDetail,quotationList,invoiceList,doughnutGraph},
        data() {
            return {
                stats: [],
                accounts: [],
                activity_logs: [],
                announcements: [],
                announcement_id: '',
                pending_todos: [],
                completed_todos: [],
                tabs: {
                    showRecentInvoiceTab: true,
                    showUnpaidTab: false,
                    showPartiallyPaidTab: false,
                    showOverdueTab: false,
                    showRecurringTab: false
                },
                quotation_tabs: {
                    showRecentQuotationTab: true,
                    showAcceptedTab: false,
                    showRejectedTab: false,
                    showExpiredTab: false,
                    showInvoicedTab: false
                },
                graph: {
                    invoice_status: {
                        labels: []
                    },
                    quotation_status: {
                        labels: []
                    },
                    transaction_category: {
                        labels: []
                    }
                },
            }
        },
        mounted(){
            axios.get('/api/dashboard')
                .then(response => response.data)
                .then(response => {
                    this.stats = response.stats;
                    this.accounts = response.accounts;
                    this.activity_logs = response.activity_logs;
                    this.announcements = response.announcements;

                    if(this.hasAdminRole)
                        this.graph = response.graph;
                })
                .catch(error => {
                    helper.showDataErrorMsg(error);
                })

            if(this.getConfig('todo'))
                this.getRecentTodo();
        },
        methods: {
            showHideTabs(activeTab){
                for(let tab in this.tabs)
                    if(tab !== activeTab)
                    this.tabs[tab] = false;
                this.tabs[activeTab] = true;
            },
            showHideQuotationTabs(activeTab){
                for(let tab in this.quotation_tabs)
                    if(tab !== activeTab)
                    this.quotation_tabs[tab] = false;
                this.quotation_tabs[activeTab] = true;
            },
            getRecentTodo(){
                axios.post('/api/todo/recent')
                    .then(response => response.data)
                    .then(response => {
                        this.pending_todos = response.pending_todos;
                        this.completed_todos = response.completed_todos;
                    })
                    .catch(error => {
                        helper.showDataErrorMsg(error);
                    })
            },
            toggleTodo(todo){
                axios.post('/api/todo/'+todo.id+'/status')
                    .then(response => response.data)
                    .then(response => {
                        this.getRecentTodo();
                    })
                    .catch(error => {
                        helper.showDataErrorMsg();
                    });
            },
            getStatus(todo){
                return todo.status ? ('<span class="label label-success">'+i18n.todo.complete+'</span>') : ('<span class="label label-danger">'+i18n.todo.incomplete+'</span>') ;
            },
            hasRole(role){
                return helper.hasRole(role);
            },
            getAvatar(user){
                return helper.getAvatar(user);
            },
            getConfig(name){
                return helper.getConfig(name);
            },
        },
        computed: {
            hasAdminRole(){
                return helper.hasAdminRole();
            }
        },
        filters: {
          momentDateTime(date) {
            return helper.formatDateTime(date);
          },
          moment(date) {
            return helper.formatDate(date);
          }
        },
    }
</script>

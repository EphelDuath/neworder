import store from '../store'
import router from '../routes'

export default {
    // to logout user
    logout(){
        return axios.post('/api/auth/logout').then(response => response.data).then(response =>  {
            toastr.success(response.message);
        }).catch(error => {
            this.showDataErrorMsg(error);
        });
    },

    // to get authenticated user data
    authUser(){
        return axios.get('/api/auth/user').then(response => response.data).then(response =>  {
            return response;
        }).catch(error => {
            this.showDataErrorMsg(error);
        });
    },

    // to check for authenticated user
    check(){
        return axios.post('/api/auth/check').then(response => response.data).then(response =>  {

            store.dispatch('setConfig',response.config);

            if(response.failed_install){
                return false;
            } else
                store.dispatch('setConfig',{'failed_install':0});

            if(response.ip_restricted)
                localStorage.setItem('ip_restricted',true);
            else
                localStorage.removeItem('ip_restricted');

            if(response.authenticated){
                store.dispatch('setAuthUserDetail',{
                    id: response.user.id,
                    first_name: response.user.profile.first_name,
                    last_name: response.user.profile.last_name,
                    email: response.user.email,
                    avatar:response.user.profile.avatar,
                    roles:response.user_roles
                });
                store.dispatch('setPermission',response.permissions);
                store.dispatch('setDefaultRole',response.default_role);

                this.setLastActivity();
            } else {
                store.dispatch('resetAuthUserDetail');
            }

            return response.authenticated;
        }).catch(error =>{
            store.dispatch('resetAuthUserDetail');
            store.dispatch('resetConfig');
        });
    },

    // to set notification position
    notification(){
        var notificationPosition = this.getConfig('notification_position') || 'toast-top-right';
        toastr.options = {
            "positionClass": notificationPosition
        };
        this.setLastActivity();

        $('[data-toastr]').on('click',function(){
            var type = $(this).data('toastr'),message = $(this).data('message'),title = $(this).data('title');
            toastr[type](message, title);
        });
    },

    setLastActivity(){
        if(!this.isScreenLocked())
            store.dispatch('setLastActivity')
    },

    // to check for last activity time and lock/unlock screen
    isScreenLocked(){
        let last_activity = this.getLastActivity();
        let lock_screen_timeout = this.getConfig('lock_screen_timeout');
        let last_activity_after_timeout = moment(last_activity).add(lock_screen_timeout,'minutes').format('LLL');
        return (moment().format('LLL') > last_activity_after_timeout);
    },

    // to append filter variables in the URL
    getFilterURL(data){
        let url = '';
        $.each(data, function(key,value) {
            url += (value) ? '&'+key+'='+encodeURI(value) : '';
        });
        return url;
    },

    getTwoFactorCode(){
        return store.getters.getTwoFactorCode;
    },

    getLastActivity(){
        return store.getters.getLastActivity;
    },

    // to get Auth Status
    isAuth(){
        return store.getters.getAuthStatus;
    },

    // to get Auth user detail
    getAuthUser(name){
        if(name === 'full_name')
            return store.getters.getAuthUser('first_name')+' '+store.getters.getAuthUser('last_name');
        else if(name === 'avatar'){
            if(store.getters.getAuthUser('avatar'))
                return '/'+store.getters.getAuthUser('avatar');
            else
                return '/images/avatar.png';
        }
        else
            return store.getters.getAuthUser(name);
    },

    // to get User avatar
    getAvatar(user){
        if(user && user.profile.avatar)
            return '/'+user.profile.avatar;
        else
            return '/images/avatar.png';
    },

    // to get config
    getConfig(config){
        return store.getters.getConfig(config);
    },

    // to get default role name of system
    getDefaultRole(role){
        return store.getters.getDefaultRole(role);
    },

    // to check role of authenticated user
    hasRole(role){
        return store.getters.hasRole(this.getDefaultRole(role));
    },

    // to check permission for authenticated user
    hasPermission(permission){
        return store.getters.hasPermission(permission);
    },

    // to check Admin role
    hasAdminRole(){
        if(this.hasRole('admin'))
            return 1;
        else
            return 0;
    },

    // to check whether a given user has given role
    userHasRole(user,role_name){
        if(!user.roles)
            return false;

        let user_role = user.roles.filter(role => role.name === this.getDefaultRole(role_name))
        if(user_role.length)
            return true;
        return false;
    },

    // to check feature is available or not
    featureAvailable(feature){
        return this.getConfig(feature);
    },

    // returns not accessible message if permission is denied
    notAccessibleMsg(){
        toastr.error(i18n.permission.permission_denied);
    },

    // returns feature not available message if permission is denied
    featureNotAvailableMsg(){
        toastr.error(i18n.general.feature_not_available);
    },

    // returns user status
    getUserStatus(user){
        let status = [];

        if(user.status === 'activated')
            status.push({'color': 'success','label': i18n.user.status_activated});
        else if(user.status === 'pending_activation')
            status.push({'color': 'warning','label': i18n.user.status_pending_activation});
        else if(user.status === 'pending_approval')
            status.push({'color': 'warning','label': i18n.user.status_pending_approval});
        else if(user.status === 'banned')
            status.push({'color': 'danger','label': i18n.user.status_banned});
        else if(user.status === 'disapproved')
            status.push({'color': 'danger','label': i18n.user.status_disapproved});

        return status;
    },

    // to mass assign one object in another object
    formAssign(form, data){
        for (let key of Object.keys(form)) {
            if(key !== "originalData" && key !== "errors" && key !== "autoReset" && key !== "providers"){
                form[key] = data[key] || '';
            }
        }
        return form;
    },

    // to get date in desired format
    formatDate(date){
        if(!date)
            return;

        return moment(date).format(this.getConfig('date_format'));
    },

    // to get date time in desired format
    formatDateTime(date){
        if(!date)
            return;

        var date_format = this.getConfig('date_format');
        var time_format = this.getConfig('time_format');

        return moment(date).format(date_format+' '+time_format);
    },

    // to get time from now
    formatDateTimeFromNow(datetime){
        if(!datetime)
            return;

        return moment(datetime).fromNow();
    },

    // to change first character of every word to upper case
    ucword(value){
        if(!value)
            return;

        return value.toLowerCase().replace(/\b[a-z]/g, function(value) {
            return value.toUpperCase();
        });
    },

    // to change string into human readable format
    toWord(value){
        if(!value)
            return;

        value = value.replace(/-/g, ' ');
        value = value.replace(/_/g, ' ');

        return value.toLowerCase().replace(/\b[a-z]/g, function(value) {
            return value.toUpperCase();
        });
    },

    // shows toastr notification for axios request
    showDataErrorMsg(error){
        this.setLastActivity();

        if(error.hasOwnProperty("error")){
            if (error.error.indexOf(' ') >= 0)
                toastr.error(error.error);
            else
                toastr.error(i18n.general[error.error]);

            if(error.error === 'token_expired')
                router.push('/login');
        } else if(error.hasOwnProperty("response") && error.response.status == 403) {
            toastr.error(i18n.general.permission_denied);
        } else if(error.hasOwnProperty("response") && error.response.status == 422 && error.response.data.hasOwnProperty("error")) {
            toastr.error(error.response.data.error);
        } else if(error.hasOwnProperty("response") && error.response.status == 404) {
            toastr.error(i18n.general.invalid_link);
        } else if(error.response.data.errors.hasOwnProperty("message"))
            toastr.error(error.response.data.errors.message[0]);
    },

    // returns error message for axios request
    fetchDataErrorMsg(error){
        return error.response.data.errors.message[0];
    },

    // shows toastr notification for axios form request
    showErrorMsg(error){
        this.setLastActivity();

        if(error.hasOwnProperty("error")){

            if (error.error.indexOf(' ') >= 0)
                toastr.error(error.error);
            else
                toastr.error(i18n.general[error.error]);

            if(error.error === 'token_expired')
                router.push('/login');
        } else if(error.hasOwnProperty("response") && error.response.status == 403) {
            toastr.error(i18n.general.permission_denied);
        } else if(error.hasOwnProperty("response") && error.response.status == 422 && error.response.data.hasOwnProperty("error")) {
            toastr.error(error.response.data.error);
        } else if(error.hasOwnProperty("response") && error.response.status == 404) {
            toastr.error(i18n.general.invalid_link);
        } else if(error.errors.hasOwnProperty("message"))
            toastr.error(error.errors.message[0]);
    },

    // returns error message for axios form request
    fetchErrorMsg(error){
        return error.errors.message[0];
    },

    // round numbers as given precision
    roundNumber(number, precision){
        precision = Math.abs(parseInt(precision)) || 0;
        var multiplier = Math.pow(10, precision);
        return (Math.round(number * multiplier) / multiplier);
    },

    // round numbers as given precision
    formatNumber(number,decimal_place){
        if (decimal_place === undefined)
            decimal_place = 2;
        return this.roundNumber(number,decimal_place);
    },

    // fill number with padding
    formatWithPadding(n, width, z){
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    },

    // generates random string of certain length
    randomString(length) {
        if (length === undefined)
            length = 40;
        var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var result = '';
        for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
        return result;
    },

    getUserNameWithDesignationAndDepartment(user){
        if(!user.profile)
            return;

        if(user.profile.designation && user.profile.designation.department)
            return user.profile.first_name+' '+user.profile.last_name+' ('+user.profile.designation.name+' '+i18n.general.in+' '+user.profile.designation.department.name+')';
        else
            return user.profile.first_name+' '+user.profile.last_name;
    },

    getInvoiceStatus(invoice){
        let status = [];
        if(invoice.is_cancelled){
            status.push({'color': 'danger','label': i18n.invoice.status_cancelled});
            return status;
        }

        if(invoice.is_draft == 1)
            status.push({'color': 'info','label': i18n.invoice.status_draft});
        else if(invoice.is_draft == 0 && (invoice.status === null || invoice.status === 'unpaid'))
            status.push({'color': 'danger','label': i18n.invoice.status_unpaid});
        else if(invoice.is_draft == 0 && invoice.status === 'partially_paid')
            status.push({'color': 'warning','label': i18n.invoice.status_partially_paid});
        else if(invoice.is_draft == 0 && invoice.status === 'paid')
            status.push({'color': 'success','label': i18n.invoice.status_paid});

        if(invoice.is_draft == 0 && invoice.status != 'paid' && invoice.due_date != -1 && invoice.due_date_detail < moment().format('YYYY-MM-DD'))
            status.push({'color': 'danger','label': i18n.invoice.status_overdue});

        return status;
    },

    getQuotationStatus(quotation){
        let status = [];
        if(quotation.is_cancelled){
            status.push({'color': 'danger','label': i18n.quotation.status_cancelled});
            return status;
        }

        if(quotation.is_draft == 1)
            status.push({'color': 'info','label': i18n.quotation.status_draft});
        else if(quotation.is_draft == 0 && (quotation.status === 'sent' || quotation.status === null))
            status.push({'color': 'warning','label': i18n.quotation.status_sent});
        else if(quotation.is_draft == 0 && quotation.status === 'accepted')
            status.push({'color': 'success','label': i18n.quotation.status_accepted});
        else if(quotation.is_draft == 0 && quotation.status === 'invoiced')
            status.push({'color': 'success','label': i18n.quotation.status_invoiced});
        else if(quotation.is_draft == 0 && quotation.status === 'rejected')
            status.push({'color': 'danger','label': i18n.quotation.status_rejected});

        if(quotation.is_draft == 0 && quotation.status != 'accepted' && quotation.status != 'rejected' && quotation.expiry_date != -1 && quotation.expiry_date_detail < moment().format('YYYY-MM-DD'))
            status.push({'color': 'danger','label': i18n.quotation.status_expired});

        return status;
    },

    formatCurrency(price,currency){
        let decimal_place = currency.decimal_place || 2;
        if(currency.position === 'prefix')
            return currency.symbol+''+this.roundNumber(price,decimal_place);
        else
            return this.roundNumber(price,decimal_place)+' '+currency.symbol;
    },

    bytesToSize(bytes) {
       var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
       if (bytes == 0) return '0 Byte';
       var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
       return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }
}

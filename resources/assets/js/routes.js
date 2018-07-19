import VueRouter from 'vue-router'
import store from './store'

let routes = [
    {
        path: '/',                                      // all the routes which needs authentication + two factor authentication + lock screen
        component: require('./layouts/default-page'),
        meta: { validate: ['auth','two_factor','lock_screen','valid_license'] },
        children: [
            {
                path: '/',
                component: require('./views/pages/home')
            },
            {
                path: '/home',
                component: require('./views/pages/home')
            },
            {
                path: '/about',
                component: require('./views/pages/about')
            },
            {
                path: '/update',
                component: require('./views/pages/update')
            },
            {
                path: '/support',
                component: require('./views/pages/support')
            },
            {
                path: '/profile',
                component: require('./views/pages/profile')
            },
            {
                path: '/change-password',
                component: require('./views/pages/change-password')
            },
            {
                path: '/blank',
                component: require('./views/pages/blank')
            },
            {
                path: '/configuration',
                component: require('./views/configuration/basic/index'),
            },
            {
                path: '/configuration/logo',
                component: require('./views/configuration/logo/index'),
            },
            {
                path: '/configuration/mail',
                component: require('./views/configuration/mail/index'),
            },
            {
                path: '/backup',
                component: require('./views/backup/index')
            },
            {
                path: '/configuration/basic',
                component: require('./views/configuration/basic/index')
            },
            {
                path: '/configuration/system',
                component: require('./views/configuration/system/index')
            },
            {
                path: '/configuration/role',
                component: require('./views/configuration/role/index')
            },
            {
                path: '/configuration/menu',
                component: require('./views/configuration/menu/index')
            },
            {
                path: '/configuration/authentication',
                component: require('./views/configuration/authentication/index')
            },
            {
                path: '/configuration/sms',
                component: require('./views/configuration/sms/index')
            },
            {
                path: '/configuration/payment-gateway',
                component: require('./views/configuration/payment-gateway/index')
            },
            {
                path: '/configuration/scheduled-job',
                component: require('./views/configuration/scheduled-job/index')
            },
            {
                path: '/configuration/ip-filter',
                component: require('./views/configuration/ip-filter/index')
            },
            {
                path: '/configuration/ip-filter/:id/edit',
                component: require('./views/configuration/ip-filter/edit')
            },
            {
                path: '/configuration/permission',
                component: require('./views/configuration/permission/index')
            },
            {
                path: '/configuration/permission/assign',
                component: require('./views/configuration/permission/assign')
            },
            {
                path: '/configuration/locale',
                component: require('./views/configuration/locale/index')
            },
            {
                path: '/configuration/locale/:id/edit',
                component: require('./views/configuration/locale/edit')
            },
            {
                path: '/configuration/locale/:locale',
                component: require('./views/configuration/locale/view')
            },
            {
                path: '/configuration/locale/:locale/:module',
                component: require('./views/configuration/locale/view')
            },
            {
                path: '/email-template',
                component: require('./views/email-template/index')
            },
            {
                path: '/email-template/:id/edit',
                component: require('./views/email-template/edit')
            },
            {
                path: '/email-log',
                component: require('./views/email-log/index')
            },
            {
                path: '/activity-log',
                component: require('./views/activity-log/index')
            },
            {
                path: '/department',
                component: require('./views/department/index')
            },
            {
                path: '/department/:id/edit',
                component: require('./views/department/edit')
            },
            {
                path: '/designation',
                component: require('./views/designation/index')
            },
            {
                path: '/designation/:id/edit',
                component: require('./views/designation/edit')
            },
            {
                path: '/configuration/currency',
                component: require('./views/configuration/currency/index')
            },
            {
                path: '/configuration/currency/:id/edit',
                component: require('./views/configuration/currency/edit')
            },
            {
                path: '/configuration/taxation',
                component: require('./views/configuration/taxation/index')
            },
            {
                path: '/configuration/taxation/:id/edit',
                component: require('./views/configuration/taxation/edit')
            },
            {
                path: '/configuration/module',
                component: require('./views/configuration/customer-group/index')
            },
            {
                path: '/configuration/customer-group',
                component: require('./views/configuration/customer-group/index')
            },
            {
                path: '/configuration/customer-group/:id/edit',
                component: require('./views/configuration/customer-group/edit')
            },
            {
                path: '/configuration/income-category',
                component: require('./views/configuration/income-category/index')
            },
            {
                path: '/configuration/income-category/:id/edit',
                component: require('./views/configuration/income-category/edit')
            },
            {
                path: '/configuration/expense-category',
                component: require('./views/configuration/expense-category/index')
            },
            {
                path: '/configuration/expense-category/:id/edit',
                component: require('./views/configuration/expense-category/edit')
            },
            {
                path: '/configuration/item-category',
                component: require('./views/configuration/item-category/index')
            },
            {
                path: '/configuration/item-category/:id/edit',
                component: require('./views/configuration/item-category/edit')
            },
            {
                path: '/configuration/invoice',
                component: require('./views/configuration/invoice/index')
            },
            {
                path: '/configuration/quotation',
                component: require('./views/configuration/quotation/index')
            },
            {
                path: '/configuration/payment-method',
                component: require('./views/configuration/payment-method/index')
            },
            {
                path: '/configuration/payment-method/:id/edit',
                component: require('./views/configuration/payment-method/edit')
            },
            {
                path: '/company',
                component: require('./views/company/index')
            },
            {
                path: '/company/:id/edit',
                component: require('./views/company/edit')
            },
            {
                path: '/supplier',
                component: require('./views/supplier/index')
            },
            {
                path: '/supplier/:id/edit',
                component: require('./views/supplier/edit')
            },
            {
                path: '/account',
                component: require('./views/account/index')
            },
            {
                path: '/account/:id/edit',
                component: require('./views/account/edit')
            },
            {
                path: '/todo',
                component: require('./views/todo/index')
            },
            {
                path: '/todo/:id/edit',
                component: require('./views/todo/edit')
            },
            {
                path: '/coupon',
                component: require('./views/coupon/index')
            },
            {
                path: '/coupon/:id/edit',
                component: require('./views/coupon/edit')
            },
            {
                path: '/item',
                component: require('./views/item/index')
            },
            {
                path: '/item/:id/edit',
                component: require('./views/item/edit')
            },
            {
                path: '/announcement',
                component: require('./views/announcement/index')
            },
            {
                path: '/announcement/:id/edit',
                component: require('./views/announcement/edit')
            },
            {
                path: '/user/:type',
                component: require('./views/user/index')
            },
            {
                path: '/user/:type/:id',
                component: require('./views/user/basic')
            },
            {
                path: '/user/:type/:id/basic',
                component: require('./views/user/basic')
            },
            {
                path: '/user/:type/:id/contact',
                component: require('./views/user/contact')
            },
            {
                path: '/user/:type/:id/avatar',
                component: require('./views/user/avatar')
            },
            {
                path: '/user/:type/:id/social',
                component: require('./views/user/social')
            },
            {
                path: '/user/:type/:id/password',
                component: require('./views/user/password')
            },
            {
                path: '/user/:type/:id/email',
                component: require('./views/user/email')
            },
            {
                path: '/transaction/:type',
                component: require('./views/transaction/index')
            },
            {
                path: '/transaction/:type/:uuid/edit',
                component: require('./views/transaction/edit')
            },
            {
                path: '/invoice',
                component: require('./views/invoice/index')
            },
            {
                path: '/invoice/create',
                component: require('./views/invoice/create')
            },
            {
                path: '/invoice/:uuid',
                component: require('./views/invoice/view')
            },
            {
                path: '/invoice/:uuid/edit',
                component: require('./views/invoice/edit')
            },
            {
                path: '/invoice/:uuid/preview',
                component: require('./views/invoice/view')
            },
            {
                path: '/invoice/:uuid/payment/:txnuuid/response',
                component: require('./views/invoice/view')
            },
            {
                path: '/quotation',
                component: require('./views/quotation/index')
            },
            {
                path: '/quotation/create',
                component: require('./views/quotation/create')
            },
            {
                path: '/quotation/:uuid',
                component: require('./views/quotation/view')
            },
            {
                path: '/quotation/:uuid/edit',
                component: require('./views/quotation/edit')
            },
            {
                path: '/quotation/:uuid/preview',
                component: require('./views/quotation/view')
            },
            {
                path: '/quotation',
                component: require('./views/quotation/index')
            },
            {
                path: '/quotation/create',
                component: require('./views/quotation/create')
            },
            {
                path: '/message',
                component: require('./views/message/index')
            },
            {
                path: '/message/compose',
                component: require('./views/message/index')
            },
            {
                path: '/message/inbox',
                component: require('./views/message/inbox')
            },
            {
                path: '/message/sent',
                component: require('./views/message/sent')
            },
            {
                path: '/message/important',
                component: require('./views/message/important')
            },
            {
                path: '/message/trash',
                component: require('./views/message/trash')
            },
            {
                path: '/message/draft',
                component: require('./views/message/draft')
            },
            {
                path: '/message/:uuid/draft',
                component: require('./views/message/edit-draft')
            },
            {
                path: '/message/:uuid',
                component: require('./views/message/view')
            },
        ]
    },
    {
        path: '/',                                       // all the routes which needs authentication + two factor authentication + lock screen
        component: require('./layouts/default-page'),
        meta: { validate: ['auth','two_factor','lock_screen'] },
        children: [
            {
                path: '/license',
                component: require('./views/license/index'),
            },
        ]
    },
    {
        path: '/',                                  // all the routes which can be access without authentication
        component: require('./layouts/guest-page'),
        meta: { validate: ['auth'] },
        children: [
            {
                path: '/auth/security',
                component: require('./views/auth/security'),
            },
            {
                path: '/auth/lock',
                component: require('./views/auth/lock'),
            },
        ]
    },
    {
        path: '/',
        component: require('./layouts/guest-page'),
        meta: { validate: ['guest'] },
        children: [
            {
                path: '/',
                component: require('./views/auth/login')
            },
            {
                path: '/login',
                component: require('./views/auth/login')
            },
            {
                path: '/password',
                component: require('./views/auth/password')
            },
            {
                path: '/register',
                component: require('./views/auth/register')
            },
            {
                path: '/auth/:token/activate',
                component: require('./views/auth/activate')
            },
            {
                path: '/password/reset/:token',
                component: require('./views/auth/reset')
            },
            {
                path: '/auth/social',
                component: require('./views/auth/social-auth')
            },
            {
                path: '/install',
                component: require('./views/install/index')
            }
        ]
    },
    {
        path: '/',
        component : require('./layouts/guest-page'),
        children: [
            {
                path: '/terms-and-conditions',
                component: require('./views/pages/terms-and-conditions')
            }
        ]
    },
    {
        path: '/',
        component : require('./layouts/error-page'),
        children: [
            {
                path: '/terms-and-conditions',
                component: require('./views/pages/terms-and-conditions')
            },
            {
                path: '/ip-restricted',
                component: require('./views/errors/ip-restricted')
            },
            {
                path: '/maintenance',
                component: require('./views/errors/maintenance')
            }
        ]
    },
    {
        path: '*',
        component : require('./layouts/error-page'),
        children: [
            {
                path: '*',
                component: require('./views/errors/page-not-found')
            }
        ]
    }
];

const router = new VueRouter({
    routes,
    // linkActiveClass: 'active',
    mode: 'history',
    scrollBehavior (to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return { x: 0, y: 0 }
        }
    }
});

router.beforeEach((to, from, next) => {
    // For force logout
    // store.dispatch('resetAuthUserDetail');

    helper.check()
        .then(response => {

            // Initialize toastr notification
            helper.notification();
            if(helper.getConfig('failed_install') && to.fullPath != '/install')
                return next({ path: '/install' })

            // Check for IP Restriction; If restricted IP found, redirect to "/ip-restricted" route
            if(helper.getConfig('ip_filter') && localStorage.getItem('ip_restricted') && to.fullPath != '/ip-restricted')
                return next({ path: '/ip-restricted' })

            // Check for Maintenance mode; If maintenance mode is on, redirect to "/maintenance" route
            if(helper.isAuth() && !helper.hasRole('admin') && helper.getConfig('maintenance_mode') && to.fullPath != '/maintenance')
                return next({ path: '/maintenance' })

            if (to.matched.some(m => m.meta.validate)){
                const m = to.matched.find(m => m.meta.validate);

                // Check for authentication; If no, redirect to "/login" route
                if (m.meta.validate.indexOf('auth') > -1){
                    if(!helper.isAuth()){
                        toastr.error(i18n.auth.auth_required);
                        return next({ path: '/login' })
                    }
                }

                // Check for two factor security; If enabled, redirect to "/auth/security" route after login
                if (m.meta.validate.indexOf('two_factor') > -1){
                    if(helper.getConfig('two_factor_security') && helper.getTwoFactorCode()){
                        return next({ path: '/auth/security' })
                    }
                }

                // Check for screen lock; If enabled, redirect to "/auth/lock" route after screen lock timeout
                if (m.meta.validate.indexOf('lock_screen') > -1){
                    if(helper.getConfig('lock_screen') && helper.isScreenLocked()){
                        return next({ path: '/auth/lock' })
                    }
                }

                // Check for screen lock; If enabled, redirect to "/auth/lock" route after screen lock timeout
                if (m.meta.validate.indexOf('valid_license') > -1){
                    if(!helper.getConfig('l')){
                        toastr.error(i18n.license.invalid_license);
                        return next({ path: '/license' })
                    }
                }

                // Check for authentication; If authenticated, redirect to "/home" route
                if (m.meta.validate.indexOf('guest') > -1){
                    if(helper.isAuth()){
                        toastr.error(i18n.auth.guest_required);
                        return next({ path: '/home' })
                    }
                }
            }

        return next()
    }).catch(error => {
        // Authentication check fail, redirected back to "/login" route
        store.dispatch('resetAuthUserDetail');
        return next({ path: '/login' })
    });
});

export default router;

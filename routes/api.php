<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login','AuthController@authenticate');
    Route::post('/check','AuthController@check');
    Route::post('/register','AuthController@register');
    Route::get('/activate/{token}','AuthController@activate');
    Route::post('/password','AuthController@password');
    Route::post('/validate-password-reset','AuthController@validatePasswordReset');
    Route::post('/reset','AuthController@reset');
    Route::post('/social/token','SocialLoginController@getToken');
});

Route::get('/configuration/variable','ConfigurationController@getConfigurationVariable');

Route::group(['middleware' => ['jwt.auth']], function () {

    Route::post('/auth/logout','AuthController@logout');
    Route::post('/auth/lock','AuthController@lock');
    Route::post('/demo/message','HomeController@demoMessage');

    Route::post('/change-password','AuthController@changePassword');

    Route::post('/upload','UploadController@upload');
    Route::post('/upload/extension','UploadController@getAllowedExtension');
    Route::post('/upload/image','UploadController@uploadImage');
    Route::post('/upload/fetch','UploadController@fetch');
    Route::post('/upload/{id}','UploadController@destroy');

    Route::get('/dashboard','HomeController@dashboard');
    Route::post('/dashboard/graph','HomeController@dashboardGraph');

    Route::get('/configuration','ConfigurationController@index');
    Route::post('/configuration','ConfigurationController@store');
    Route::post('/configuration/logo/{type}','ConfigurationController@uploadLogo');
    Route::delete('/configuration/logo/{type}/remove','ConfigurationController@removeLogo');
    Route::get('/fetch/lists','ConfigurationController@fetchList');

    Route::post('/backup','BackupController@store');
    Route::get('/backup','BackupController@index');
    Route::delete('/backup/{id}','BackupController@destroy');

    Route::get('/locale','LocaleController@index');
    Route::post('/locale','LocaleController@store');
    Route::get('/locale/{id}','LocaleController@show');
    Route::patch('/locale/{id}','LocaleController@update');
    Route::delete('/locale/{id}','LocaleController@destroy');
    Route::post('/locale/fetch','LocaleController@fetch');
    Route::post('/locale/translate','LocaleController@translate');
    Route::post('/locale/add-word','LocaleController@addWord');

    Route::get('/role','RoleController@index');
    Route::get('/role/{id}','RoleController@show');
    Route::post('/role','RoleController@store');
    Route::delete('/role/{id}','RoleController@destroy');

    Route::get('/permission','PermissionController@index');
    Route::get('/permission/assign/pre-requisite','PermissionController@preRequisite');
    Route::get('/permission/{id}','PermissionController@show');
    Route::post('/permission','PermissionController@store');
    Route::delete('/permission/{id}','PermissionController@destroy');
    Route::post('/permission/assign','PermissionController@assignPermission');

    Route::get('/ip-filter','IpFilterController@index');
    Route::get('/ip-filter/{id}','IpFilterController@show');
    Route::post('/ip-filter','IpFilterController@store');
    Route::patch('/ip-filter/{id}','IpFilterController@update');
    Route::delete('/ip-filter/{id}','IpFilterController@destroy');

    Route::get('/email-template','EmailTemplateController@index');
    Route::post('/email-template','EmailTemplateController@store');
    Route::get('/email-template/{id}','EmailTemplateController@show');
    Route::patch('/email-template/{id}','EmailTemplateController@update');
    Route::delete('/email-template/{id}','EmailTemplateController@destroy');
    Route::get('/email-template/{category}/lists','EmailTemplateController@lists');
    Route::get('/email-template/{id}/content','EmailTemplateController@getContent');

    Route::get('/todo','TodoController@index');
    Route::get('/todo/{id}','TodoController@show');
    Route::post('/todo','TodoController@store');
    Route::patch('/todo/{id}','TodoController@update');
    Route::delete('/todo/{id}','TodoController@destroy');
    Route::post('/todo/{id}/status','TodoController@toggleStatus');
    Route::post('/todo/recent','TodoController@recent');

    Route::get('/user/pre-requisite/{type}','UserController@preRequisite');
    Route::get('/user/detail','UserController@detail');
    Route::get('/user/{type}','UserController@index');
    Route::get('/user/{type}/{id}','UserController@show');
    Route::post('/user','UserController@store');
    Route::post('/user/{type}/{id}/status','UserController@updateStatus');
    Route::patch('/user/{type}/{id}','UserController@update');
    Route::patch('/user/{type}/{id}/contact','UserController@updateContact');
    Route::patch('/user/{type}/{id}/social','UserController@updateSocial');
    Route::patch('/user/{type}/{id}/force-reset-password','UserController@forceResetPassword');
    Route::patch('/user/{type}/{id}/email','UserController@sendEmail');
    Route::post('/user/profile/update','UserController@updateProfile');
    Route::post('/user/profile/avatar/{id}','UserController@uploadAvatar');
    Route::delete('/user/profile/avatar/remove/{id}','UserController@removeAvatar');
    Route::delete('/user/{uuid}','UserController@destroy');

    Route::get('/message/compose/pre-requisite','MessageController@preRequisite');
    Route::post('/message/statistics','MessageController@statistics');
    Route::post('/message/compose','MessageController@store');
    Route::post('/message/reply','MessageController@reply');
    Route::get('/message/{uuid}/reply','MessageController@loadReply');
    Route::get('/message/draft','MessageController@getDraftList');
    Route::get('/message/{uuid}/draft','MessageController@getDraft');
    Route::get('/message/inbox','MessageController@getInboxList');
    Route::get('/message/sent','MessageController@getSentList');
    Route::get('/message/important','MessageController@getImportantList');
    Route::get('/message/trash','MessageController@getTrashList');
    Route::delete('/message/{uuid}/draft','MessageController@destroyDraft');
    Route::post('/message/{uuid}/trash','MessageController@trash');
    Route::post('/message/{uuid}/restore','MessageController@restore');
    Route::delete('/message/{id}/delete','MessageController@destroy');
    Route::get('/message/{uuid}','MessageController@show');
    Route::post('/message/{uuid}/important','MessageController@toggleImportant');

    Route::get('/email-log','EmailLogController@index');
    Route::get('/email-log/{id}','EmailLogController@show');
    Route::delete('/email-log/{id}','EmailLogController@destroy');

    Route::get('/activity-log','ActivityLogController@index');
    Route::delete('/activity-log/{id}','ActivityLogController@destroy');

    Route::get('/department','DepartmentController@index');
    Route::post('/department','DepartmentController@store');
    Route::get('/department/{id}','DepartmentController@show');
    Route::patch('/department/{id}','DepartmentController@update');
    Route::delete('/department/{id}','DepartmentController@destroy');

    Route::get('/designation/pre-requisite','DesignationController@preRequisite');
    Route::get('/designation','DesignationController@index');
    Route::post('/designation','DesignationController@store');
    Route::get('/designation/{id}','DesignationController@show');
    Route::patch('/designation/{id}','DesignationController@update');
    Route::delete('/designation/{id}','DesignationController@destroy');

    Route::get('/currency','CurrencyController@index');
    Route::get('/currency/{id}','CurrencyController@show');
    Route::post('/currency','CurrencyController@store');
    Route::patch('/currency/{id}','CurrencyController@update');
    Route::delete('/currency/{id}','CurrencyController@destroy');
    Route::get('/currency/fetch/default','CurrencyController@fetchDefault');

    Route::get('/taxation','TaxationController@index');
    Route::get('/taxation/{id}','TaxationController@show');
    Route::post('/taxation','TaxationController@store');
    Route::patch('/taxation/{id}','TaxationController@update');
    Route::delete('/taxation/{id}','TaxationController@destroy');
    Route::get('/taxation/fetch/default','TaxationController@fetchDefault');

    Route::get('/customer-group','CustomerGroupController@index');
    Route::get('/customer-group/{id}','CustomerGroupController@show');
    Route::post('/customer-group','CustomerGroupController@store');
    Route::patch('/customer-group/{id}','CustomerGroupController@update');
    Route::delete('/customer-group/{id}','CustomerGroupController@destroy');

    Route::get('/expense-category','ExpenseCategoryController@index');
    Route::get('/expense-category/{id}','ExpenseCategoryController@show');
    Route::post('/expense-category','ExpenseCategoryController@store');
    Route::patch('/expense-category/{id}','ExpenseCategoryController@update');
    Route::delete('/expense-category/{id}','ExpenseCategoryController@destroy');

    Route::get('/income-category','IncomeCategoryController@index');
    Route::get('/income-category/{id}','IncomeCategoryController@show');
    Route::post('/income-category','IncomeCategoryController@store');
    Route::patch('/income-category/{id}','IncomeCategoryController@update');
    Route::delete('/income-category/{id}','IncomeCategoryController@destroy');

    Route::get('/item-category','ItemCategoryController@index');
    Route::get('/item-category/{id}','ItemCategoryController@show');
    Route::post('/item-category','ItemCategoryController@store');
    Route::patch('/item-category/{id}','ItemCategoryController@update');
    Route::delete('/item-category/{id}','ItemCategoryController@destroy');

    Route::get('/payment-method','PaymentMethodController@index');
    Route::get('/payment-method/{id}','PaymentMethodController@show');
    Route::post('/payment-method','PaymentMethodController@store');
    Route::patch('/payment-method/{id}','PaymentMethodController@update');
    Route::delete('/payment-method/{id}','PaymentMethodController@destroy');

    Route::get('/company','CompanyController@index');
    Route::get('/company/{id}','CompanyController@show');
    Route::post('/company','CompanyController@store');
    Route::patch('/company/{id}','CompanyController@update');
    Route::delete('/company/{id}','CompanyController@destroy');

    Route::get('/supplier/pre-requisite','SupplierController@preRequisite');
    Route::get('/supplier','SupplierController@index');
    Route::get('/supplier/{id}','SupplierController@show');
    Route::post('/supplier','SupplierController@store');
    Route::patch('/supplier/{id}','SupplierController@update');
    Route::delete('/supplier/{id}','SupplierController@destroy');

    Route::get('/account','AccountController@index');
    Route::get('/account/{id}','AccountController@show');
    Route::post('/account','AccountController@store');
    Route::patch('/account/{id}','AccountController@update');
    Route::delete('/account/{id}','AccountController@destroy');

    Route::get('/coupon','CouponController@index');
    Route::get('/coupon/{id}','CouponController@show');
    Route::post('/coupon','CouponController@store');
    Route::patch('/coupon/{id}','CouponController@update');
    Route::delete('/coupon/{id}','CouponController@destroy');
    
    Route::get('/item/pre-requisite','ItemController@preRequisite');
    Route::get('/item','ItemController@index');
    Route::get('/item/{id}','ItemController@show');
    Route::post('/item','ItemController@store');
    Route::patch('/item/{id}','ItemController@update');
    Route::delete('/item/{id}','ItemController@destroy');

    Route::get('/announcement/pre-requisite','AnnouncementController@preRequisite');
    Route::get('/announcement','AnnouncementController@index');
    Route::get('/announcement/{id}','AnnouncementController@show');
    Route::post('/announcement','AnnouncementController@store');
    Route::patch('/announcement/{id}','AnnouncementController@update');
    Route::delete('/announcement/{id}','AnnouncementController@destroy');

    Route::get('/transaction/{type}/pre-requisite','TransactionController@preRequisite');
    Route::get('/transaction/{type}','TransactionController@index');
    Route::get('/transaction/{type}/{uuid}','TransactionController@show');
    Route::delete('/transaction/{type}/{uuid}','TransactionController@destroy');
    Route::post('/transaction','TransactionController@store');
    Route::patch('/transaction/{uuid}','TransactionController@update');

    Route::get('/invoice/pre-requisite','InvoiceController@preRequisite');
    Route::get('/invoice','InvoiceController@index');
    Route::post('/invoice','InvoiceController@store');
    Route::post('/invoice/{uuid}','InvoiceController@store');
    Route::get('/invoice/{uuid}','InvoiceController@show');
    Route::get('/invoice/{uuid}/detail','InvoiceController@fetchDetail');
    Route::post('/invoice/{uuid}/email','InvoiceController@sendEmail');
    Route::get('/invoice/{uuid}/email-log','InvoiceController@emailLog');
    Route::post('/invoice/{uuid}/email-detail','InvoiceController@emailDetail');
    Route::post('/invoice/{uuid}/send','InvoiceController@send');
    Route::post('/invoice/{uuid}/cancel','InvoiceController@cancel');
    Route::post('/invoice/{uuid}/undo-cancel','InvoiceController@undoCancel');
    Route::post('/invoice/{uuid}/mark-as-sent','InvoiceController@markAsSent');
    Route::post('/invoice/{uuid}/copy','InvoiceController@copy');
    Route::delete('/invoice/{uuid}','InvoiceController@destroy');
    Route::post('/invoice/{uuid}/toggle-partial-payment','InvoiceController@togglePartialPayment');
    Route::post('/invoice/{uuid}/recurring','InvoiceController@recurring');
    Route::get('/invoice/{uuid}/recurring','InvoiceController@listRecurring');
    Route::post('/invoice/{uuid}/payment/pre-requisite','InvoiceController@paymentPreRequisite');
    Route::post('/invoice/{uuid}/payment','InvoiceController@payment');
    Route::get('/invoice/{uuid}/payment','InvoiceController@getPayment');
    Route::get('/invoice/{uuid}/payment/{payment_uuid}','InvoiceController@showPayment');
    Route::delete('/invoice/{uuid}/payment/{id}','InvoiceController@destroyPayment');

    Route::post('/payment/validate/coupon','PaymentController@validateCoupon');
    Route::post('/payment/{invoice_uuid}/credit-card','PaymentController@creditCard');
    Route::post('/payment/{invoice_uuid}/paypal','PaymentController@paypal');

    Route::get('/quotation/pre-requisite','QuotationController@preRequisite');
    Route::get('/quotation','QuotationController@index');
    Route::post('/quotation','QuotationController@store');
    Route::post('/quotation/{uuid}','QuotationController@store');
    Route::get('/quotation/{uuid}','QuotationController@show');
    Route::get('/quotation/{uuid}/detail','QuotationController@fetchDetail');
    Route::post('/quotation/{uuid}/email','QuotationController@sendEmail');
    Route::get('/quotation/{uuid}/email-log','QuotationController@emailLog');
    Route::post('/quotation/{uuid}/email-detail','QuotationController@emailDetail');
    Route::post('/quotation/{uuid}/send','QuotationController@send');
    Route::post('/quotation/{uuid}/cancel','QuotationController@cancel');
    Route::post('/quotation/{uuid}/undo-cancel','QuotationController@undoCancel');
    Route::post('/quotation/{uuid}/mark-as-sent','QuotationController@markAsSent');
    Route::post('/quotation/{uuid}/copy','QuotationController@copy');
    Route::delete('/quotation/{uuid}','QuotationController@destroy');
    Route::post('/quotation/{uuid}/action','QuotationController@customerAction');
    Route::post('/quotation/{uuid}/convert-to-invoice','QuotationController@convertToInvoice');
    Route::post('/quotation/{uuid}/discussion','QuotationController@discussion');
    Route::get('/quotation/{uuid}/discussion','QuotationController@getDiscussion');
    Route::delete('/quotation/{uuid}/discussion/{id}','QuotationController@destroyDiscussion');
});

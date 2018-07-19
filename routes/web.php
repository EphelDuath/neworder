<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test','HomeController@test');

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('/backup/{id}/download','BackupController@download');
    Route::get('/message/{message_uuid}/attachment/{attachment_uuid}/download','MessageController@download');
    Route::get('/announcement/{id}/attachment/{attachment_uuid}/download','AnnouncementController@download');
    Route::get('/invoice/{invoice_uuid}/attachment/{attachment_uuid}/download','InvoiceController@download');
    Route::get('/invoice/{uuid}/print','InvoiceController@print');
    Route::get('/invoice/{uuid}/pdf','InvoiceController@pdf');
    Route::get('/quotation/{invoice_uuid}/attachment/{attachment_uuid}/download','QuotationController@download');
    Route::get('/quotation/{uuid}/print','QuotationController@print');
    Route::get('/quotation/{uuid}/pdf','QuotationController@pdf');
});
Route::get('/auth/social/{provider}', 'SocialLoginController@providerRedirect');
Route::get('/auth/{provider}/callback', 'SocialLoginController@providerRedirectCallback');
Route::get('/paypal/status', 'PaymentController@paypalStatus');

// Used to get translation in json format for current locale

Route::get('/js/lang', function () {
    if(App::environment('local'))
        Cache::forget('lang.js');
    $strings = Cache::rememberForever('lang.js', function () {
        $lang = config('app.locale');
        $files   = glob(resource_path('lang/' . $lang . '/*.php'));
        $strings = [];
        foreach ($files as $file) {
            $name           = basename($file, '.php');
            $strings[$name] = require $file;
        }
        return $strings;
    });
    header('Content-Type: text/javascript');
    echo('window.i18n = ' . json_encode($strings) . ';');
    exit();
})->name('assets.lang');

Route::get('/{vue?}', function () {
    return view('home');
})->where('vue', '[\/\w\.-]*')->name('home');

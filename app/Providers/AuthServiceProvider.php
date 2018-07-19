<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Todo'         => 'App\Policies\TodoPolicy',
        'App\User'         => 'App\Policies\UserPolicy',
        'App\Account'      => 'App\Policies\AccountPolicy',
        'App\Announcement' => 'App\Policies\AnnouncementPolicy',
        'App\Supplier'     => 'App\Policies\SupplierPolicy',
        'App\Company'      => 'App\Policies\CompanyPolicy',
        'App\Coupon'       => 'App\Policies\CouponPolicy',
        'App\Department'   => 'App\Policies\DepartmentPolicy',
        'App\Designation'  => 'App\Policies\DesignationPolicy',
        'App\Invoice'      => 'App\Policies\InvoicePolicy',
        'App\Item'         => 'App\Policies\ItemPolicy',
        'App\Quotation'    => 'App\Policies\QuotationPolicy',
        'App\Transaction'  => 'App\Policies\TransactionPolicy'
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}

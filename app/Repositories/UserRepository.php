<?php
namespace App\Repositories;

use App\User;
use App\Profile;
use App\Jobs\SendMail;
use App\UserPreference;
use Illuminate\Support\Str;
use App\Notifications\Activation;
use Illuminate\Support\Facades\Log;
use App\Repositories\RoleRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\EmailLogRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\CustomerGroupRepository;
use Illuminate\Validation\ValidationException;

class UserRepository
{
    protected $user;
    protected $role;
    protected $email;
    protected $designation;
    protected $company;
    protected $customer_group;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        User $user,
        RoleRepository $role,
        EmailLogRepository $email,
        DesignationRepository $designation,
        CompanyRepository $company,
        CustomerGroupRepository $customer_group
    ) {
        $this->user  = $user;
        $this->role  = $role;
        $this->email = $email;
        $this->designation = $designation;
        $this->company = $company;
        $this->customer_group = $customer_group;
    }

    /**
     * Get all users with profile
     *
     * @return User
     */
    public function getAll()
    {
        return $this->user->with('profile', 'roles')->get();
    }

    /**
     * Count users
     *
     * @return integer
     */
    public function count()
    {
        return $this->user->count();
    }

    /**
     * List all users by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->user->all()->pluck('id')->all();
    }

    /**
     * List all customers by id
     *
     * @return array
     */
    public function listCustomerId()
    {
        return $this->user->role(config('system.default_role.customer'))->get()->pluck('id')->all();
    }

    /**
     * Count customers
     *
     * @return integer
     */
    public function countFilterbyCustomer()
    {
        return $this->user->role(config('system.default_role.customer'))->count();
    }

    /**
     * Count users registered between dates
     *
     * @return integer
     */
    public function countDateBetween($start_date, $end_date)
    {
        return $this->user->createdAtDateBetween(['start_date' => $start_date, 'end_date' => $end_date])->count();
    }

    /**
     * Find user by Id
     *
     * @param integer $id
     * @return User
     */
    public function findOrFail($id = null)
    {
        $user = $this->user->with('profile', 'roles', 'userPreference', 'profile.designation', 'profile.designation.department', 'profile.company', 'customerGroup')->find($id);

        if (! $user) {
            throw ValidationException::withMessages(['message' => trans('user.could_not_find')]);
        }

        return $user;
    }

    /**
     * Find user by Email
     *
     * @param email $email
     * @return User
     */
    public function findByEmail($email = null)
    {
        return $this->user->with('profile', 'roles', 'userPreference', 'profile.designation', 'profile.designation.department', 'profile.company', 'customerGroup')->filterByEmail($email)->first();
    }

    /**
     * Find user by activation token
     *
     * @param string $token
     * @return User
     */
    public function findByActivationToken($token = null)
    {
        return $this->user->with('profile', 'roles', 'userPreference', 'profile.designation', 'profile.designation.department', 'profile.company', 'customerGroup')->whereActivationToken($token)->first();
    }

    /**
     * List user except authenticated user by name & email
     *
     * @param string $token
     * @return User
     */
    public function listByNameAndEmailExceptAuthUser()
    {
        return $this->user->where('id', '!=', \Auth::user()->id)->get()->pluck('name_with_email', 'id')->all();
    }

    /**
     * Validate user type
     *
     * @param string $type
     * @return string
     */
    public function validateType($type = 'staff')
    {
        if (! in_array($type, ['staff','customer'])) {
            return  'staff';
        }

        return $type;
    }

    /**
     * List all customers by id
     *
     * @return array
     */
    public function listCustomerById()
    {
        return $this->user->role(config('system.default_role.customer'))->get()->pluck('id')->all();
    }

    /**
     * List all customers by name with email & id for select option
     *
     * @return array
     */
    public function selectAllCustomerByNameId()
    {
        return generateSelectOption($this->user->with('profile')->role(config('system.default_role.customer'))->get()->pluck('name_with_email', 'id')->all());
    }

    /**
     * Get user type of given user
     *
     * @param User $user
     * @return string
     */
    public function getUserType(User $user)
    {
        return ($user->hasRole(config('system.default_role.customer'))) ? 'customer' : 'staff';
    }

    /**
     * Paginate all todos using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params = array(), $type)
    {
        $sort_by               = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order                 = isset($params['order']) ? $params['order'] : 'desc';
        $page_length           = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $first_name            = isset($params['first_name']) ? $params['first_name'] : null;
        $last_name             = isset($params['last_name']) ? $params['last_name'] : null;
        $email                 = isset($params['email']) ? $params['email'] : null;
        $role_id               = isset($params['role_id']) ? $params['role_id'] : null;
        $company_id            = isset($params['company_id']) ? $params['company_id'] : null;
        $designation_id        = isset($params['designation_id']) ? $params['designation_id'] : null;
        $customer_group_id     = isset($params['customer_group_id']) ? $params['customer_group_id'] : null;
        $status                = isset($params['status']) ? $params['status'] : null;
        $created_at_start_date = isset($params['created_at_start_date']) ? $params['created_at_start_date'] : null;
        $created_at_end_date   = isset($params['created_at_end_date']) ? $params['created_at_end_date'] : null;

        $customers = $this->user->role(config('system.default_role.customer'))->get()->pluck('id')->all();

        if ($type === 'staff') {
            $accessible_users = $this->getAccessibleUserId();
            $query = $this->user->with('profile', 'profile.designation', 'roles')->whereNotIn('id', $customers)->whereIn('id', $accessible_users);
        } elseif ($type === 'customer') {
            $query = $this->user->with('profile', 'profile.company', 'customerGroup')->whereIn('id', $customers);
        }

        $query->filterByFirstName($first_name)->filterByLastName($last_name)->filterByEmail($email)->filterByStatus($status)->createdAtDateBetween([
            'start_date' => $created_at_start_date,
            'end_date' => $created_at_end_date
        ]);

        if ($type === 'staff') {
            $query->filterByRoleId($role_id)->filterByDesignationId($designation_id);
        } elseif ($type === 'customer') {
            $query->filterByCompanyId($company_id)->filterByCustomerGroupId($customer_group_id);
        }

        if ($sort_by === 'first_name') {
            $query->select('users.*', \DB::raw('(select first_name from profiles where users.id = profiles.user_id) as first_name'))->orderBy('first_name', $order);
        } elseif ($sort_by === 'last_name') {
            $query->select('users.*', \DB::raw('(select last_name from profiles where users.id = profiles.user_id) as last_name'))->orderBy('last_name', $order);
        } elseif ($sort_by === 'company') {
            $query->select('users.*', \DB::raw('(select company_id from profiles where users.id = profiles.user_id) as company_id'))->orderBy('company_id', $order);
        } elseif ($sort_by === 'designation') {
            $query->select('users.*', \DB::raw('(select designation_id from profiles where users.id = profiles.user_id) as designation_id'))->orderBy('designation_id', $order);
        } else {
            $query->orderBy($sort_by, $order);
        }

        return $query->paginate($page_length);
    }

    /**
     * Validate user by given type & id
     *
     * @param string $type
     * @param integer $id
     * @return void
     */
    public function validateUser($type, $id)
    {
        $type = $this->validateType($type);

        if ($type === 'staff' && ! in_array($id, $this->getAccessibleUserId())) {
            throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);
        }

        $customers = $this->listCustomerById();

        if (($type === 'staff' && in_array($id, $customers)) || ($type === 'customer' && !in_array($id, $customers))) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }
    }

    /**
     * Get all user query who are accessible for given user id
     *
     * @param integer $user_id
     * @param boolean $self (Pass 1 to include given user id)
     * @return Query
     */
    public function getAccessibleUser($user_id, $self = 0)
    {
        $auth_user = \Auth::user();

        $user_id = ($user_id) ? : $auth_user->id;

        $user = $this->user->find($user_id);
        $self = $user->hasRole(config('system.default_role.admin')) ? 1 : 0;

        if ($user->hasRole(config('system.default_role.admin'))) {
            return $this->user->with('profile', 'profile.designation', 'profile.designation.department');
        }

        if ($self) {
            return $this->user->with('profile', 'profile.designation', 'profile.designation.department')->where(function ($qry1) use ($user,$auth_user) {
                $qry1->where('id', '=', $auth_user->id)->orWhereHas('profile', function ($qry) use ($user) {
                    $qry->whereIn('designation_id', $this->designation->getSubordinate($user));
                });
            });
        } else {
            return $this->user->with('profile', 'profile.designation', 'profile.designation.department')->whereHas('profile', function ($qry) use ($user) {
                $qry->whereIn('designation_id', $this->designation->getSubordinate($user));
            });
        }
    }

    /**
     * Get all user's id who are accessible for given user id
     *
     * @param integer $user_id
     * @param boolean $self (Pass 1 to include given user id)
     * @return array
     */
    public function getAccessibleUserId($user_id = '', $self = 0)
    {
        return $this->getAccessibleUser($user_id, $self)->get()->pluck('id')->all();
    }

    /**
     * Create a new user.
     *
     * @param array $params
     * @return User
     */
    public function create($params, $register = 0)
    {
        $type = ($register && $this->count()) ? 'customer' : (isset($params['type']) ? $params['type'] : 'staff');

        $this->validateInputId($params, $type);
        
        $user = $this->user->forceCreate($this->formatParams($params, 'register'));

        if ($register) {
            $role_id = $this->role->findByName(config('system.default_role.'.($this->count() > 1 ? 'customer' : 'admin')))->id;
        } else {
            $role_id = ($type === 'staff') ? (isset($params['role_id']) ? $params['role_id'] : null) : $this->role->findByName(config('system.default_role.customer'))->id;
        }

        $this->assignRole($user, $role_id);

        $profile = $this->associateProfile($user);

        if ($register && $this->count() <= 1) {
            $designation = $this->designation->getHidden();
            $profile->designation_id = $designation ? $designation->id : null;
            $profile->save();
        }

        $profile = $this->updateProfileRelation($profile, $params, $type);

        $profile = $this->updateProfile($profile, $params);

        if (isset($params['send_email']) && isset($params['send_email']) && $params['send_email']) {
            SendMail::dispatch($user->email, [
                'slug'      => 'welcome-email-'.$type,
                'user'      => $user,
                'password'  => $params['password'],
                'module'    => 'user',
                'module_id' => $user->id
            ]);
        }

        if ($register && config('config.email_verification')) {
            $user->notify(new Activation($user));
        }

        return $user;
    }

    /**
     * Validate input ids.
     *
     * @param array $params
     * @return null
     */

    public function validateInputId($params, $type)
    {
        $company_ids = $this->company->listId();
        $customer_group_ids = $this->customer_group->listId();
        $role_ids = $this->role->listId();
        $designation_ids = $this->designation->listId();

        $company_id = isset($params['company_id']) ? $params['company_id'] : null;
        $customer_group_id = isset($params['customer_group_id']) ? $params['customer_group_id'] : [];
        $designation_id = isset($params['designation_id']) ? $params['designation_id'] : null;
        $role_id = isset($params['role_id']) ? $params['role_id'] : [];

        if ($type === 'customer' && ! in_array($company_id, $company_ids)) {
            throw ValidationException::withMessages(['message' => trans('company.could_not_find')]);
        }

        if ($type === 'staff' && ! in_array($designation_id, $designation_ids)) {
            throw ValidationException::withMessages(['message' => trans('designation.could_not_find')]);
        }

        if ($type === 'customer' && $customer_group_id && (! is_array($customer_group_id) || count(array_diff($customer_group_id, $customer_group_ids)))) {
            throw ValidationException::withMessages(['message' => trans('customer_group.could_not_find')]);
        }
        
        if ($type === 'staff' && $role_id && (! is_array($role_id) || count(array_diff($role_id, $role_ids)))) {
            throw ValidationException::withMessages(['message' => trans('role.could_not_find')]);
        }
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $action = 'create')
    {
        $formatted = [
            'email'       => isset($params['email']) ? $params['email'] : null,
            'status' => 'activated',
            'password' => isset($params['password']) ? bcrypt($params['password']) : null,
            'activation_token' => Str::uuid(),
            'uuid' => Str::uuid()
        ];

        if ($action === 'register') {
            if (config('config.email_verification')) {
                $formatted['status'] = 'pending_activation';
            } elseif (config('config.account_approval')) {
                $formatted['status'] = 'pending_approval';
            }
        }

        return $formatted;
    }

    /**
     * Assign role to user.
     *
     * @param User
     * @param integer $role_id
     * @return null
     */
    private function assignRole($user, $role_id, $action = 'attach')
    {
        if ($action === 'attach') {
            $user->assignRole($this->role->listNameById($role_id));
        } else {
            $user->roles()->sync($role_id);
        }
    }

    /**
     * Associate user to profile.
     *
     * @param User
     * @return Profile
     */
    private function associateProfile($user)
    {
        $profile = new Profile;
        $user->profile()->save($profile);

        $user_preference = new UserPreference;
        $user->userPreference()->save($user_preference);

        return $profile;
    }

    /**
     * Update user profile.
     *
     * @param Profile
     * @param array $params
     * @return null
     */
    public function updateProfile($profile, $params = array())
    {
        $profile->first_name           = isset($params['first_name']) ? $params['first_name'] : $profile->first_name;
        $profile->last_name            = isset($params['last_name']) ? $params['last_name'] : $profile->last_name;
        $profile->address_line_1       = isset($params['address_line_1']) ? $params['address_line_1'] : $profile->address_line_1;
        $profile->address_line_2       = isset($params['address_line_2']) ? $params['address_line_2'] : $profile->address_line_2;
        $profile->city                 = isset($params['city']) ? $params['city'] : $profile->city;
        $profile->state                = isset($params['state']) ? $params['state'] : $profile->state;
        $profile->zipcode              = isset($params['zipcode']) ? $params['zipcode'] : $profile->zipcode;
        $profile->country_id           = isset($params['country_id']) ? $params['country_id'] : $profile->country_id;
        $profile->gender               = isset($params['gender']) ? $params['gender'] : $profile->gender;
        $profile->phone                = isset($params['phone']) ? $params['phone'] : $profile->phone;
        $profile->work_phone           = isset($params['work_phone']) ? $params['work_phone'] : $profile->work_phone;
        $profile->work_phone_extension = isset($params['work_phone_extension']) ? $params['work_phone_extension'] : $profile->work_phone_extension;
        $profile->work_email           = isset($params['work_email']) ? $params['work_email'] : $profile->work_email;
        $profile->date_of_birth        = isset($params['date_of_birth']) ? ($params['date_of_birth'] ? : null) : $profile->date_of_birth;
        $profile->date_of_anniversary  = isset($params['date_of_anniversary']) ? ($params['date_of_anniversary'] ? : null) : $profile->date_of_anniversary;
        $profile->facebook_profile     = isset($params['facebook_profile']) ? $params['facebook_profile'] : $profile->facebook_profile;
        $profile->twitter_profile      = isset($params['twitter_profile']) ? $params['twitter_profile'] : $profile->twitter_profile;
        $profile->linkedin_profile     = isset($params['linkedin_profile']) ? $params['linkedin_profile'] : $profile->linkedin_profile;
        $profile->google_plus_profile  = isset($params['google_plus_profile']) ? $params['google_plus_profile'] : $profile->google_plus_profile;
        $profile->save();

        return $profile;
    }

    /**
     * Update user profile with designation, company & customer group.
     *
     * @param Profile
     * @param array $params
     * @param string $type
     * @return profile
     */
    public function updateProfileRelation($profile, $params, $type)
    {
        if ($type === 'staff') {
            $profile->designation_id = isset($params['designation_id']) ? $params['designation_id'] : null;
        } elseif ($type === 'customer') {
            $profile->company_id = isset($params['company_id']) ? $params['company_id'] : null;
        }

        $profile->User->customerGroup()->sync(isset($params['customer_group_id']) ? $params['customer_group_id'] : null);
        
        $profile->save();

        return $profile;
    }

    /**
     * Update given user.
     *
     * @param User $user
     * @param array $params
     *
     * @return User
     */
    public function update(User $user, $params = array(), $type = 'staff')
    {
        $this->validateInputId($params, $type);

        $profile = $this->updateProfile($user->Profile, $params);

        $profile = $this->updateProfileRelation($profile, $params, $type);

        if ($type === 'staff') {
            $this->assignRole($user, isset($params['role_id']) ? $params['role_id'] : [], 'sync');
        }

        return $user;
    }

    /**
     * Update given user status.
     *
     * @param User $user
     * @param string $status
     *
     * @return User
     */
    public function status(User $user, $status = null)
    {
        if (!in_array($status, ['activated','pending_activation','pending_approval','banned','disapproved'])) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        if ($user->hasRole(config('system.default_role.admin'))) {
            throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);
        }

        $user->status = $status;
        $user->save();

        return $user;
    }

    /**
     * Force reset user password.
     *
     * @param User $user
     * @param string $password
     *
     * @return User
     */
    public function forceResetPassword(User $user, $password = null)
    {
        $user->password = bcrypt($password);
        $user->save();

        return $user;
    }

    /**
     * Send email to user.
     *
     * @param User $user
     * @param array $params
     *
     * @return null
     */
    public function sendEmail(User $user, $params = array())
    {
        $body = isset($params['body']) ? $params['body'] : null;
        $subject = isset($params['subject']) ? $params['subject'] : null;
        $email = $user->email;

        \Mail::send('emails.email', compact('body'), function ($message) use ($subject, $email) {
            $message->to($email)->subject($subject);
        });

        $this->email->record([
            'to' => $email,
            'subject' => $subject,
            'body' => $body,
            'module' => 'user',
            'module_id' => $user->id
        ]);
    }

    /**
     * Find user & check it can be deleted or not.
     *
     * @param integer $id
     * @return User
     */
    public function deletable($id)
    {
        $user = $this->findOrFail($id);

        if ($user->invoices()->count()) {
            throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);
        }

        if ($user->customerInvoices()->count()) {
            throw ValidationException::withMessages(['message' => trans('user.has_many_customer_invoices')]);
        }

        if ($user->quotations()->count()) {
            throw ValidationException::withMessages(['message' => trans('user.has_many_quotations')]);
        }

        if ($user->customerQuotations()->count()) {
            throw ValidationException::withMessages(['message' => trans('user.has_many_customer_quotations')]);
        }

        if ($user->transactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('user.has_many_transactions')]);
        }

        if ($user->customerTransactions()->count()) {
            throw ValidationException::withMessages(['message' => trans('user.has_many_customer_transactions')]);
        }
        
        return $user;
    }

    /**
     * Delete user.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(User $user)
    {
        return $user->delete();
    }

    /**
     * Delete multiple users.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->user->whereIn('id', $ids)->delete();
    }
}

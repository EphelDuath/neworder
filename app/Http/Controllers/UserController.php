<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserEmailRequest;
use App\Repositories\CompanyRepository;
use App\Http\Requests\UserProfileRequest;
use App\Repositories\ActivityLogRepository;
use App\Repositories\DesignationRepository;
use App\Http\Requests\ChangePasswordRequest;
use App\Repositories\CustomerGroupRepository;

class UserController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $role;
    protected $company;
    protected $customer_group;
    protected $designation;
    protected $module = 'user';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        UserRepository $repo,
        ActivityLogRepository $activity,
        RoleRepository $role,
        CompanyRepository $company,
        CustomerGroupRepository $customer_group,
        DesignationRepository $designation
    ) {
        $this->request = $request;
        $this->repo = $repo;
        $this->activity = $activity;
        $this->role = $role;
        $this->company = $company;
        $this->customer_group = $customer_group;
        $this->designation = $designation;

        $this->middleware('prohibited.test.mode')->only(['forceResetPassword','destroy','uploadAvatar','removeAvatar']);
    }

    /**
     * Used to get Pre Requisite for User Module
     * @get ("/api/user/pre-requisite")
     * @return Response
     */
    public function preRequisite($type = 'staff')
    {
        $type = $this->repo->validateType($type);

        $this->authorize('preRequisite', [User::class, $type]);

        $countries = generateNormalSelectOption(getVar('country'));
        $genders = generateTranslatedSelectOption(getVar('list')['gender']);

        if ($type === 'customer') {
            $companies = $this->company->selectAll();
            $customer_groups = $this->customer_group->selectAll();
        } else {
            $designations = generateSelectOption($this->designation->listAll());
            $roles = generateSelectOption($this->role->listExceptName([config('system.default_role.admin'),config('system.default_role.customer')]));
        }

        return $this->success(compact('countries', 'roles', 'genders', 'companies', 'customer_groups', 'designations', 'roles'));
    }

    /**
     * Used to get all Users
     * @get ("/api/user")
     * @return Response
     */
    public function index($type = 'staff')
    {
        $type = $this->repo->validateType($type);

        $this->authorize('list', [User::class, $type]);

        return $this->ok($this->repo->paginate($this->request->all(), $type));
    }

    /**
     * Used to store User
     * @post ("/api/user")
     * @param ({
     *      @Parameter("first_name", type="string", required="true", description="First Name of User"),
     *      @Parameter("last_name", type="string", required="true", description="Last Name of User"),
     *      @Parameter("email", type="email", required="true", description="Email of User"),
     *      @Parameter("password", type="password", required="true", description="Password of User"),
     *      @Parameter("password_confirmation", type="password_confirmation", required="true", description="Confirm Password of User"),
     *      @Parameter("role_id", type="array", required="true", description="Roles of User"),
     *      @Parameter("address_line_1", type="string", required="optional", description="Address Line 1 of User"),
     *      @Parameter("address_line_2", type="string", required="optional", description="Address Line 2 of User"),
     *      @Parameter("city", type="string", required="optional", description="City of User"),
     *      @Parameter("state", type="string", required="optional", description="State of User"),
     *      @Parameter("zipcode", type="string", required="optional", description="Zipcode of User"),
     *      @Parameter("country_id", type="integer", required="true", description="Country of User"),
     * })
     * @return Response
     */
    public function store(RegisterRequest $request)
    {
        $type = $this->repo->validateType(request('type'));

        $this->authorize('create', [User::class, $type]);

        $user = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => request('type'),
            'module_id' => $user->id,
            'activity'  => 'created'
        ]);

        return $this->success(['message' => trans('user.added', ['type' => request('type')])]);
    }

    /**
     * Used to fetch User detail
     * @get ("/api/user/detail")
     * @return Response
     */
    public function detail()
    {
        return $this->ok($this->repo->findOrFail(\Auth::user()->id));
    }

    /**
     * Used to get User detail
     * @get ("/api/user/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     * })
     * @return Response
     */
    public function show($type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('view', [User::class, $type]);

        $this->repo->validateUser($type, $id);

        $selected_roles = ($type === 'staff') ? generateSelectOption($user->roles()->pluck('name', 'id')->all()) : [];
        $roles = ($type === 'staff') ? $user->roles()->pluck('id')->all() : [];
        $selected_customer_groups = ($type === 'customer') ? generateSelectOption($user->customerGroup()->pluck('name', 'customer_groups.id')->all()) : [];
        $customer_groups = ($type === 'customer') ? $user->customerGroup()->pluck('customer_groups.id')->all() : [];
        $selected_designation = ($type === 'staff') ? ['id' => $user->Profile->designation_id, 'name' => $user->Profile->Designation->designation_with_department] : [];
        $selected_company = ($type === 'customer' && $user->Profile->company_id) ? ['id' => $user->Profile->company_id, 'name' => $user->Profile->Company->name] : [];

        return $this->success(compact('user', 'selected_roles', 'roles', 'selected_customer_groups', 'customer_groups', 'selected_designation', 'selected_company'));
    }

    /**
     * Used to update User
     * @patch ("/api/user/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("first_name", type="string", required="true", description="First Name of User"),
     *      @Parameter("last_name", type="string", required="true", description="Last Name of User"),
     *      @Parameter("role_id", type="array", required="true", description="Roles of User"),
     *      @Parameter("gender", type="string", required="true", description="Gender of User"),
     *      @Parameter("date_of_birth", type="date", required="optional", description="Date of Birth of User"),
     *      @Parameter("date_of_anniversary", type="date", required="optional", description="Date of Anniversary of User"),
     * })
     * @return Response
     */
    public function update(UserProfileRequest $request, $type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('update', [User::class, $type]);

        $this->repo->validateUser($type, $id);

        $user = $this->repo->update($user, $this->request->all(), $type);

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'profile',
            'activity'   => 'updated'
        ]);

        return $this->success(['message' => trans('user.profile_updated')]);
    }

    /**
     * Used to update User Status
     * @post ("/api/user/{id}/status")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("status", type="string", required="true", description="Status of User"),
     * })
     * @return Response
     */
    public function updateStatus($type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('update', [User::class, $type]);

        $this->repo->validateUser($type, $id);

        $this->repo->status($user, request('status'));

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'status',
            'activity'   => 'updated'
        ]);

        return $this->success(['message' => trans('user.profile_updated')]);
    }

    /**
     * Used to update User Contact
     * @patch ("/api/user/{id}/contact")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("address_line_1", type="string", required="optional", description="Address Line 1 of User"),
     *      @Parameter("address_line_2", type="string", required="optional", description="Address Line 2 of User"),
     *      @Parameter("city", type="string", required="optional", description="City of User"),
     *      @Parameter("state", type="string", required="optional", description="State of User"),
     *      @Parameter("zipcode", type="string", required="optional", description="Zipcode of User"),
     *      @Parameter("country_id", type="integer", required="true", description="Country of User"),
     *      @Parameter("phone", type="string", required="true", description="Phone of User"),
     * })
     * @return Response
     */
    public function updateContact(UserProfileRequest $request, $type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('update', [User::class, $type]);

        $this->repo->validateUser($type, $id);

        $profile = $this->repo->update($user, $this->request->all());

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'contact',
            'activity'   => 'updated'
        ]);

        return $this->success(['message' => trans('user.profile_updated')]);
    }

    /**
     * Used to update User Social Links
     * @patch ("/api/user/{id}/social")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("facebook_profile", type="string", required="optional", description="Facebook Profile of User"),
     *      @Parameter("twitter_profile", type="string", required="optional", description="Twitter Profile of User"),
     *      @Parameter("linkedin_profile", type="string", required="optional", description="Linked Profile of User"),
     *      @Parameter("google_plus_profile", type="string", required="optional", description="Google Plus Profile of User"),
     * })
     * @return Response
     */
    public function updateSocial($type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('update', [User::class, $type]);

        $this->repo->validateUser($type, $id);

        $profile = $this->repo->update($user, $this->request->all());

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'social',
            'activity'   => 'updated'
        ]);

        return $this->success(['message' => trans('user.profile_updated')]);
    }

    /**
     * Used to force change User Password
     * @patch ("/api/user/{id}/force-reset-password")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("new_password", type="string", required="true", description="New Password of User"),
     *      @Parameter("new_password_confirmation", type="string", required="true", description="Confirm New Password of User"),
     * })
     * @return Response
     */
    public function forceResetPassword(ChangePasswordRequest $request, $type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('forceResetUserPassword', [$user, $type]);

        $this->repo->validateUser($type, $id);

        $user = $this->repo->forceResetPassword($user, request('new_password'));

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'password',
            'activity'   => 'updated'
        ]);

        return $this->success(['message' => trans('passwords.change')]);
    }

    /**
     * Used to send email to User
     * @patch ("/api/user/{id}/email")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("template_id", type="integer", required="true", description="Id of Template"),
     *      @Parameter("subject", type="string", required="true", description="Subject of Email"),
     *      @Parameter("body", type="text", required="true", description="Body of Email"),
     * })
     * @return Response
     */
    public function sendEmail(UserEmailRequest $request, $type, $id)
    {
        $user = $this->repo->findOrFail($id);

        $this->authorize('email', [$user, $type]);

        $this->repo->validateUser($type, $id);

        $this->repo->sendEmail($user, $this->request->all());

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'mail',
            'activity'   => 'sent'
        ]);

        return $this->success(['message' => trans('template.mail_sent')]);
    }

    /**
     * Used to update User Profile
     * @post ("/api/user/profile/update")
     * @param ({
     *      @Parameter("first_name", type="string", required="true", description="First Name of User"),
     *      @Parameter("last_name", type="string", required="true", description="Last Name of User"),
     *      @Parameter("gender", type="string", required="true", description="Gender of User"),
     *      @Parameter("date_of_birth", type="date", required="optional", description="Date of Birth of User"),
     *      @Parameter("date_of_anniversary", type="date", required="optional", description="Date of Anniversary of User"),
     *      @Parameter("facebook_profile", type="string", required="optional", description="Facebook Profile of User"),
     *      @Parameter("twitter_profile", type="string", required="optional", description="Twitter Profile of User"),
     *      @Parameter("linkedin_profile", type="string", required="optional", description="Linked Profile of User"),
     *      @Parameter("google_plus_profile", type="string", required="optional", description="Google Plus Profile of User"),
     * })
     * @return Response
     */
    public function updateProfile(UserProfileRequest $request)
    {
        $auth_user = \Auth::user();

        $profile = $this->repo->update($auth_user, $this->request->all());

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $auth_user->id,
            'sub_module' => 'profile',
            'activity'   => 'updated'
        ]);

        return $this->success(['message' => trans('user.profile_updated')]);
    }

    /**
     * Used to update User Avatar
     * @post ("/api/user/profile/avatar/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     *      @Parameter("image", type="file", required="true", description="Image File to be uploaded"),
     * })
     * @return Response
     */
    public function uploadAvatar($id)
    {
        $user = $this->repo->findOrFail($id);

        $type = $this->repo->getUserType($user);

        $this->authorize('avatar', [$user, $type]);

        $this->repo->validateUser($type, $id);

        $image_path = config('system.upload_path.avatar').'/';

        $profile = $user->Profile;
        $image = $profile->avatar;

        if ($image && \File::exists($image)) {
            \File::delete($image);
        }

        $extension = request()->file('image')->getClientOriginalExtension();
        $filename = uniqid();
        $file = request()->file('image')->move($image_path, $filename.".".$extension);
        $img = \Image::make($image_path.$filename.".".$extension);
        $img->resize(200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($image_path.$filename.".".$extension);

        $profile->avatar = $image_path.$filename.".".$extension;
        $profile->save();

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'avatar',
            'activity'   => 'uploaded'
        ]);

        return $this->success(['message' => trans('user.avatar_uploaded'),'image' => $image_path.$filename.".".$extension]);
    }

    /**
     * Used to remove User Avatar
     * @delete ("/api/user/profile/avatar/remove/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of User"),
     * })
     * @return Response
     */
    public function removeAvatar($id)
    {
        $user = $this->repo->findOrFail($id);

        $type = $this->repo->getUserType($user);

        $this->authorize('avatar', [$user, $type]);

        $this->repo->validateUser($type, $id);

        $image_path = config('system.upload_path.avatar').'/';

        $profile = $user->Profile;
        $image = $profile->avatar;

        if (!$image) {
            return $this->error(['message' => trans('user.no_avatar_uploaded')]);
        }

        if (\File::exists($image)) {
            \File::delete($image);
        }

        $profile->avatar = null;
        $profile->save();

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => 'avatar',
            'activity'   => 'removed'
        ]);

        return $this->success(['message' => trans('user.avatar_removed')]);
    }

    /**
     * Used to delete User
     * @delete ("/api/user/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Uuid of User"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $user = $this->repo->deletable($id);

        $type = $this->repo->getUserType($user);

        $this->authorize('delete', [User::class, $type]);

        $this->repo->validateUser($type, $id);

        $this->activity->record([
            'module'     => $type,
            'module_id'  => $user->id,
            'sub_module' => $user->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($user);

        return $this->success(['message' => trans('user.deleted')]);
    }
}

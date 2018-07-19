<?php
namespace App\Repositories;

use App\Announcement;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use App\Repositories\DesignationRepository;
use Illuminate\Validation\ValidationException;

class AnnouncementRepository
{
	protected $announcement;
	protected $upload;
	protected $user;
    protected $designation;
    protected $module = 'announcement';

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
	public function __construct(
        Announcement $announcement, 
        UploadRepository $upload, 
        UserRepository $user,
        DesignationRepository $designation
    ) {
		$this->announcement = $announcement;
		$this->upload = $upload;
		$this->user = $user;
        $this->designation = $designation;
	}

    /**
     * Get announcement query
     *
     * @return Announcement query
     */
    public function getQuery()
    {
        return $this->announcement->with('designation','userAdded','userAdded.profile','userAdded.profile.designation','userAdded.profile.designation.department');
    }

    /**
     * Count announcement
     *
     * @return integer
     */
    public function count()
    {
        return $this->announcement->count();
    }

    /**
     * List all announcements by name & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->announcement->all()->pluck('title','id')->all();
    }

    /**
     * Get all announcements
     *
     * @return array
     */
    public function getAll()
    {
        return $this->announcement->with('designation','userAdded','userAdded.profile','userAdded.profile.designation','userAdded.profile.designation.department')->get();
    }

    public function getUserAnnouncement()
    {
        $announcements = $this->announcement->with('designation','userAdded','userAdded.profile','userAdded.profile.designation','userAdded.profile.designation.department')->where('start_date','<=',date('Y-m-d'))->where('end_date','>=',date('Y-m-d'));

        if(\Auth::user()->hasRole(config('system.default_role.customer')))
            $announcements->where('audience','=','customer');
        else {
            $announcements->where(function($q) {
                $q->whereIn('user_id',$this->user->getAccessibleUserId())
                    ->orWhere(function($q1) {
                        $q1->whereHas('designation',function($q2) {
                            $q2->where('designation_id',\Auth::user()->Profile->designation_id);
                        });
                    });
            }); 
        }

        return $announcements->get();
    }

    /**
     * Find announcement with given id or throw an error.
     *
     * @param integer $id
     * @return Announcement
     */
    public function findOrFail($id)
    {
        $announcement = $this->announcement->with('designation','userAdded','userAdded.profile','userAdded.profile.designation','userAdded.profile.designation.department')->find($id);

        if (! $announcement) {
            throw ValidationException::withMessages(['message' => trans('announcement.could_not_find')]);
        }

        return $announcement;
    }

    /**
     * Find announcement is accessible for authenticated user.
     *
     * @param Announcement $announcement
     * @return boolean
     */
    public function accessible(Announcement $announcement)
    {
    	$auth_user = \Auth::user();
        $accessible_users_id = $this->user->getAccessibleUserId();

        // Aaccessible if owner of announcement is accessible user of logged in user
        // Accessible if announcement is for customer & logged in user is customer
        // Accessible if announcement owner is logged in user
        // Accessible if logged in user's designation is listed in designation list of announcement

        if(
        	($auth_user->hasRole(config('system.default_role.customer')) && $announcement->audience === 'customer') ||
            in_array($announcement->user_id,$accessible_users_id) ||
            $announcement->user_id === $auth_user->id ||
            ($announcement->audience === 'staff' && in_array($auth_user->Profile->designation_id,$announcement->designation()->pluck('designation_id')->all()))
        )
            return 1;

        return 0;
    }

    /**
     * Paginate all announcements using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
		$sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
		$order       = isset($params['order']) ? $params['order'] : 'desc';
		$page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $title       = isset($params['title']) ? $params['title'] : '';

        return $this->announcement->with('designation','userAdded','userAdded.profile','userAdded.profile.designation','userAdded.profile.designation.department')->filterByTitle($title)->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new announcement.
     *
     * @param array $params
     * @return Announcement
     */
    public function create($params)
    {
        $this->validateInputId($params);

        $announcement = $this->announcement->forceCreate($this->formatParams($params));

        $this->assignDesignation($announcement, $params);

        $upload_token = isset($params['upload_token']) ? $params['upload_token'] : null;

        $this->upload->store($this->module, $announcement->id, $upload_token);

        return $announcement;
    }

    /**
     * Validate input ids.
     *
     * @param array $params
     * @return null
     */

    public function validateInputId($params)
    {
        $designation_ids = $this->designation->listId();

        $designation_id = isset($params['designation_id']) ? $params['designation_id'] : null;

        if ($designation_id && (! is_array($designation_id) || count(array_diff($designation_id, $designation_ids)))) {
            throw ValidationException::withMessages(['message' => trans('designation.could_not_find')]);
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
            'title'       => isset($params['title']) ? $params['title'] : null,
            'audience'    => isset($params['audience']) ? $params['audience'] : null,
            'start_date'  => isset($params['start_date']) ? toDate($params['start_date']) : null,
            'end_date'    => isset($params['end_date']) ? toDate($params['end_date']) : null,
            'description' => isset($params['description']) ? $params['description'] : null
        ];

        if ($action === 'create') {
            $formatted['user_id']      = \Auth::user()->id;
            $formatted['upload_token'] =  isset($params['upload_token']) ? $params['upload_token'] : null;
        }

        return $formatted;
    }

    /**
     * Assign designation to announcement if required.
     *
     * @param Announcement $announcement
     * @param array $params
     * @return void
     */
    private function assignDesignation(Announcement $announcement, $params)
    {
        $designation_id = isset($params['designation_id']) ? $params['designation_id'] : [];
    	if ($announcement->audience === 'staff') {
    		$announcement->designation()->sync($designation_id);
    	} else {
            $announcement->designation()->sync([]);
        }
    }

    /**
     * Update given announcement.
     *
     * @param Announcement $announcement
     * @param array $params
     *
     * @return Announcement
     */
    public function update(Announcement $announcement, $params)
    {
        $this->validateInputId($params);

        $announcement->forceFill($this->formatParams($params, 'update'))->save();

        $this->assignDesignation($announcement, $params);

        $upload_token = isset($params['upload_token']) ? $params['upload_token'] : null;

        $this->upload->update($this->module, $announcement->id, $upload_token);

        return $announcement;
    }

    /**
     * Delete announcement.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Announcement $announcement)
    {
        return $announcement->delete();
    }

    /**
     * Delete multiple announcements.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->announcement->whereIn('id', $ids)->delete();
    }
}
<?php

namespace App\Http\Controllers;

use App\Announcement;
use Illuminate\Http\Request;
use App\Repositories\UploadRepository;
use App\Http\Requests\AnnouncementRequest;
use App\Repositories\ActivityLogRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\AnnouncementRepository;

class AnnouncementController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $designation;
    protected $upload;
    protected $module = 'announcement';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        AnnouncementRepository $repo,
        ActivityLogRepository $activity,
        DesignationRepository $designation,
        UploadRepository $upload
    ) {
        $this->request     = $request;
        $this->repo        = $repo;
        $this->activity    = $activity;
        $this->designation = $designation;
        $this->upload      = $upload;

        $this->middleware('feature.available:announcement');
    }

    /**
     * Used to fetch Pre-Requisites for Announcement
     * @get ("/api/announcement/pre-requisite")
     * @return Response
     */
    public function preRequisite()
    {
        $this->authorize('preRequisite', Announcement::class);

        return $this->ok(generateSelectOption($this->designation->listAllFilterById($this->designation->getChild(\Auth::user()->Profile->designation_id, 1))));
    }

    /**
     * Used to get all Announcements
     * @get ("/api/announcement")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Announcement::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Announcement
     * @post ("/api/announcement")
     * @param ({
     *      @Parameter("title", type="string", required="true", description="Title of Announcement"),
     *      @Parameter("start_date", type="date", required="true", description="Start date of Announcement"),
     *      @Parameter("end_date", type="date", required="true", description="End date of Announcement"),
     *      @Parameter("audience", type="string", required="true", description="Type of audience for Announcement"),
     *      @Parameter("designation_id", type="array", required="conditional", description="Designations for Announcement if audience is staff"),
     * })
     * @return Response
     */
    public function store(AnnouncementRequest $request)
    {
        $this->authorize('create', Announcement::class);

        $announcement = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $announcement->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('announcement.added')]);
    }

    /**
     * Used to get Announcement detail
     * @get ("/api/announcement/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Announcement"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('view', Announcement::class);

        $announcement = $this->repo->findOrFail($id);

        $this->repo->accessible($announcement);
        
        $designation_id        = ($announcement->audience === 'staff') ? $announcement->designation()->pluck('designation_id')->all() : [];
        $selected_designations = ($announcement->audience === 'staff') ? generateSelectOption($this->designation->listAllFilterById($designation_id)) : '';
        $attachments           =  $this->upload->getAttachment($this->module, $announcement->id);

        return $this->success(compact('announcement', 'designation_id', 'selected_designations', 'attachments'));
    }

    /**
     * Used to update Announcement
     * @patch ("/api/announcement/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Announcement"),
     *      @Parameter("start_date", type="date", required="true", description="Start date of Announcement"),
     *      @Parameter("end_date", type="date", required="true", description="End date of Announcement"),
     *      @Parameter("audience", type="string", required="true", description="Type of audience for Announcement"),
     *      @Parameter("designation_id", type="array", required="conditional", description="Designations for Announcement if audience is staff"),
     * })
     * @return Response
     */
    public function update(AnnouncementRequest $request, $id)
    {
        $this->authorize('update', Announcement::class);

        $announcement = $this->repo->findOrFail($id);

        $this->repo->accessible($announcement);

        $announcement = $this->repo->update($announcement, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $announcement->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('announcement.updated')]);
    }

    /**
     * Used to delete Announcement
     * @delete ("/api/announcement/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Announcement"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Announcement::class);

        $announcement = $this->repo->findOrFail($id);

        $this->repo->accessible($announcement);

        $this->upload->delete($this->module, $announcement->id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $announcement->id,
            'sub_module' => $announcement->title,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($announcement);

        return $this->success(['message' => trans('announcement.deleted')]);
    }

    /**
     * Used to download Announcement Attachment
     * @get ("/announcement/{id}/attachment/{attachment_uuid}/download")
     * @param ({
     *      @Parameter("id", type="string", required="true", description="Id of Announcement"),
     *      @Parameter("attachment_uuid", type="string", required="true", description="Unique Id of Attachment"),
     * })
     * @return Response download
     */
    public function download($id, $attachment_uuid)
    {
        $this->authorize('view', Announcement::class);

        $announcement = $this->repo->findOrFail($id);

        $this->repo->accessible($announcement);

        $attachment = $this->upload->getAttachment($this->module, $announcement->id, $attachment_uuid);

        try {
            \Storage::exists($attachment->filename);
        } catch (\Exception $e) {
        }

        $this->activity->record([
            'module'        => 'attachment',
            'module_id'     => $attachment->id,
            'sub_module'    => $this->module,
            'sub_module_id' => $announcement->id,
            'activity'      => 'downloaded'
        ]);

        $download_path = storage_path('app/'.$attachment->filename);
        return response()->download($download_path, $attachment->user_filename);
    }
}

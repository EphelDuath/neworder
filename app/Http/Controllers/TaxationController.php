<?php

namespace App\Http\Controllers;

use App\Taxation;
use Illuminate\Http\Request;
use App\Http\Requests\TaxationRequest;
use App\Repositories\TaxationRepository;
use App\Repositories\ActivityLogRepository;

class TaxationController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'taxation';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        TaxationRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;

        $this->middleware('permission:access-configuration')->except(['fetchDefault']);
    }

    /**
     * Used to get all Taxations
     * @get ("/api/taxation")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to fetch default Taxation
     * @get ("/api/taxation/fetch/default")
     * @return Response
     */
    public function fetchDefault()
    {
        return $this->ok($this->repo->default());
    }

    /**
     * Used to store Taxation
     * @post ("/api/taxation")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Taxation"),
     *      @Parameter("value", type="number", required="true", description="Value of Taxation in Percentage"),
     *      @Parameter("description", type="text", required="optional", description="Description of Taxation"),
     *      @Parameter("is_default", type="boolean", required="optional", description="Is taxation default?"),
     * })
     * @return Response
     */
    public function store(TaxationRequest $request)
    {
        $taxation = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $taxation->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('taxation.added')]);
    }

    /**
     * Used to get Taxation detail
     * @get ("/api/taxation/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Taxation"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Taxation
     * @patch ("/api/taxation/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Taxation"),
     *      @Parameter("name", type="string", required="true", description="Name of Taxation"),
     *      @Parameter("value", type="number", required="true", description="Value of Taxation in Percentage"),
     *      @Parameter("description", type="text", required="optional", description="Description of Taxation"),
     *      @Parameter("is_default", type="boolean", required="optional", description="Is taxation default?"),
     * })
     * @return Response
     */
    public function update(TaxationRequest $request, $id)
    {
        $taxation = $this->repo->findOrFail($id);
        
        $taxation = $this->repo->update($taxation, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $taxation->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('taxation.updated')]);
    }

    /**
     * Used to delete Taxation
     * @delete ("/api/taxation/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Taxation"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $taxation = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $taxation->id,
            'sub_module' => $taxation->detail,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($taxation);

        return $this->success(['message' => trans('taxation.deleted')]);
    }
}

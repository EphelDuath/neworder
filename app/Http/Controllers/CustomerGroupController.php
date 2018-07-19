<?php

namespace App\Http\Controllers;

use App\CustomerGroup;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerGroupRequest;
use App\Repositories\CustomerGroupRepository;
use App\Repositories\ActivityLogRepository;

class CustomerGroupController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'customer_group';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        CustomerGroupRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
        
        $this->middleware('permission:access-configuration');
    }

    /**
     * Used to get all Customer Groups
     * @get ("/api/customer-group")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Customer Group
     * @post ("/api/customer-group")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Customer Group"),
     *      @Parameter("description", type="text", required="optional", description="Description of Customer Group"),
     * })
     * @return Response
     */
    public function store(CustomerGroupRequest $request)
    {
        $customer_group = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $customer_group->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('customer_group.added')]);
    }

    /**
     * Used to get Customer Group detail
     * @get ("/api/customer-group/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Customer Group"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Customer Group
     * @patch ("/api/customer-group/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Customer Group"),
     *      @Parameter("name", type="string", required="true", description="Name of Customer Group"),
     *      @Parameter("description", type="text", required="optional", description="Description of Customer Group")
     * })
     * @return Response
     */
    public function update(CustomerGroupRequest $request, $id)
    {
        $customer_group = $this->repo->findOrFail($id);
        
        $customer_group = $this->repo->update($customer_group, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $customer_group->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('customer_group.updated')]);
    }

    /**
     * Used to delete Customer Group
     * @delete ("/api/customer-group/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Customer Group"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $customer_group = $this->repo->findOrFail($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $customer_group->id,
            'sub_module' => $customer_group->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($customer_group);

        return $this->success(['message' => trans('customer_group.deleted')]);
    }
}

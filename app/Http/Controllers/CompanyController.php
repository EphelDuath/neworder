<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\ActivityLogRepository;

class CompanyController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'company';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        CompanyRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
    }

    /**
     * Used to get all Companies
     * @get ("/api/company")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Company::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Company
     * @post ("/api/company")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Company"),
     *      @Parameter("email", type="email", required="true", description="Email of Company"),
     *      @Parameter("website", type="url", required="true", description="Website of Company"),
     *      @Parameter("phone", type="string", required="optional", description="Phone of Company"),
     *      @Parameter("fax", type="string", required="optional", description="Fax of Company"),
     *      @Parameter("address_line_1", type="string", required="optional", description="Address Line 1 of Company"),
     *      @Parameter("address_line_2", type="string", required="optional", description="Address Line 2 of Company"),
     *      @Parameter("city", type="string", required="optional", description="City of Company"),
     *      @Parameter("state", type="string", required="optional", description="State of Company"),
     *      @Parameter("zipcode", type="string", required="optional", description="Zipcode of Company"),
     *      @Parameter("country_id", type="string", required="optional", description="Country of Company"),
     * })
     * @return Response
     */
    public function store(CompanyRequest $request)
    {
        $this->authorize('create', Company::class);

        $company = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $company->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('company.added')]);
    }

    /**
     * Used to get Company detail
     * @get ("/api/company/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Company"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('view', Company::class);

        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Company
     * @patch ("/api/company/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Company"),
     *      @Parameter("name", type="string", required="true", description="Name of Company"),
     *      @Parameter("email", type="email", required="true", description="Email of Company"),
     *      @Parameter("website", type="url", required="true", description="Website of Company"),
     *      @Parameter("phone", type="string", required="optional", description="Phone of Company"),
     *      @Parameter("fax", type="string", required="optional", description="Fax of Company"),
     *      @Parameter("address_line_1", type="string", required="optional", description="Address Line 1 of Company"),
     *      @Parameter("address_line_2", type="string", required="optional", description="Address Line 2 of Company"),
     *      @Parameter("city", type="string", required="optional", description="City of Company"),
     *      @Parameter("state", type="string", required="optional", description="State of Company"),
     *      @Parameter("zipcode", type="string", required="optional", description="Zipcode of Company"),
     *      @Parameter("country_id", type="string", required="optional", description="Country of Company"),
     * })
     * @return Response
     */
    public function update(CompanyRequest $request, $id)
    {
        $this->authorize('update', Company::class);

        $company = $this->repo->findOrFail($id);
        
        $company = $this->repo->update($company, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $company->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('company.updated')]);
    }

    /**
     * Used to delete Company
     * @delete ("/api/company/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Company"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Company::class);

        $company = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $company->id,
            'sub_module' => $company->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($company);

        return $this->success(['message' => trans('company.deleted')]);
    }
}

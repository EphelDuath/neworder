<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;
use App\Repositories\SupplierRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ActivityLogRepository;

class SupplierController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $company;
    protected $module = 'supplier';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        SupplierRepository $repo,
        ActivityLogRepository $activity,
        CompanyRepository $company
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
        $this->company  = $company;
    }

    /**
     * Used to fetch Pre-Requisites for supplier
     * @get ("/api/supplier/pre-requisite")
     * @return Response
     */
    public function preRequisite()
    {
        $countries = generateNormalSelectOption(getVar('country'));
        $companies = $this->company->selectAll();

        return $this->success(compact('countries', 'companies'));
    }

    /**
     * Used to get all Suppliers
     * @get ("/api/supplier")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Supplier::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Supplier
     * @post ("/api/supplier")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Supplier"),
     *      @Parameter("email", type="email", required="true", description="Email of Supplier"),
     *      @Parameter("company_id", type="integer", required="optional", description="Company of Supplier"),
     *      @Parameter("phone", type="string", required="optional", description="Phone of Supplier"),
     *      @Parameter("address_line_1", type="string", required="optional", description="Address Line 1 of Supplier"),
     *      @Parameter("address_line_2", type="string", required="optional", description="Address Line 2 of Supplier"),
     *      @Parameter("city", type="string", required="optional", description="City of Supplier"),
     *      @Parameter("state", type="string", required="optional", description="State of Supplier"),
     *      @Parameter("zipcode", type="string", required="optional", description="Zipcode of Supplier"),
     *      @Parameter("country_id", type="string", required="optional", description="Country of Supplier"),
     * })
     * @return Response
     */
    public function store(SupplierRequest $request)
    {
        $this->authorize('create', Supplier::class);

        $supplier = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $supplier->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('supplier.added')]);
    }

    /**
     * Used to get Supplier detail
     * @get ("/api/supplier/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Supplier"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('view', Supplier::class);

        $supplier = $this->repo->findOrFail($id);

        $selected_company = ($supplier->Company) ? ['id' => $supplier->company_id, 'name' => $supplier->Company->name] : [];

        return $this->success(compact('supplier','selected_company'));
    }

    /**
     * Used to update Supplier
     * @patch ("/api/supplier/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Supplier"),
     *      @Parameter("name", type="string", required="true", description="Name of Supplier"),
     *      @Parameter("email", type="email", required="true", description="Email of Supplier"),
     *      @Parameter("company_id", type="integer", required="optional", description="Company of Supplier"),
     *      @Parameter("phone", type="string", required="optional", description="Phone of Supplier"),
     *      @Parameter("address_line_1", type="string", required="optional", description="Address Line 1 of Supplier"),
     *      @Parameter("address_line_2", type="string", required="optional", description="Address Line 2 of Supplier"),
     *      @Parameter("city", type="string", required="optional", description="City of Supplier"),
     *      @Parameter("state", type="string", required="optional", description="State of Supplier"),
     *      @Parameter("zipcode", type="string", required="optional", description="Zipcode of Supplier"),
     *      @Parameter("country_id", type="string", required="optional", description="Country of Supplier"),
     * @return Response
     */
    public function update(SupplierRequest $request, $id)
    {
        $this->authorize('update', Supplier::class);

        $supplier = $this->repo->findOrFail($id);
        
        $supplier = $this->repo->update($supplier, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $supplier->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('supplier.updated')]);
    }

    /**
     * Used to delete Supplier
     * @delete ("/api/supplier/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Supplier"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Supplier::class);

        $supplier = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $supplier->id,
            'sub_module' => $supplier->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($supplier);

        return $this->success(['message' => trans('supplier.deleted')]);
    }
}

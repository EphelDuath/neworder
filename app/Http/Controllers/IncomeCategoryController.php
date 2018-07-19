<?php

namespace App\Http\Controllers;

use App\IncomeCategory;
use Illuminate\Http\Request;
use App\Http\Requests\IncomeCategoryRequest;
use App\Repositories\IncomeCategoryRepository;
use App\Repositories\ActivityLogRepository;

class IncomeCategoryController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'income_category';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        IncomeCategoryRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
        
        $this->middleware('permission:access-configuration');
    }

    /**
     * Used to get all Income Categories
     * @get ("/api/income-category")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Income Category
     * @post ("/api/income-category")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Income Category"),
     *      @Parameter("description", type="text", required="optional", description="Description of Income Category"),
     * })
     * @return Response
     */
    public function store(IncomeCategoryRequest $request)
    {
        $income_category = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $income_category->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('transaction.transaction_category_added', ['type' => trans('transaction.income')])]);
    }

    /**
     * Used to get Income Category detail
     * @get ("/api/income-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Income Category"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Income Category
     * @patch ("/api/income-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Income Category"),
     *      @Parameter("name", type="string", required="true", description="Name of Income Category"),
     *      @Parameter("description", type="text", required="optional", description="Description of Income Category")
     * })
     * @return Response
     */
    public function update(IncomeCategoryRequest $request, $id)
    {
        $income_category = $this->repo->findOrFail($id);
        
        $income_category = $this->repo->update($income_category, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $income_category->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('transaction.transaction_category_updated', ['type' => trans('transaction.income')])]);
    }

    /**
     * Used to delete Income Category
     * @delete ("/api/income-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Income Category"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $income_category = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $income_category->id,
            'sub_module' => $income_category->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($income_category);

        return $this->success(['message' => trans('transaction.transaction_category_deleted', ['type' => trans('transaction.income')])]);
    }
}

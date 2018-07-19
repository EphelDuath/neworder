<?php

namespace App\Http\Controllers;

use App\ExpenseCategory;
use Illuminate\Http\Request;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Repositories\ExpenseCategoryRepository;
use App\Repositories\ActivityLogRepository;

class ExpenseCategoryController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'expense_category';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        ExpenseCategoryRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
        
        $this->middleware('permission:access-configuration');
    }

    /**
     * Used to get all Expense Categories
     * @get ("/api/expense-category")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Expense Category
     * @post ("/api/expense-category")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Expense Category"),
     *      @Parameter("description", type="text", required="optional", description="Description of Expense Category"),
     * })
     * @return Response
     */
    public function store(ExpenseCategoryRequest $request)
    {
        $expense_category = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $expense_category->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('transaction.transaction_category_added', ['type' => trans('transaction.expense')])]);
    }

    /**
     * Used to get Expense Category detail
     * @get ("/api/expense-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Expense Category"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Expense Category
     * @patch ("/api/expense-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Expense Category"),
     *      @Parameter("name", type="string", required="true", description="Name of Expense Category"),
     *      @Parameter("description", type="text", required="optional", description="Description of Expense Category")
     * })
     * @return Response
     */
    public function update(ExpenseCategoryRequest $request, $id)
    {
        $expense_category = $this->repo->findOrFail($id);
        
        $expense_category = $this->repo->update($expense_category, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $expense_category->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('transaction.transaction_category_updated', ['type' => trans('transaction.expense')])]);
    }

    /**
     * Used to delete Expense Category
     * @delete ("/api/expense-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Expense Category"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $expense_category = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $expense_category->id,
            'sub_module' => $expense_category->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($expense_category);

        return $this->success(['message' => trans('transaction.transaction_category_deleted', ['type' => trans('transaction.expense')])]);
    }
}

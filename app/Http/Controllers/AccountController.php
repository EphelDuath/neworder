<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;
use App\Http\Requests\AccountRequest;
use App\Repositories\AccountRepository;
use App\Repositories\ActivityLogRepository;

class AccountController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'account';

    public function __construct(
        Request $request,
        AccountRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
    }

    /**
     * Used to get all Accounts
     * @get ("/api/account")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Account::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Account
     * @post ("/api/account")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Account"),
     *      @Parameter("opening_balance", type="numeric", required="true", description="Opening Balance of Account"),
     *      @Parameter("type", type="string", required="true", description="Type of Account, can be bank or cash"),
     *      @Parameter("bank_name", type="string", required="conditional", description="Name of Bank if type of account is bank"),
     *      @Parameter("number", type="string", required="optional", description="Account Number of Bank if type of account is bank"),
     *      @Parameter("bank_branch", type="string", required="optional", description="Branch of Bank if type of account is bank"),
     *      @Parameter("branch_code", type="string", required="optional", description="Branch Code of Bank if type of account is bank"),
     * })
     * @return Response
     */
    public function store(AccountRequest $request)
    {
        $this->authorize('create', Account::class);

        $account = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $account->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('account.added')]);
    }

    /**
     * Used to get Account detail
     * @get ("/api/account/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Account"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('view', Account::class);

        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Account
     * @patch ("/api/account/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Account"),
     *      @Parameter("name", type="string", required="true", description="Name of Account"),
     *      @Parameter("opening_balance", type="numeric", required="true", description="Opening Balance of Account"),
     *      @Parameter("type", type="string", required="true", description="Type of Account, can be bank or cash"),
     *      @Parameter("bank_name", type="string", required="conditional", description="Name of Bank if type of account is bank"),
     *      @Parameter("number", type="string", required="optional", description="Account Number of Bank if type of account is bank"),
     *      @Parameter("bank_branch", type="string", required="optional", description="Branch of Bank if type of account is bank"),
     *      @Parameter("branch_code", type="string", required="optional", description="Branch Code of Bank if type of account is bank"),
     * })
     * @return Response
     */
    public function update(AccountRequest $request, $id)
    {
        $this->authorize('update', Account::class);

        $account = $this->repo->findOrFail($id);
        
        $account = $this->repo->update($account, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $account->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('account.updated')]);
    }

    /**
     * Used to delete Account
     * @delete ("/api/account/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Account"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Account::class);

        $account = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $account->id,
            'sub_module' => $account->name,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($account);

        return $this->success(['message' => trans('account.deleted')]);
    }
}

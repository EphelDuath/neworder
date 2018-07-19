<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;
use App\Http\Requests\CurrencyRequest;
use App\Repositories\CurrencyRepository;
use App\Repositories\ActivityLogRepository;

class CurrencyController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'currency';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        CurrencyRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;

        $this->middleware('permission:access-configuration')->except(['fetchDefault']);
    }

    /**
     * Used to get all Currencies
     * @get ("/api/currency")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to fetch default Currency
     * @get ("/api/currency/fetch/default")
     * @return Response
     */
    public function fetchDefault()
    {
        return $this->ok($this->repo->default());
    }

    /**
     * Used to store Currency
     * @post ("/api/currency")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Currency"),
     *      @Parameter("symbol", type="string", required="true", description="Symbol of Currency"),
     *      @Parameter("position", type="string", required="true", description="Display position of Currency, can be prefixed or suffixed"),
     *      @Parameter("decimal_place", type="integer", required="true", description="Number of decimal place in Currency, min 0 & max 4"),
     *      @Parameter("is_default", type="boolean", required="optional", description="Is currency default?"),
     * })
     * @return Response
     */
    public function store(CurrencyRequest $request)
    {
        $currency = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $currency->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('currency.added')]);
    }

    /**
     * Used to get Currency detail
     * @get ("/api/currency/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Currency"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Currency
     * @patch ("/api/currency/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Currency"),
     *      @Parameter("name", type="string", required="true", description="Name of Currency"),
     *      @Parameter("symbol", type="string", required="true", description="Symbol of Currency"),
     *      @Parameter("position", type="string", required="true", description="Display position of Currency, can be prefixed or suffixed"),
     *      @Parameter("decimal_place", type="integer", required="true", description="Number of decimal place in Currency, min 0 & max 4"),
     *      @Parameter("is_default", type="boolean", required="optional", description="Is currency default?"),
     * })
     * @return Response
     */
    public function update(CurrencyRequest $request, $id)
    {
        $currency = $this->repo->findOrFail($id);
        
        $currency = $this->repo->update($currency, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $currency->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('currency.updated')]);
    }

    /**
     * Used to delete Currency
     * @delete ("/api/currency/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Currency"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $currency = $this->repo->deletable($id);

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $currency->id,
            'sub_module' => $currency->detail,
            'activity'  => 'deleted'
        ]);

        $this->repo->delete($currency);

        return $this->success(['message' => trans('currency.deleted')]);
    }
}

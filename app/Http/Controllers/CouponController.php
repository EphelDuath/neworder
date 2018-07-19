<?php

namespace App\Http\Controllers;

use App\Coupon;
use Illuminate\Http\Request;
use App\Http\Requests\CouponRequest;
use App\Repositories\CouponRepository;
use App\Repositories\ActivityLogRepository;

class CouponController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'coupon';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        CouponRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;

        $this->middleware('feature.available:coupon');
    }

    /**
     * Used to get all Coupons
     * @get ("/api/coupon")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Coupon::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Coupon
     * @post ("/api/coupon")
     * @param ({
     *      @Parameter("code", type="string", required="true", description="Code of Coupon"),
     *      @Parameter("discount", type="numeric", required="true", description="Discount of Coupon"),
     *      @Parameter("valid_start_date", type="date", required="true", description="Start date of validity of Coupon"),
     *      @Parameter("valid_end_date", type="date", required="true", description="End date of validity of Coupon"),
     *      @Parameter("max_use_count", type="integer", required="true", description="Max number of use of coupon"),
     *      @Parameter("description", type="text", required="optional", description="Coupon Description"),
     *      @Parameter("new_user", type="boolean", required="optional", description="Is only applicable for new user?"),
     *      @Parameter("valid_days", type="array", required="optional", description="Is only applicable for selected days?"),
     * })
     * @return Response
     */
    public function store(CouponRequest $request)
    {
        $this->authorize('create', Coupon::class);

        $coupon = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $coupon->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('coupon.added')]);
    }

    /**
     * Used to get Coupon detail
     * @get ("/api/coupon/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Coupon"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('view', Coupon::class);

        $coupon = $this->repo->findOrFail($id);
        
        $valid_days = explode(',', $coupon->valid_days);
        $selected_days = array();
        foreach ($valid_days as $valid_day) {
            $selected_days[] = array('id' => $valid_day, 'name' => trans('list.'.$valid_day));
        }

        return $this->success(compact('coupon', 'valid_days', 'selected_days'));
    }

    /**
     * Used to update Coupon
     * @patch ("/api/coupon/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Coupon"),
     *      @Parameter("code", type="string", required="true", description="Code of Coupon"),
     *      @Parameter("discount", type="numeric", required="true", description="Discount of Coupon"),
     *      @Parameter("valid_start_date", type="date", required="true", description="Start date of validity of Coupon"),
     *      @Parameter("valid_end_date", type="date", required="true", description="End date of validity of Coupon"),
     *      @Parameter("max_use_count", type="integer", required="true", description="Max number of use of coupon"),
     *      @Parameter("description", type="text", required="optional", description="Coupon Description"),
     *      @Parameter("new_user", type="boolean", required="optional", description="Is only applicable for new user?"),
     *      @Parameter("valid_days", type="array", required="optional", description="Is only applicable for selected days?"),
     * })
     * @return Response
     */
    public function update(CouponRequest $request, $id)
    {
        $this->authorize('update', Coupon::class);

        $coupon = $this->repo->findOrFail($id);
        
        $coupon = $this->repo->update($coupon, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $coupon->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('coupon.updated')]);
    }

    /**
     * Used to delete Coupon
     * @delete ("/api/coupon/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Coupon"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Coupon::class);

        $coupon = $this->repo->findOrFail($id);

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $coupon->id,
            'sub_module' => $coupon->code,
            'activity'  => 'deleted'
        ]);

        $this->repo->delete($coupon);

        return $this->success(['message' => trans('coupon.deleted')]);
    }
}

<?php

namespace App\Http\Controllers;

use App\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentMethodRequest;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ActivityLogRepository;

class PaymentMethodController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'payment_method';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        PaymentMethodRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
        
        $this->middleware('permission:access-configuration');
    }

    /**
     * Used to get all Payment Methods
     * @get ("/api/payment-method")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Payment Method
     * @post ("/api/payment-method")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Payment Method"),
     *      @Parameter("type", type="string", required="true", description="Type of Payment Method, can be income, expense or account transfer"),
     *      @Parameter("description", type="text", required="optional", description="Description of Payment Method"),
     * })
     * @return Response
     */
    public function store(PaymentMethodRequest $request)
    {
        $payment_method = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $payment_method->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('payment.payment_method_added')]);
    }

    /**
     * Used to get Payment Method detail
     * @get ("/api/payment-method/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Payment Method"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Payment Method
     * @patch ("/api/payment-method/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Payment Method"),
     *      @Parameter("name", type="string", required="true", description="Name of Payment Method"),
     *      @Parameter("type", type="string", required="true", description="Type of Payment Method, can be income, expense or account transfer"),
     *      @Parameter("description", type="text", required="optional", description="Description of Payment Method")
     * })
     * @return Response
     */
    public function update(PaymentMethodRequest $request, $id)
    {
        $payment_method = $this->repo->findOrFail($id);
        
        $payment_method = $this->repo->update($payment_method, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $payment_method->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('payment.payment_method_updated')]);
    }

    /**
     * Used to delete Payment Method
     * @delete ("/api/payment-method/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Payment Method"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $payment_method = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $payment_method->id,
            'sub_module' => $payment_method->detail,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($payment_method);

        return $this->success(['message' => trans('payment.payment_method_deleted')]);
    }
}

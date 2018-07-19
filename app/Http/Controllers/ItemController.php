<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;
use App\Http\Requests\ItemRequest;
use App\Repositories\ItemRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\TaxationRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\ItemCategoryRepository;

class ItemController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $item_category;
    protected $taxation;
    protected $currency;
    protected $module = 'item';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        ItemRepository $repo,
        ActivityLogRepository $activity,
        ItemCategoryRepository $item_category,
        TaxationRepository $taxation,
        CurrencyRepository $currency
    ) {
        $this->request       = $request;
        $this->repo          = $repo;
        $this->activity      = $activity;
        $this->item_category = $item_category;
        $this->taxation      = $taxation;
        $this->currency      = $currency;
    }

    /**
     * Used to fetch Pre-Requisites for Item
     * @get ("/api/item/pre-requisite")
     * @return Response
     */
    public function preRequisite()
    {
        $item_categories = generateSelectOption($this->item_category->listAll());
        $taxations       = generateSelectOption($this->taxation->listAll());
        $currencies      = $this->currency->getAll();

        return $this->success(compact('currencies', 'item_categories', 'taxations'));
    }

    /**
     * Used to get all Items
     * @get ("/api/item")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Item::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Item
     * @post ("/api/item")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Item"),
     *      @Parameter("description", type="text", required="optional", description="Item description"),
     *      @Parameter("code", type="string", required="true", description="Code of Currency"),
     *      @Parameter("item_category_id", type="integer", required="true", description="Category of Item"),
     *      @Parameter("taxation_id", type="integer", required="true", description="Default tax applied on Item"),
     *      @Parameter("discount", type="numeric", required="true", description="Default discount applied on Item"),
     *      @Parameter("price_{currency_name}", type="numeric", required="true", description="Default price of Item"),
     * })
     * @return Response
     */
    public function store(ItemRequest $request)
    {
        $this->authorize('create', Item::class);

        $item = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $item->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('item.item_added')]);
    }

    /**
     * Used to get Item detail
     * @get ("/api/item/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Item"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('view', Item::class);

        $item = $this->repo->findOrFail($id);

        $selected_item_category = ['id' => $item->item_category_id, 'name' => $item->ItemCategory->detail];
        $selected_taxation      = ($item->Taxation) ? ['id' => $item->taxation_id, 'name' => $item->Taxation->detail] : [];

        return $this->success(compact('item', 'selected_item_category', 'selected_taxation'));
    }

    /**
     * Used to update Item
     * @patch ("/api/item/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Item"),
     *      @Parameter("name", type="string", required="true", description="Name of Item"),
     *      @Parameter("description", type="text", required="optional", description="Item description"),
     *      @Parameter("code", type="string", required="true", description="Code of Currency"),
     *      @Parameter("item_category_id", type="integer", required="true", description="Category of Item"),
     *      @Parameter("taxation_id", type="integer", required="true", description="Default tax applied on Item"),
     *      @Parameter("discount", type="numeric", required="true", description="Default discount applied on Item"),
     *      @Parameter("price_{currency_name}", type="numeric", required="true", description="Default price of Item"),
     * })
     * @return Response
     */
    public function update(ItemRequest $request, $id)
    {
        $this->authorize('update', Item::class);

        $item = $this->repo->findOrFail($id);
        
        $item = $this->repo->update($item, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $item->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('item.item_updated')]);
    }

    /**
     * Used to delete Item
     * @delete ("/api/item/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Item"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', Item::class);

        $item = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $item->id,
            'sub_module' => $item->detail,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($item);

        return $this->success(['message' => trans('item.item_deleted')]);
    }
}

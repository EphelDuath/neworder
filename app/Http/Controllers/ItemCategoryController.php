<?php

namespace App\Http\Controllers;

use App\ItemCategory;
use Illuminate\Http\Request;
use App\Http\Requests\ItemCategoryRequest;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ActivityLogRepository;

class ItemCategoryController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $module = 'item_category';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        ItemCategoryRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;
        
        $this->middleware('permission:access-configuration');
    }

    /**
     * Used to get all Item Categories
     * @get ("/api/item-category")
     * @return Response
     */
    public function index()
    {
        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Item Category
     * @post ("/api/item-category")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Item Category"),
     *      @Parameter("type", type="string", required="true", description="Type of Item Category, can be product or service"),
     *      @Parameter("description", type="text", required="optional", description="Description of Item Category"),
     * })
     * @return Response
     */
    public function store(ItemCategoryRequest $request)
    {
        $item_category = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $item_category->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('item.item_category_added')]);
    }

    /**
     * Used to get Item Category detail
     * @get ("/api/item-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Item Category"),
     * })
     * @return Response
     */
    public function show($id)
    {
        return $this->ok($this->repo->findOrFail($id));
    }

    /**
     * Used to update Item Category
     * @patch ("/api/item-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Item Category"),
     *      @Parameter("name", type="string", required="true", description="Name of Item Category"),
     *      @Parameter("type", type="string", required="true", description="Type of Item Category, can be product or service"),
     *      @Parameter("description", type="text", required="optional", description="Description of Item Category")
     * })
     * @return Response
     */
    public function update(ItemCategoryRequest $request, $id)
    {
        $item_category = $this->repo->findOrFail($id);
        
        $item_category = $this->repo->update($item_category, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $item_category->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('item.item_category_updated')]);
    }

    /**
     * Used to delete Item Category
     * @delete ("/api/item-category/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Item Category"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $item_category = $this->repo->deletable($id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $item_category->id,
            'sub_module' => $item_category->detail,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($item_category);

        return $this->success(['message' => trans('item.item_category_deleted')]);
    }
}

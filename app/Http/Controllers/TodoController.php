<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Todo;
use Illuminate\Http\Request;
use App\Http\Requests\TodoRequest;
use App\Repositories\TodoRepository;
use App\Repositories\ActivityLogRepository;

class TodoController extends Controller
{
    protected $module = 'todo';

    private $request;
    private $repo;
    protected $activity;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        TodoRepository $repo,
        ActivityLogRepository $activity
    ) {
        $this->request  = $request;
        $this->repo     = $repo;
        $this->activity = $activity;

        $this->middleware('feature.available:todo');
    }

    /**
     * Used to get all Todos
     * @get ("/api/todo")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Todo::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Todo
     * @post ("/api/todo")
     * @param ({
     *      @Parameter("title", type="string", required="true", description="Title of Todo"),
     *      @Parameter("date", type="date", required="true", description="Due date of Todo"),
     * })
     * @return Response
     */
    public function store(TodoRequest $request)
    {
        $this->authorize('create', Todo::class);

        $todo = $this->repo->create($this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $todo->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('todo.added')]);
    }

    /**
     * Used to get Todo detail
     * @get ("/api/todo/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Todo"),
     * })
     * @return Response
     */
    public function show($id)
    {
        $todo = $this->repo->findOrFail($id);

        $this->authorize('view', $todo);

        return $this->ok($todo);
    }

    /**
     * Used to update Todo status
     * @post ("/api/todo/{id}/status")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Todo"),
     * })
     * @return Response
     */
    public function toggleStatus($id)
    {
        $todo = $this->repo->findOrFail($id);

        $this->authorize('update', $todo);

        $todo = $this->repo->toggle($todo);

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $todo->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('todo.updated'),'todo' => $todo]);
    }

    /**
     * Used to update Todo
     * @patch ("/api/todo/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Todo"),
     *      @Parameter("title", type="string", required="true", description="Title of Todo"),
     *      @Parameter("date", type="date", required="true", description="Due date of Todo"),
     * })
     * @return Response
     */
    public function update($id, TodoRequest $request)
    {
        $todo = $this->repo->findOrFail($id);

        $this->authorize('update', $todo);

        $todo = $this->repo->update($todo, $this->request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $todo->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('todo.updated')]);
    }

    /**
     * Used to delete Todo
     * @delete ("/api/todo/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Todo"),
     * })
     * @return Response
     */
    public function destroy($id)
    {
        $todo = $this->repo->findOrFail($id);

        $this->authorize('delete', $todo);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $todo->id,
            'sub_module' => $todo->title,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($todo);

        return $this->success(['message' => trans('todo.deleted')]);
    }

    /**
     * Used to get recent Todo
     * @post ("/api/todo/recent")
     * @return Response
     */
    public function recent(){
        $pending_todos = $this->repo->getQuery()->filterByUserId(\Auth::user()->id)->filterByStatus(0)->orderBy('created_at','desc')->take(5)->get();
        $completed_todos = $this->repo->getQuery()->filterByUserId(\Auth::user()->id)->filterByStatus(1)->orderBy('completed_at','desc')->take(5)->get();

        return $this->success(compact('pending_todos','completed_todos'));
    }
}

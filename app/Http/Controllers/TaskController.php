<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Http\Requests\AddTaskDependentsRequest;
use App\Http\Requests\ChangeTaskStatusRequest;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Services\Task\ListTasks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Tasks'), Authenticated]

class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get Tasks
     *
     * Retrieve tasks with optional filters.
     *
     * Filters:
     * - `status`: pending, in_progress, completed, cancelled
     * - `title`: partial match
     * - `owner_id`: filter by owner
     * - `assignee_id`: filter by assigned user
     * - `due_date_from`, `due_date_to`: filter by due date range
     *
     * - Example of properties:
     *     - "depends_on_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *
     *     - "dependents_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *     - "assignees": [
     *  {
     *      "id": 4,
     *      "name": "Michael Murazik III",
     *      "email": "harber.hazle@example.com",
     *      "role": "user"
     *  }
     * ],
     *
     * - Access Level: user (own tasks), manager, admin (all)
     */
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: true)]
    public function index(TaskFilterRequest $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $tasks = (new ListTasks)->execute(
            $user,
            $request->input('status', ''),
            $request->input('title', ''),
            $request->input('owner_id', 0),
            $request->input('assignee_id', 0),
            $request->input('due_date_from', ''),
            $request->input('due_date_to', ''),
            $request->input('per_page', 15),
        );

        return TaskResource::collection($tasks);
    }

    /**
     * Get Task
     *
     * - Get a single task details using the task id
     *
     * - Example of properties:
     *     - "depends_on_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *
     *     - "dependents_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *     - "assignees": [
     *  {
     *      "id": 4,
     *      "name": "Michael Murazik III",
     *      "email": "harber.hazle@example.com",
     *      "role": "user"
     *  }
     * ],
     *
     * - Access Level: Admin , Manager , User(if assigned)
     */
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false, ), UrlParam(name: 'id', type: 'int', description: 'The desired task id')]
    public function show(Request $request, int $id): TaskResource
    {

        $user = $request->user();
        $query = Task::query();

        $task = $this->limitUserVisibility($user, $query)->where('id', $id)->first();

        if (! $task) {
            abort(404, 'Task not found!');
        }

        return new TaskResource($task);
    }

    /**
     * Create New Task
     *
     * - Creates a new task with assignees (users).
     * - user cannot be entered twice.
     * - Default status (Pending)
     *
     * - Example of properties:
     *     - "depends_on_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *
     *     - "dependents_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *     - "assignees": [
     *  {
     *      "id": 4,
     *      "name": "Michael Murazik III",
     *      "email": "harber.hazle@example.com",
     *      "role": "user"
     *  }
     * ],
     *
     * - Access Level: Admin , Manager
     */
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false, )]
    public function store(CreateTaskRequest $request): TaskResource
    {
        $user = $request->user();

        $data = $request->validated();

        $task = $user->createdTasks()->create($data);

        if (isset($data['assignees_ids'])) {

            if (isset($data['assignees_ids'])) {
                $task->assignees()->syncWithoutDetaching($data['assignees_ids']);
            }
        }

        return new TaskResource($task);
    }

    /**
     * Change Task Status
     *
     * - update the task status to --> (Pending , In Progress , Completed , Cancelled)
     *
     * - Example of properties:
     *   -  "depends_on_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *
     *    - "dependents_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *    - "assignees": [
     *  {
     *      "id": 4,
     *      "name": "Michael Murazik III",
     *      "email": "harber.hazle@example.com",
     *      "role": "user"
     *  }
     * ],
     *
     * - Access Level: N/A
     * - **NOTE** setting the task to cancelled is only limited to manager access level
     */
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false, )]
    public function changeStatus(ChangeTaskStatusRequest $request, $id): TaskResource
    {
        $data = $request->validated();

        $task = Task::with(['dependencies', 'dependents'])->findorFail($id);

        $this->authorize('update', $task);

        $isUser = $request->user()->hasRole(UserRole::USER);
        $unclosedDependents = $this->checkDependents($task);

        if ($isUser && $unclosedDependents) {
            abort(422, 'Action cannot be taken, please check for unclosed dependent tasks');
        }

        $task->update([
            'status' => $data['status'],
        ]);

        return new TaskResource($task);
    }

    /**
     * Update Task Data
     *
     * - update the task data (except for status and dependents located in separate endpoints)
     *
     * - Example of properties:
     *   -  "depends_on_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *
     *    - "dependents_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *    - "assignees": [
     *  {
     *      "id": 4,
     *      "name": "Michael Murazik III",
     *      "email": "harber.hazle@example.com",
     *      "role": "user"
     *  }
     * ],
     *
     *
     * - Access Level : Manager , Admin
     */
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false, )]
    public function update(UpdateTaskRequest $request, $id): TaskResource
    {
        $task = Task::findOrFail($id);
        $data = $request->validated();

        unset($data['status']);

        $task->update($data);

        if (isset($data['assignees_ids'])) {
            $task->assignees()->sync($data['assignees_ids']);
        }

        return new TaskResource($task);
    }

    /**
     * Add dependents
     *
     * - assign task to the parent task to make them dependent on it
     *
     * - Example of properties:
     *   -  "depends_on_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *
     *
     *    - "dependents_links": [
     *           "id": 8,
     *           "title": "Accusamus expedita nihil molestiae culpa blanditiis laboriosam laborum.",
     *           "link": "http://localhost:8000/api/tasks/8"
     *       ],
     *    - "assignees": [
     *  {
     *      "id": 4,
     *      "name": "Michael Murazik III",
     *      "email": "harber.hazle@example.com",
     *      "role": "user"
     *  }
     * ],
     *
     *
     * - Access Level : Manager , Admin
     */
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false, )]
    public function addDependents(AddTaskDependentsRequest $request, $id)
    {
        $task = Task::findOrFail($id);
        $data = $request->validated();

        foreach ($data['dependent_tasks_ids'] as $dependentId) {
            $dependent = Task::find($dependentId);

            if ($dependent->id === $task->id) {
                abort(422, 'You cannot make the task dependent on itself');
            }
        }

        $task->dependents()->sync($data['dependent_tasks_ids']);

        return new TaskResource($task);
    }

    /**
     * Limit Visibility
     *
     * private function used to limit the visibility of the tasks for users
     *
     * @hideFromAPIDocumentation
     */
    private function limitUserVisibility(User $user, $query): Builder
    {
        if ($user->hasRole(UserRole::USER)) {
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query;
    }

    /**
     * Check Dependent Tasks Status
     *
     * private function used to check for unclosed tasks
     *
     *
     * @hideFromAPIDocumentation
     */
    private function checkDependents(Task $task): bool
    {
        $dependents = $task->dependents()->get();

        if ($dependents->count() == 0) {
            return false;
        }

        foreach ($dependents as $dependent) {
            if ($dependent->status == TaskStatus::PENDING || $dependent->status == TaskStatus::IN_PROGRESS) {
                return true;
            }
        }

        return false;
    }
}

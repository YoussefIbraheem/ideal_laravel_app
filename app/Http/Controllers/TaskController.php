<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use App\Services\Task\ShowTask;
use App\Services\Task\ListTasks;
use App\Services\Task\StoreTask;
use App\Services\Task\UpdateTask;
use App\Http\Resources\TaskResource;
use Knuckles\Scribe\Attributes\Group;
use App\Services\Task\ChangeTaskStatus;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\Task\AddTaskDependents;
use Knuckles\Scribe\Attributes\UrlParam;
use Illuminate\Database\Eloquent\Builder;
use Knuckles\Scribe\Attributes\Authenticated;
use App\Http\Requests\ChangeTaskStatusRequest;
use App\Http\Requests\AddTaskDependentsRequest;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
            user: $user,
            status: $request->input('status', ''),
            title: $request->input('title', ''),
            owner_id: $request->input('owner_id', 0),
            assignee_id: $request->input('assignee_id', 0),
            due_date_from: $request->input('due_date_from', ''),
            due_date_to: $request->input('due_date_to', ''),
            per_page: $request->input('per_page', 15),
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
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false,), UrlParam(name: 'id', type: 'int', description: 'The desired task id')]
    public function show(Request $request, int $id): TaskResource
    {

        $user = $request->user();

        $task = (new ShowTask)->execute($id, $user);

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
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false,)]
    public function store(CreateTaskRequest $request): TaskResource
    {
        $user = $request->user();

        $data = $request->validated();

        $task = (new StoreTask)->execute(
            $data,
            $user
        );

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
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false,)]
    public function changeStatus(ChangeTaskStatusRequest $request, int $id): TaskResource
    {
        $data = $request->validated();
        $user = $request->user();

        $task = (new ChangeTaskStatus)->execute($id, $data, $user);

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
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false,)]
    public function update(UpdateTaskRequest $request, $id): TaskResource
    {
        $data = $request->validated();

        $task = (new UpdateTask)->execute($id, $data);

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
    #[ResponseFromApiResource(TaskResource::class, Task::class, collection: false,)]
    public function addDependents(AddTaskDependentsRequest $request, $id)
    {
        $data = $request->validated();
        $task = (new AddTaskDependents)->execute($id, $data);
        return new TaskResource($task);
    }
}

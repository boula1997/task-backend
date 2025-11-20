<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Repositories\Task\TaskRepositoryInterface;

class TaskController extends Controller
{
    private $tasks;

    public function __construct(TaskRepositoryInterface $tasks)
    {
        $this->tasks = $tasks;
    }

public function index(Request $request)
{
    $userId = auth('api')->user()->id;

    // Get filters from query params
    $title = $request->query('title');
    $description = $request->query('description');
    $priority = $request->query('priority');
    $status = $request->query('status'); // "completed" or "incomplete"
    $dueFrom = $request->query('dueFrom');
    $dueTo = $request->query('dueTo');

    $tasksQuery = $this->tasks->queryForUser($userId); // a query builder method in your repository

    if ($title) {
        $tasksQuery->where('title', 'like', "%$title%");
    }

    if ($description) {
        $tasksQuery->where('description', 'like', "%$description%");
    }

    if ($priority) {
        $tasksQuery->where('priority', $priority);
    }

    if ($status) {
        if ($status === 'completed') {
            $tasksQuery->where('is_completed', true);
        } elseif ($status === 'incomplete') {
            $tasksQuery->where('is_completed', false);
        }
    }

    if ($dueFrom) {
        $tasksQuery->whereDate('due_date', '>=', $dueFrom);
    }

    if ($dueTo) {
        $tasksQuery->whereDate('due_date', '<=', $dueTo);
    }

    $tasks = $tasksQuery->get();

    return successResponse($tasks);
}


    public function show($id)
    {
        $task = $this->tasks->find($id);
        if (!$task) {
            return failedResponse([], "Task not found", 404);
        }
        return successResponse($task);
    }

    public function store(TaskRequest $request)
    {
        $data = $request->all();
        $data['creator_id'] = auth('api')->user()->id;

        try {
            $task = $this->tasks->create($data);
            return successResponse($task, "Task created successfully", 201);
        } catch (\Exception $e) {
            return failedResponse([], $e->getMessage(), 400);
        }
    }

    public function update(TaskRequest $request, Task $task)
    {
        if ($task->assignee_id != $request->user()->id) {
            return failedResponse([], 'Forbidden', 403);
        }

        try {
            $updatedTask = $this->tasks->update($task, $request->all());
            return successResponse($updatedTask, "Task updated successfully");
        } catch (\Exception $e) {
            return failedResponse([], $e->getMessage(), 400);
        }
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->creator_id != $request->user()->id && $task->assignee_id != $request->user()->id) {
            return failedResponse([], 'Forbidden', 403);
        }

        $this->tasks->delete($task);
        return successResponse([], "Task deleted successfully");
    }
}

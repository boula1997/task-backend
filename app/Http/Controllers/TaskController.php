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

    public function index()
    {
        $tasks = $this->tasks->allForUser(auth('api')->user()->id);
        return response()->json($tasks);
    }

    public function show($id)
    {
        $task = $this->tasks->find($id);
        return response()->json($task);
    }

    public function store(TaskRequest $request)
    {
        $data = $request->all();
        $data['creator_id'] = auth('api')->user()->id;

        try {
            $task = $this->tasks->create($data);
            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(TaskRequest $request, Task $task)
    {
        if ($task->assignee_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $updatedTask = $this->tasks->update($task, $request->all());
            return response()->json($updatedTask);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->creator_id != $request->user()->id && $task->assignee_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->tasks->delete($task);
        return response()->json(['message' => 'Task deleted']);
    }
}

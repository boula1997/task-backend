<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::where('assignee_id', $request->user()->id)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'due_date' => 'required|date',
            'assignee_email' => 'required|email',
        ]);

        $assignee = User::where('email', $request->assignee_email)->first();
        if (!$assignee) {
            return response()->json(['message' => 'Assignee email not found'], 404);
        }

        $task = Task::create([
            'creator_id' => $request->user()->id,
            'assignee_id' => $assignee->id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'medium',
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        if ($task->assignee_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->update($request->only('title','description','due_date','priority','is_completed'));

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->creator_id != $request->user()->id && $task->assignee_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('assignee_id', auth('api')->user()->id)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($tasks);
    }

    public function show($id)
    {
        $task = Task::find($id);

        return response()->json($task);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'due_date' => 'required|date',
            'assignee_email' => 'required|email',
            'priority' => 'required',
        ]);

        $data=$request->all();
        $data["creator_id"]= auth('api')->user()->id;

        $assignee = User::where('email', $request->assignee_email)->first();

        if($assignee)
            $data["assignee_id"]= $assignee->id;
        else
            return response()->json(['message' => 'Assignee email not found'], 404);
        

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {

        $request->validate([
            'title' => 'required|string',
            'due_date' => 'required|date',
            'assignee_email' => 'nullable|email',
            'priority' => 'required',

        ]);

        if ($task->assignee_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data=$request->all();

        $assignee = User::where('email', $request->assignee_email)->first();

        if($assignee)
            $data["assignee_id"]= $assignee->id;
        else{
            if(isset($request->assignee_email))
                return response()->json(['message' => 'Assignee email not found'], 404);
            else
                $data["assignee_id"]= $task->assignee_id;
        }


        $task->update($data);

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

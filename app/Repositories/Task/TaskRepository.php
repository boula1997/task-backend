<?php

namespace App\Repositories\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function allForUser(int $userId): Collection
    {
        return Task::where('assignee_id', $userId)
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function find(int $id): ?Task
    {
        return Task::find($id);
    }

    public function queryForUser($userId)
{
    return Task::where('assignee_id', $userId)->orWhere('creator_id', $userId);
}

    public function create(array $data): Task
    {
        if (isset($data['assignee_email'])) {
            $assignee = User::where('email', $data['assignee_email'])->first();
            if (!$assignee) {
                throw new \Exception("Assignee email not found");
            }
            $data['assignee_id'] = $assignee->id;
        }

        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        if (isset($data['assignee_email'])) {
            $assignee = User::where('email', $data['assignee_email'])->first();
            if (!$assignee) {
                throw new \Exception("Assignee email not found");
            }
            $data['assignee_id'] = $assignee->id;
        }

        $task->update($data);
        return $task;
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}

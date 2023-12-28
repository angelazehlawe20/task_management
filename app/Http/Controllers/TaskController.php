<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\GeneralTrait;
use App\Models\Comment;
use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class TaskController extends Controller
{
    use GeneralTrait;

    public function getSortedTasks(Request $request)
    {
        $sortBy = $request->input('sort_by');

        $validSortOptions = ['priority', 'date', 'name','status'];

        if (!in_array($sortBy, $validSortOptions)) {
            return $this->ResponseTasksErrors('Invalid sorting parameter', 400);
        }

        $tasks = Task::query();

        switch ($sortBy) {
            case 'priority':
                $tasks->orderBy('priority')->orderBy('due_date');
                break;
            case 'date':
                $tasks->orderBy('due_date')->orderBy('priority');
                break;
            case 'name':
                $tasks->orderBy('title');
                break;
            case 'status':
                $tasks->orderBy('status');
                break;
            default:
               return $this->ResponseTasksErrors('Invalid sorting parameter', 400);
                break;
        }

        $sortedTasks = $tasks->get();

        return $this->ResponseTasks($sortedTasks,'All tasks sorted by ' . $sortBy, 200);
    }

    protected function getColorForPriority(Request $request)
    {
        $priority = $request->input('priority');
        $priorityColors = [
            'high' => '#FF0000',
            'medium' => '#FFFF00',
            'low' => '#00FF00'
        ];

        if (array_key_exists($priority, $priorityColors)) {
            return $priorityColors[$priority];
        }
        return $this->ResponseTasksErrors('Color of '.$priority.' priority not found',404);
    }

    public function createTask(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'priority' => 'required|in:high,medium,low',
                'title' => 'required|string',
                'description' => 'string',
                'due_date' => 'required|date_format:Y-m-d h:i:s'
            ]);
        } catch (ValidationException $e) {
            return $this->ResponseTasksErrors('Please ensure the accuracy of the provided information and fill in the required fields', 400);
        } catch (Exception $e) {
            return $this->ResponseTasksErrors('An error occurred while creating the task', 500);
        }

        try{
        $newTask = Task::create($validatedData);

        return $this->ResponseTasks($newTask,'Task created successfully', 201);
    }
    catch (\Exception $e) {
        return $this->ResponseTasksErrors('Failed to create task. Please try again.', 500);
    }
}


    public function show(Request $request,Task $task)
    {
        $one=Task::where('id',$request->id)->get();
        if($one->isEmpty())
        {
            return $this->ResponseTasksErrors('Task not found',404);
        }
        return $this->ResponseTasks($one,'Task retrieved successfully',200);
    }


    public function updateTask(Request $request, Task $task)
    {

        try{
            $validatedData=$request->validate([
                'id' => 'integer',
                'priority' => 'string|in:high,medium,low',
                'title' => 'string',
                'description' => 'string',
                'due_date' => 'date_format:Y-m-d h:i:s',
                'status'=> 'string|in:COMPLETED,IN_PROGRESS,PENDING'
            ]);
        }
        catch (ValidationException $e) {
            return $this->ResponseTasksErrors('Please ensure the accuracy of the provided information and fill in the required fields', 400);
        } catch (Exception $e) {
            return $this->ResponseTasksErrors('An error occurred while updating the task', 500);
        }
        $taskk=Task::find($validatedData['id']);
        if(!$taskk){
            return $this->ResponseTasksErrors('Task not found',404);
        }
            $taskk->update($validatedData);
            return $this->ResponseTasks($taskk,'Task updated successfully',200);
        }



public function updateStatus(Request $request)
{
    try{
    $validatedData = $request->validate([
        'id' => 'required',
        'status' => 'required|string|in:COMPLETED,IN_PROGRESS,PENDING'
    ]);
}
catch (ValidationException $e) {
    return $this->ResponseTasksErrors('Please ensure the accuracy of the provided information and fill in the required fields', 400);
} catch (Exception $e) {
    return $this->ResponseTasksErrors('An error occurred while updating the task', 500);
}
    $task = Task::where('id', $validatedData['id'])->first();
    if(!$task){
        return $this->ResponseTasksErrors('Task not found',404);
    }
    $task->update(['status' => $validatedData['status']]);

    return $this->ResponseTasks($task, 'Status changed successfully', 200);

}

public function delete(Request $request)
{
        $task_id = $request->id;

        $task = Task::find($task_id);

        if (!$task) {
            return $this->ResponseTasksErrors('Task not found', 404);
        }

    Comment::where('task_id',$task_id)->delete();
        $task->delete();

        $tasks = Task::all();

        return $this->ResponseTasks($tasks, 'Task deleted successfully', 200);
    }





   public function showStatus(Request $request)
    {
            $stat = $request->input('status');

            $statusMap = [
                'COMPLETED' => 1,
                'IN_PROGRESS' => 2,
                'PENDING' => 3
            ];

            if (array_key_exists($stat, $statusMap)) {
                $taskStat = Task::where('status', $statusMap[$stat])->get();
                if ($taskStat->count() > 0)
                {
                    return $this->ResponseTasks($taskStat,'Tasks with status '.$stat.' retrieved successfully',200);
                }
                return $this->ResponseTasksErrors('No tasks found for the selected status',404);
            }
            else
             {
                return $this->ResponseTasksErrors('Status '.$stat.' not found',404);
            }
        }

}




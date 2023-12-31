<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\GeneralTrait;
use App\Models\Comment;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use GeneralTrait;
public function getAllSorted(Request $request){
    $sortBy=$request->input('sort_by','date');
        $comments=Comment::query();
        switch ($sortBy) {
            case 'date':
                $comments->orderBy('addition_date');
                break;
            case 'comment':
                $comments->orderBy('content')->orderBy('addition_date');
                break;
            default:
                return $this->ResponseTasksErrors('Invalid sorting parameter', 400);
                break;
        }
        $sortedComments=$comments->get();
        return $this->ResponseTasks($sortedComments,'All comments by '.$sortBy.':',200);
    }

    public function getOne(Request $request,Comment $comment)
    {

        $one=Comment::where('id',$request->id)->get();
        if($one->isEmpty()){
            return $this->ResponseTasksErrors('Comment not found',404);
        }
        return $this->ResponseTasks($one,'Task retrieved successfully',200);
    }

    public function getCommOfTask(Request $request,Comment $comment){
        $task_id=$request->input('task_id');
        $commTask=Comment::where('task_id',$task_id)->get();
        if($commTask->isEmpty()){
            return $this->ResponseTasksErrors('Task not found',404);
        }
        return $this->ResponseTasks($commTask,'Comments related to task #'.$task_id,200);
    }


    public function newComment(Request $request)
    {
        try{
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'content' => 'required|string',
        ]);

    $newComment = Comment::create($validatedData);

    return $this->ResponseTasks($newComment,'Comment created successfully',201);
    }
    catch(ValidationException $e){
        return $this->ResponseTasksErrors('Please ensure the accuracy of the provided information and fill in the required fields',400);
    }
    catch(Exception $e){
        return $this->ResponseTasksErrors('An error occurred while creating the comment',500);
    }
    }

    public function updateComment(Request $request, Comment $comment)
    {
        try {

            $comm_id=$request->input('id');
            $comment = Comment::findOrFail($comm_id);

            if ($request->filled('content')) {
                $comment->update(['content' => $request->input('content')]);
            }

            return $this->ResponseTasks($comment, 'Comment updated successfully', 200);
        }
        catch (ModelNotFoundException $e)
        {
            return $this->ResponseTasksErrors('Comment not found', 404);
        }
    }


    public function getUserComments(Request $request)
{
    try {
        $user_id = $request->input('user_id');

        $userComments = Comment::where('user_id', $user_id)->get();

        if($userComments->isEmpty()) {
            return $this->ResponseTasksErrors('No comments found for this user', 404);
        }

        return $this->ResponseTasks($userComments, 'Comments for user #' . $user_id, 200);
    } catch (Exception $e) {
        return $this->ResponseTasksErrors('An error occurred while processing the request', 500);
    }
}


    public function deletComm(Request $request)
    {
        $comm_id=$request->input('id');
        $commDel=Comment::find($comm_id);
        if(!$commDel){
            return $this->ResponseTasksErrors('Comment not found',404);
        }
        $commDel->delete();
        $comments=Comment::all();
        return $this->ResponseTasks($comments,'Comment deleted successfully',200);
    }


}

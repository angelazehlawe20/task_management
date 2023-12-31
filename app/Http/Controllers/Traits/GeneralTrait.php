<?php

namespace App\Http\Controllers\Traits;

trait GeneralTrait
{
    public function ResponseTasks($data=null,$message=null,$status=null){
        $array=[
            'data'=>$data,
            'message'=>$message,
            'status'=> $status
        ];
        return response($array);
    }

    public function ResponseTasksErrors($message=null,$status=null){
        $error=[
            'error'=>$message,
            'status'=>$status
        ];
        return response($error);
    }
}




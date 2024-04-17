<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'file','file_type','title','description'
    ];

    protected $appends = ['post_data','likes','like_flag','total_comments','date','time','time_difference'];


    public function getPostDataAttribute(){
        return url('public/'.$this->file);
    }

    public function getLikesAttribute(){
        return Like::where('post_id',$this->id)->count();
    }
    public function getLikeFlagAttribute(){
        $like =  Like::where('post_id',$this->id)->where('user_id',Auth::user()->id)->first();
        if($like){
            return true;
        }else{
            return false;
        }
    }

    public function getTotalCommentsAttribute(){
        return Comment::where('post_id',$this->id)->count();
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id','id');
    }

    

}

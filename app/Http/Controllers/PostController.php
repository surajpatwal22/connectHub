<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function get_posts(){
        $posts = Post::with('comments')->with('comments.user')->orderBy('created_at','desc')->get();
        return response()->json([
            'posts' => $posts,
            'status' =>200,
            'success' =>true
        ],200);
    }

    public function get_notifications(){
        $notifications = Notification::with('notification_user')->where('user_id',0)->orWhere('user_id',Auth::user()->id)->orderBy('created_at','desc')->get();
        $notification_users = NotificationUser::where('user_id',Auth::user()->id)->delete();
        return response()->json([
            'notifications' => $notifications,
            'status' =>200,
            'success' =>true
        ],200);
    }

    public function like(Request $request){
        $validator = Validator::make($request->all(),[
            'post_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
            $like = Like::where('post_id',$request->post_id)->where('user_id',Auth::user()->id)->first();
            if($like){
                $like->delete();
                return response()->json([
                    'message' => 'disliked successfully',
                    'status' =>200,
                    'success' =>true
                ],200);
            }else{
                $like_create = Like::create([
                    'post_id' => $request->post_id,
                    'user_id' => Auth::user()->id,
                ]);
                if($like_create){
                    return response()->json([
                        'message' => 'liked successfully',
                        'status' =>200,
                        'success' =>true
                    ],200);
                }else{
                    return response()->json([
                        'message' => 'something went wrong',
                        'status' =>400,
                        'success' =>false
                    ],400);
                }
            }
        }
    }

    public function comment(Request $request){
        $validator = Validator::make($request->all(),[
            'post_id' => 'required',
            'comment' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
                $comment_create = Comment::create([
                    'post_id' => $request->post_id,
                    'user_id' => Auth::user()->id,
                    'comment' => $request->comment
                ]);
                if($comment_create){
                    return response()->json([
                        'message' => 'Comment created successfully',
                        'status' =>200,
                        'success' =>true
                    ],200);
                }else{
                    return response()->json([
                        'message' => 'something went wrong',
                        'status' =>400,
                        'success' =>false
                    ],400);
                }
            
        }
    }
    
    public function comment_delete(Request $request){
        $validator = Validator::make($request->all(),[
            'comment_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=> $validator->errors(),
                'status' => 400 ,
                'success' => false
            ]);
        }else{
                $Comment = Comment::find($request->comment_id);
                if($Comment){
                    $delete = $Comment->delete();
                    if($delete){
                        return response()->json([
                            'message' => 'Comment deleted successfully',
                            'status' =>200,
                            'success' =>true
                        ],200);
                    }else{
                        return response()->json([
                            'message' => 'something went wrong',
                            'status' =>400,
                            'success' =>false
                        ],400);
                    }
                }else{
                    return response()->json([
                        'message' => 'post not found',
                        'status' =>400,
                        'success' =>false
                    ],400);
                }
            
        }
    }
}

<?php
namespace App\Http\Controllers;

use App\Comment;
use App\Post;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class CommentController extends Controller {
    public function getCreateComment (Request $request, $post_id) {

        $newbody = str_replace("<p>", "", $request['commentbody']);
        $newbody2 = str_replace("</p>", "", $newbody);

        $comment = new Comment();
        $comment -> post_id = $post_id;
        $comment -> user_id = Auth::user()->id;
        $comment -> body = $newbody2;
        $comment -> first_name = auth() -> user() -> first_name;
        $comment -> last_name = auth() -> user() -> last_name;
        $comment->save();


        return redirect() -> route('post.getpost', ['post_id' => $post_id]) -> with(['message' => 'Comment successfully create!']);
    }
    public function getEditComment($comment_id) {
        $comment = Comment::where('id', $comment_id) -> first();
        return view('editcomment')->with('comment', $comment);

    }
    public function getUpdateComment(Request $request, $comment_id) {

        $this->validate($request, [
            'comment-body' => 'required'
        ]);

        $newbody = str_replace("<p>", "", $request->get('comment-body'));
        $newbody2 = str_replace("</p>", "", $newbody);

        // Create Post
        $comment = Comment::where('id', $comment_id) -> first();
        $comment->body = $newbody2;

        $comment->save();

        return redirect() -> route('post.getpost', ['post_id' => $comment -> post_id]) -> with(['message' => 'Comment successfully update!']);
    }

    public function getDeleteComment($id) {
        $comment = Comment::where('id', $id) -> first();
        $comment -> delete();

        return back() -> with(['message' => 'Successfully deleted!']);
    }


}
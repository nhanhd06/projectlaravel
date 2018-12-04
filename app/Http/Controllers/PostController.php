<?php

namespace App\Http\Controllers;


use App\Post;
use App\User;
use App\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;



class PostController extends Controller {

    public function getDashboard() {
        $posts = Post::orderBy('created_at', 'desc') -> paginate(5);

        return view('dashboard', ['posts' => $posts]);
    }

    public function getDashboard2() {

        $user_id = auth() -> user() -> id;
        $user = User::find($user_id);
        return view('dashboard2') -> with('posts', $user->posts);
    }

    public function postCreatePost(Request $request) {

        $this -> validate($request, [
            'body' => 'required|max:1000' ,
            'title' => 'required|max: 100',
            'cover_image' => 'image|nullable|max:1999'
        ]);

        if ($request -> hasFile('cover_image')) {
            $filenameWithExt = $request -> file('cover_image')-> getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            $path = $request -> file('cover_image') -> storeAs('public/cover_images',$fileNameToStore);
        }
        else {
            $fileNameToStore = 'noimage.jpg';
        }

        $newbody = str_replace("<p>", "", $request['body']);
        $newbody2 = str_replace("</p>", "", $newbody);

        $post = new Post();
        $post -> title = $request['title'];
        $post -> body = $newbody2;
        $post -> cover_image = $fileNameToStore;



        $message = 'There was an error';

        if ($request -> user() -> posts() -> save($post)) {
            $message = 'Post successfully create!';
        }

        return redirect() -> route('dashboard') -> with(['message' => $message]);

    }


    public function getPost($post_id) {
        $post = Post::find($post_id);
        $comments = DB::table('comments')->where('post_id', $post_id)->get();
        return view('show')->with('post', $post)
                                 ->with('comments', $comments);

    }

    public function getDeletePost($post_id) {
        $post = Post::where('id', $post_id) -> first();
        if (Auth::user() != $post -> user) {
            return redirect() -> back();
        }
        $post -> delete();
        if ($post->cover_image != 'noimage.jpg') {

        }
        return redirect() -> route('dashboard') -> with(['message' => 'Successfully deleted!']);
    }

    public function postEditPost($post_id) {
        $post = Post::find($post_id);
        return view('edit')->with('post', $post);

    }

    public function getAbout() {
        return view('aboutus');
    }

    public function getCreatedForm() {
        return view('createpost');
    }

    public function getUpdatePost(Request $request, $post_id) {

        $this->validate($request, [
            'title' => 'required',
            'body' => 'required'
        ]);

        if ($request -> hasFile('cover_image')) {
            $filenameWithExt = $request -> file('cover_image')-> getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            $path = $request -> file('cover_image') -> storeAs('public/cover_images',$fileNameToStore);
        }


        $newbody = str_replace("<p>", "", $request->get('body'));
        $newbody2 = str_replace("</p>", "", $newbody);

        // Create Post
        $post = Post::find($post_id);
        $post->title = $request->get('title');
        $post->body = $newbody2;

        if ($request -> hasFile('cover_image')) {
            $post->cover_image = $fileNameToStore;
            Storage::delete('public/cover_images/'.$post -> cover_image);
        }

        $post->save();

        return redirect()->route('dashboard')->with(['message' => 'Successfully updated!']);
    }


}
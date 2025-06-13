<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Storage;

class PostController extends Controller
{
    function loadMorePost(Request $request)
    {
        return response(Post::orderBy('created_at', 'desc')->get(), 200);
    }

    function countPost()
    {
        return response(['data' => Post::count()], 200);
    }

    function getPosts(Request $request)
    {
        $query = $request->query('query');
        $data = Post::all();
        if (!is_null($query)) {
            $post = $data->where('title', 'like', '%' . $query . '%');
            return response($post, 200);
        }
        return response(['data' => $data->paginate(5)], 200);
    }

    function store(Request $request)
    {
        $fields = $request->all();

        $errors = Validator::make($fields, [
            'title' => 'required',
            'post_content' => 'required'
        ]);

        if ($errors->fails()) {
            return response($errors->errors()->all(), 422);
        }

        $post = Post::create([
            'title' => $fields['title'],
            'post_content' => $fields['post_content'],
            'slug' => Str::slug($fields['title'])
        ]);

        return response(['date' => $post, 'message' => 'post created !'], 201);
    }

    function generateSlug($title)
    {
        $randomNumber = Str::random(6) . time();
        $slug = Str::slug($title) . '-' . $randomNumber;
        return $slug;
    }

    function update(Request $request, $id)
    {
        Post::where('id', $id)->update([
            'title' => $request->title,
            'post_content' => $request->post_content,
            'slug' => Str::slug($request->title)
        ]);

        return response(['message' => 'post updated !'], 200);
    }

    function destroy(Request $request, $id)
    {
        $post = Post::find($id);
        if ($post->image != null) {
            $imagePath = public_path('image' . $post->image);
            unlink($imagePath);
        }
        $post->delete();

        return response(['message' => 'post deleted !'], 200);
    }

    function getPostBySlug($slug)
    {
        $posts = Post::where('slug', $slug)->get();
        if (count($posts)) {
            return response($posts, 200);
        } else {
            return response($posts, 200);
        }
    }

    function addImage(Request $request)
    {
        $fields = $request->all();
        $post = Post::find($request->postId);

        $errors = Validator::make($fields, [
            'postId' => 'required',
            'image' => 'required|image'
        ]);

        if ($errors->fails()) {
            return response($errors->errors()->all(), 422);
        }

        if ($post->image != null) {
            // $fileName = basename($post->image);
            $filePath = public_path("image" . '/' . basename($post->image));
            unlink($filePath);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $path = public_path('image');
            $imageName = time() . '.' . $extension;

            $url = url('/image/' . $imageName);

            if (!File::exists($path)) {
                File::makeDirectory($path);
            }
            $image->move($path, $imageName);

            Post::where('id', $request->postId)
                ->update([
                    'image' => $url
                ]);
            return response(['message' => 'post image uploaded !'], 200);
        }
    }
}

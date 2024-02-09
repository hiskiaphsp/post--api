<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request)
    {
        $this->validatePostRequest($request);

        $imagePath = $request->file('image')->storeAs('public/posts', $request->file('image')->hashName());

        $post = Post::create([
            'image'   => $imagePath,
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        return new PostResource(true, 'Data Post Successfully Created!', $post);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return new PostResource(true, 'Detail Data Post!', $post);
    }

    public function update(Request $request, $id)
    {
        $this->validatePostRequest($request);

        $post = Post::findOrFail($id);

        if ($request->hasFile('image')) {
            $this->updatePostWithImage($post, $request->file('image'));
        } else {
            $this->updatePostWithoutImage($post, $request->only(['title', 'content']));
        }

        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    private function validatePostRequest(Request $request)
    {
        $request->validate([
            'title'   => 'required',
            'content' => 'required',
            'image'   => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }

    private function updatePostWithImage($post, $image)
    {
        $this->deleteOldImage($post);

        $imagePath = $image->storeAs('public/post/', $image->hashName());

        $post->update([
            'image'   => $imagePath,
            'title'   => $request->title,
            'content' => $request->content,
        ]);
    }

    private function updatePostWithoutImage($post, $data)
    {
        $post->update($data);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        $this->deletePostImage($post);
        $post->delete();

        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }

    private function deletePostImage($post)
    {
        if ($post->image) {
            Storage::delete('public/posts/' . basename($post->image));
        }
    }
}

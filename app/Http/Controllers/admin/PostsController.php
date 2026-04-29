<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostsController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index()
    {
        $posts = News::paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $validated['author_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('posts', $imageName, 'public');
            $validated['image'] = 'posts/' . $imageName;
        }

        News::create($validated);

        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được tạo thành công');
    }

    /**
     * Display the specified post.
     */
    public function show($post)
    {
        $post = News::findOrFail($post);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit($post)
    {
        $post = News::findOrFail($post);
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, $post)
    {
        $post = News::findOrFail($post);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'contentt' => 'required|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $content = $validated['contentt'];
        $validated['slug'] = Str::slug($validated['title']);
        $validated['content'] = $content;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('posts', $imageName, 'public');
            $validated['image'] = 'posts/' . $imageName;
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được cập nhật thành công');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy($post)
    {
        $post = News::findOrFail($post);
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được xóa thành công');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Criteria\PostCriteria;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index(Request $request)
    {
        $this->postRepository->pushCriteria(new PostCriteria($request));

        $data = $this->postRepository->findWhere([]);

        return view('home.index')->with('data', $data);
    }

    public function show($id)
    {
        $post = $this->postRepository->findWhereFirst(['id' => $id, 'status' => Post::STATUS_ACTIVE]);

        return view('posts.detail')->with('post', $post);
    }
}

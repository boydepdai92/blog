<?php

namespace App\Http\Controllers\Console;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Repositories\Criteria\PostCriteria;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\PostIndexRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostController extends Controller
{
    protected $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index(PostIndexRequest $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            $request->merge(['created_by' => Auth::id()]);
        }

        $this->postRepository->pushCriteria(new PostCriteria($request));

        $data = $this->postRepository->findWhere([]);

        return view('posts.index')->with('data', $data);
    }

    public function show(int $id)
    {
        $post = $this->postRepository->findWhereFirst(['id' => $id]);

        if (empty($post)) {
            return redirect()->back()->withErrors('Not found post for edit');
        }

        return view('posts.detail')->with('post', $post);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(StorePostRequest $request)
    {
        $param = $request->only(['title', 'content', 'publish_date']);
        $param['created_by'] = Auth::id();
        if ($request->has('publish_date')) {
            $param['publish_date'] = Carbon::createFromTimestamp($request->input('publish_date'));
        }

        $post = $this->postRepository->create($param);

        if ($post) {
            return redirect('console/posts')->withSuccess('Create post success');
        }

        return redirect()->back()->withErrors('Create post error, please try again');
    }

    public function edit(int $id)
    {
        $post = $this->postRepository->findWhereFirst(['id' => $id]);

        if (empty($post)) {
            return redirect()->back()->withErrors('Not found post for edit');
        }

        return view('posts.update')->with('post', $post);
    }

    public function update(int $id, UpdatePostRequest $request)
    {
        $param = $request->only(['title', 'content', 'publish_date']);
        if ($request->has('publish_date')) {
            $param['publish_date'] = Carbon::createFromTimestamp($request->input('publish_date'));
        }

        $post = $this->postRepository->findWhereFirst(['id' => $id, 'status' => Post::STATUS_ACTIVE]);

        if (empty($post)) {
            return redirect()->back()->withErrors('Not found post for update');
        }

        $resultUpdate = $this->postRepository->update($param, $id);

        if ($resultUpdate) {
            return redirect('console/posts')->withSuccess('Update post success');
        }

        return redirect()->back()->withErrors('Update post error, please try again');
    }

    public function delete(int $id)
    {
        $post = $this->postRepository->findWhereFirst(['id' => $id, 'status' => Post::STATUS_INACTIVE]);

        if (empty($post)) {
            return redirect()->back()->withErrors('Not found post for delete');
        }

        $resultDelete = $this->postRepository->delete($id);

        if ($resultDelete) {
            return redirect('console/posts')->withSuccess('Delete post success');
        }

        return redirect()->back()->withErrors('Delete post error, please try again');
    }

    public function publish(int $id)
    {
        $post = $this->postRepository->findWhereFirst(['id' => $id, 'status' => Post::STATUS_INACTIVE]);

        if (empty($post)) {
            return redirect()->back()->withErrors('Not found post for publish');
        }

        $resultPublish = $this->postRepository->update(['status' => Post::STATUS_ACTIVE], $post->id);

        if ($resultPublish) {
            return redirect('console/posts')->withSuccess('Publish post success');
        }

        return redirect()->back()->withErrors('Publish post error, please try again');
    }

    public function unPublish(int $id)
    {
        $post = $this->postRepository->findWhereFirst(['id' => $id, 'status' => Post::STATUS_ACTIVE]);

        if (empty($post)) {
            return redirect()->back()->withErrors('Not found post for unPublish');
        }

        $resultUnPublish = $this->postRepository->update(['status' => Post::STATUS_INACTIVE], $post->id);

        if ($resultUnPublish) {
            return redirect('console/posts')->withSuccess('UnPublish post success');
        }

        return redirect()->back()->withErrors('UnPublish post error, please try again');
    }
}

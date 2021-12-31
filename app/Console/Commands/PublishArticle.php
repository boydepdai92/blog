<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Repositories\Contracts\PostRepositoryInterface;

class PublishArticle extends Command
{
    protected $signature = 'article:publish';

    protected $description = 'Auto publish article';

    public function handle()
    {
        $this->info('Start auto publish article');

        try {
            /** @var PostRepositoryInterface $postRepository */
            $postRepository = app(PostRepositoryInterface::class);

            $data = $postRepository->findWhere([
                'status' => Post::STATUS_INACTIVE,
                ['publish_date', '>', Carbon::yesterday()->startOfDay()],
                ['publish_date', '<', Carbon::now()]
            ]);

            if (!empty($data)) {
                /** @var Post $value */
                foreach ($data as $value) {
                    $value->status = Post::STATUS_ACTIVE;
                    $value->publish_date = Carbon::now();
                    $value->save();
                }
            }
        } catch (Exception $exception) {
            Log::error($exception);
        }
    }
}

<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class EpisodeWorkflowController extends BaseApiController
{
    public function generateStoryboard(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock storyboard generation queued']);
    }

    public function storyboards(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'items' => []]);
    }

    public function extractProps(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock props extracted']);
    }

    public function extractCharacters(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock characters extracted']);
    }

    public function finalize(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'status' => 'finalized']);
    }

    public function download(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'url' => '/static/mock/episode-' . $episodeId . '.mp4']);
    }
}

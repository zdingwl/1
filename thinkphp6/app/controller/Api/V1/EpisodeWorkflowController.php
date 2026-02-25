<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\model\Character;
use app\model\Episode;
use app\model\Prop;
use app\model\Scene;
use app\model\Storyboard;
use app\service\SchemaService;
use app\service\TaskService;

class EpisodeWorkflowController extends BaseApiController
{
    public function generateStoryboard(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        $episode = Episode::find($episodeId);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        $scenes = Scene::where('episode_id', $episodeId)->order('sort_order', 'asc')->select();
        $created = [];
        foreach ($scenes as $scene) {
            $exists = Storyboard::where('episode_id', $episodeId)->where('scene_id', (int)$scene['id'])->find();
            if ($exists) {
                continue;
            }

            $created[] = Storyboard::create([
                'episode_id' => $episodeId,
                'scene_id' => (int)$scene['id'],
                'shot_name' => 'Shot for scene #' . $scene['id'],
                'description' => trim((string)$scene['prompt']) !== '' ? (string)$scene['prompt'] : 'auto generated storyboard',
                'duration_seconds' => 3,
                'frame_type' => 'keyframe',
                'created_at' => $this->now(),
                'updated_at' => $this->now(),
            ])->toArray();
        }

        $task = (new TaskService())->create('storyboard.generate', ['episode_id' => $episodeId, 'count' => count($created)], 'completed');

        return $this->success([
            'episode_id' => $episodeId,
            'count' => count($created),
            'items' => $created,
            'task' => $task->toArray(),
        ]);
    }

    public function storyboards(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $items = Storyboard::where('episode_id', $episodeId)->order('id', 'asc')->select()->toArray();
        return $this->success(['episode_id' => $episodeId, 'items' => $items]);
    }

    public function extractProps(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        $episode = Episode::find($episodeId);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        $dramaId = (int)$episode['drama_id'];
        $existing = Prop::where('drama_id', $dramaId)->count();
        if ($existing === 0) {
            $now = $this->now();
            Prop::create([
                'drama_id' => $dramaId,
                'name' => '默认道具',
                'description' => '从分集中抽取（mock）',
                'image_url' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $task = (new TaskService())->create('props.extract', ['episode_id' => $episodeId], 'completed');
        return $this->success(['episode_id' => $episodeId, 'task' => $task->toArray(), 'message' => 'props extracted']);
    }

    public function extractCharacters(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        $episode = Episode::find($episodeId);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        $dramaId = (int)$episode['drama_id'];
        $existing = Character::where('drama_id', $dramaId)->count();
        if ($existing === 0) {
            $now = $this->now();
            Character::create([
                'drama_id' => $dramaId,
                'name' => '默认角色',
                'profile' => '从分集中抽取（mock）',
                'image_url' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $task = (new TaskService())->create('characters.extract', ['episode_id' => $episodeId], 'completed');
        return $this->success(['episode_id' => $episodeId, 'task' => $task->toArray(), 'message' => 'characters extracted']);
    }

    public function finalize(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        $episode = Episode::find($episodeId);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        $episode->save(['status' => 'finalized', 'updated_at' => $this->now()]);
        $task = (new TaskService())->create('episode.finalize', ['episode_id' => $episodeId], 'completed');

        return $this->success(['episode' => $episode->refresh()->toArray(), 'task' => $task->toArray(), 'status' => 'finalized']);
    }

    public function download(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        $episode = Episode::find($episodeId);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        return $this->success([
            'episode_id' => $episodeId,
            'url' => '/static/mock/episode-' . $episodeId . '.mp4',
            'status' => (string)$episode['status'],
        ]);
    }
}

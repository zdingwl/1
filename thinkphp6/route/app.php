<?php

declare(strict_types=1);

use think\facade\Route;

Route::get('health', 'Api.V1.HealthController/index');

Route::group('api/v1', function () {
    Route::get('health', 'Api.V1.HealthController/index');

    // dramas
    Route::get('dramas', 'Api.V1.DramaController/index');
    Route::post('dramas', 'Api.V1.DramaController/create');
    Route::get('dramas/stats', 'Api.V1.DramaController/stats');
    Route::get('dramas/:id', 'Api.V1.DramaController/read');
    Route::put('dramas/:id', 'Api.V1.DramaController/update');
    Route::delete('dramas/:id', 'Api.V1.DramaController/delete');

    // episodes by drama
    Route::get('dramas/:dramaId/episodes', 'Api.V1.EpisodeController/index');
    Route::post('dramas/:dramaId/episodes', 'Api.V1.EpisodeController/create');
    Route::put('episodes/:id', 'Api.V1.EpisodeController/update');
    Route::delete('episodes/:id', 'Api.V1.EpisodeController/delete');

    // scenes by episode
    Route::get('episodes/:episodeId/scenes', 'Api.V1.SceneController/index');
    Route::post('episodes/:episodeId/scenes', 'Api.V1.SceneController/create');
    Route::put('scenes/:id', 'Api.V1.SceneController/update');
    Route::delete('scenes/:id', 'Api.V1.SceneController/delete');

    // storyboards by episode
    Route::get('episodes/:episodeId/storyboards', 'Api.V1.StoryboardController/index');
    Route::post('episodes/:episodeId/storyboards', 'Api.V1.StoryboardController/create');
    Route::put('storyboards/:id', 'Api.V1.StoryboardController/update');
    Route::delete('storyboards/:id', 'Api.V1.StoryboardController/delete');

    // ai-configs
    Route::get('ai-configs', 'Api.V1.AIConfigController/index');
    Route::post('ai-configs', 'Api.V1.AIConfigController/create');
    Route::post('ai-configs/test', 'Api.V1.AIConfigController/testConnection');
    Route::get('ai-configs/:id', 'Api.V1.AIConfigController/read');
    Route::put('ai-configs/:id', 'Api.V1.AIConfigController/update');
    Route::delete('ai-configs/:id', 'Api.V1.AIConfigController/delete');

    // tasks
    Route::get('tasks', 'Api.V1.TaskController/index');
    Route::post('tasks', 'Api.V1.TaskController/create');
    Route::get('tasks/:taskId', 'Api.V1.TaskController/read');
    Route::put('tasks/:taskId', 'Api.V1.TaskController/update');

    // 未迁移模块统一返回 501，明确迁移状态
    Route::any(':module/:path?', 'Api.V1.NotImplementedController/handle')
        ->pattern([
            'module' => '[a-z\-]+',
            'path' => '.*',
        ]);
});

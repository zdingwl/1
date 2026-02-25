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
    Route::put('dramas/:id/outline', 'Api.V1.DramaController/saveOutline');
    Route::get('dramas/:id/characters', 'Api.V1.DramaController/getCharacters');
    Route::put('dramas/:id/characters', 'Api.V1.DramaController/saveCharacters');
    Route::put('dramas/:id/episodes', 'Api.V1.DramaController/saveEpisodes');
    Route::put('dramas/:id/progress', 'Api.V1.DramaController/saveProgress');
    Route::get('dramas/:id/props', 'Api.V1.PropController/listByDrama');

    // generation
    Route::post('generation/characters', 'Api.V1.GenerationController/characters');

    // character-library
    Route::get('character-library', 'Api.V1.CharacterLibraryController/index');
    Route::post('character-library', 'Api.V1.CharacterLibraryController/create');
    Route::get('character-library/:id', 'Api.V1.CharacterLibraryController/read');
    Route::delete('character-library/:id', 'Api.V1.CharacterLibraryController/delete');

    // characters
    Route::put('characters/:id', 'Api.V1.CharacterController/update');
    Route::delete('characters/:id', 'Api.V1.CharacterController/delete');
    Route::post('characters/batch-generate-images', 'Api.V1.CharacterController/batchGenerateImages');
    Route::post('characters/:id/generate-image', 'Api.V1.CharacterController/generateImage');
    Route::post('characters/:id/upload-image', 'Api.V1.CharacterController/uploadImage');
    Route::put('characters/:id/image', 'Api.V1.CharacterController/uploadImage');
    Route::put('characters/:id/image-from-library', 'Api.V1.CharacterController/applyLibrary');
    Route::post('characters/:id/add-to-library', 'Api.V1.CharacterController/addToLibrary');

    // props
    Route::post('props', 'Api.V1.PropController/create');
    Route::put('props/:id', 'Api.V1.PropController/update');
    Route::delete('props/:id', 'Api.V1.PropController/delete');
    Route::post('props/:id/generate', 'Api.V1.PropController/generate');

    // upload
    Route::post('upload/image', 'Api.V1.UploadController/image');

    // episodes workflow
    Route::post('episodes/:episodeId/storyboards', 'Api.V1.EpisodeWorkflowController/generateStoryboard');
    Route::post('episodes/:episodeId/props/extract', 'Api.V1.EpisodeWorkflowController/extractProps');
    Route::post('episodes/:episodeId/characters/extract', 'Api.V1.EpisodeWorkflowController/extractCharacters');
    Route::get('episodes/:episodeId/storyboards', 'Api.V1.EpisodeWorkflowController/storyboards');
    Route::post('episodes/:episodeId/finalize', 'Api.V1.EpisodeWorkflowController/finalize');
    Route::get('episodes/:episodeId/download', 'Api.V1.EpisodeWorkflowController/download');

    // episode CRUD by drama
    Route::get('dramas/:dramaId/episodes', 'Api.V1.EpisodeController/index');
    Route::post('dramas/:dramaId/episodes', 'Api.V1.EpisodeController/create');
    Route::put('episodes/:id', 'Api.V1.EpisodeController/update');
    Route::delete('episodes/:id', 'Api.V1.EpisodeController/delete');

    // scenes
    Route::put('scenes/:id', 'Api.V1.SceneController/update');
    Route::put('scenes/:id/prompt', 'Api.V1.SceneController/updatePrompt');
    Route::delete('scenes/:id', 'Api.V1.SceneController/delete');
    Route::post('scenes/generate-image', 'Api.V1.SceneController/generateImage');
    Route::post('scenes', 'Api.V1.SceneController/createStandalone');
    Route::get('episodes/:episodeId/scenes', 'Api.V1.SceneController/index');
    Route::post('episodes/:episodeId/scenes', 'Api.V1.SceneController/create');

    // images
    Route::get('images', 'Api.V1.ImageController/index');
    Route::post('images', 'Api.V1.ImageController/create');
    Route::get('images/:id', 'Api.V1.ImageController/read');
    Route::delete('images/:id', 'Api.V1.ImageController/delete');
    Route::post('images/scene/:sceneId', 'Api.V1.ImageController/generateByScene');
    Route::post('images/upload', 'Api.V1.ImageController/upload');
    Route::get('images/episode/:episodeId/backgrounds', 'Api.V1.ImageController/backgrounds');
    Route::post('images/episode/:episodeId/backgrounds/extract', 'Api.V1.ImageController/extractBackgrounds');
    Route::post('images/episode/:episodeId/batch', 'Api.V1.ImageController/batch');

    // videos
    Route::get('videos', 'Api.V1.VideoController/index');
    Route::post('videos', 'Api.V1.VideoController/create');
    Route::get('videos/:id', 'Api.V1.VideoController/read');
    Route::delete('videos/:id', 'Api.V1.VideoController/delete');
    Route::post('videos/image/:imageGenId', 'Api.V1.VideoController/fromImage');
    Route::post('videos/episode/:episodeId/batch', 'Api.V1.VideoController/batch');

    // video merges
    Route::get('video-merges', 'Api.V1.VideoMergeController/index');
    Route::post('video-merges', 'Api.V1.VideoMergeController/create');
    Route::get('video-merges/:mergeId', 'Api.V1.VideoMergeController/read');
    Route::delete('video-merges/:mergeId', 'Api.V1.VideoMergeController/delete');

    // assets
    Route::get('assets', 'Api.V1.AssetController/index');
    Route::post('assets', 'Api.V1.AssetController/create');
    Route::get('assets/:id', 'Api.V1.AssetController/read');
    Route::put('assets/:id', 'Api.V1.AssetController/update');
    Route::delete('assets/:id', 'Api.V1.AssetController/delete');
    Route::post('assets/import/image/:imageGenId', 'Api.V1.AssetController/importFromImage');
    Route::post('assets/import/video/:videoGenId', 'Api.V1.AssetController/importFromVideo');

    // storyboards
    Route::get('storyboards/episode/:episodeId/generate', 'Api.V1.EpisodeWorkflowController/generateStoryboard');
    Route::post('storyboards', 'Api.V1.StoryboardController/createStandalone');
    Route::put('storyboards/:id', 'Api.V1.StoryboardController/update');
    Route::delete('storyboards/:id', 'Api.V1.StoryboardController/delete');
    Route::post('storyboards/:id/props', 'Api.V1.PropController/associate');
    Route::post('storyboards/:id/frame-prompt', 'Api.V1.StoryboardController/framePrompt');
    Route::get('storyboards/:id/frame-prompts', 'Api.V1.StoryboardController/framePrompts');
    Route::get('episodes/:episodeId/storyboards-v2', 'Api.V1.StoryboardController/index');
    Route::post('episodes/:episodeId/storyboards-v2', 'Api.V1.StoryboardController/create');

    // audio
    Route::post('audio/extract', 'Api.V1.AudioController/extract');
    Route::post('audio/extract/batch', 'Api.V1.AudioController/batchExtract');

    // settings
    Route::get('settings/language', 'Api.V1.SettingsController/language');
    Route::put('settings/language', 'Api.V1.SettingsController/updateLanguage');

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

    // fallback
    Route::any(':module/:path?', 'Api.V1.NotImplementedController/handle')
        ->pattern([
            'module' => '[a-z\-]+',
            'path' => '.*',
        ]);
});

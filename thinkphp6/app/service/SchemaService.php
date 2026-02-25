<?php

declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class SchemaService
{
    private static bool $initialized = false;

    public static function ensureCoreTables(): void
    {
        if (self::$initialized) {
            return;
        }

        Db::execute('PRAGMA foreign_keys = ON');

        Db::execute('CREATE TABLE IF NOT EXISTS dramas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            genre VARCHAR(100) DEFAULT "",
            synopsis TEXT DEFAULT "",
            progress INTEGER DEFAULT 0,
            status VARCHAR(50) DEFAULT "draft",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS episodes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            drama_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            episode_no INTEGER DEFAULT 1,
            summary TEXT DEFAULT "",
            status VARCHAR(50) DEFAULT "draft",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE CASCADE
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS scenes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            episode_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            prompt TEXT DEFAULT "",
            image_url VARCHAR(500) DEFAULT "",
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS storyboards (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            episode_id INTEGER NOT NULL,
            scene_id INTEGER,
            shot_name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT "",
            duration_seconds INTEGER DEFAULT 3,
            frame_type VARCHAR(50) DEFAULT "keyframe",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
            FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE SET NULL
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS ai_configs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            provider VARCHAR(50) NOT NULL,
            model VARCHAR(100) DEFAULT "",
            endpoint VARCHAR(255) DEFAULT "",
            api_key_masked VARCHAR(255) DEFAULT "",
            is_enabled INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            task_key VARCHAR(100) NOT NULL UNIQUE,
            task_type VARCHAR(100) NOT NULL,
            status VARCHAR(50) DEFAULT "pending",
            progress INTEGER DEFAULT 0,
            payload TEXT DEFAULT "",
            result TEXT DEFAULT "",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS character_library (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT DEFAULT "",
            image_url VARCHAR(500) DEFAULT "",
            tags VARCHAR(255) DEFAULT "",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS characters (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            drama_id INTEGER,
            name VARCHAR(100) NOT NULL,
            profile TEXT DEFAULT "",
            image_url VARCHAR(500) DEFAULT "",
            library_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE SET NULL,
            FOREIGN KEY (library_id) REFERENCES character_library(id) ON DELETE SET NULL
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS props (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            drama_id INTEGER,
            name VARCHAR(100) NOT NULL,
            description TEXT DEFAULT "",
            image_url VARCHAR(500) DEFAULT "",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE SET NULL
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS image_generations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            scene_id INTEGER,
            prompt TEXT NOT NULL,
            image_url VARCHAR(500) DEFAULT "",
            status VARCHAR(50) DEFAULT "pending",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE SET NULL
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS video_generations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            image_gen_id INTEGER,
            prompt TEXT DEFAULT "",
            video_url VARCHAR(500) DEFAULT "",
            status VARCHAR(50) DEFAULT "pending",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (image_gen_id) REFERENCES image_generations(id) ON DELETE SET NULL
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS assets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            source VARCHAR(50) DEFAULT "manual",
            url VARCHAR(500) DEFAULT "",
            meta TEXT DEFAULT "",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        Db::execute('CREATE TABLE IF NOT EXISTS app_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value VARCHAR(255) DEFAULT "",
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        Db::execute('CREATE INDEX IF NOT EXISTS idx_episodes_drama_id ON episodes(drama_id)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_scenes_episode_id ON scenes(episode_id)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_storyboards_episode_id ON storyboards(episode_id)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_tasks_task_key ON tasks(task_key)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_characters_drama_id ON characters(drama_id)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_props_drama_id ON props(drama_id)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_image_generations_scene_id ON image_generations(scene_id)');
        Db::execute('CREATE INDEX IF NOT EXISTS idx_video_generations_image_gen_id ON video_generations(image_gen_id)');

        self::$initialized = true;
    }
}

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

        Db::execute(
            'CREATE TABLE IF NOT EXISTS dramas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                genre VARCHAR(100) DEFAULT "",
                synopsis TEXT DEFAULT "",
                progress INTEGER DEFAULT 0,
                status VARCHAR(50) DEFAULT "draft",
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        Db::execute(
            'CREATE TABLE IF NOT EXISTS ai_configs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                provider VARCHAR(50) NOT NULL,
                model VARCHAR(100) DEFAULT "",
                endpoint VARCHAR(255) DEFAULT "",
                is_enabled INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        self::$initialized = true;
    }
}

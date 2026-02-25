# ThinkPHP 6.0 重写版本（后端）

这是把当前 Go(Gin) 后端迁移到 ThinkPHP 6 的第二版实现（在骨架基础上补充了可运行模块）。

## 当前已迁移模块

- ✅ 健康检查：`GET /health`、`GET /api/v1/health`
- ✅ 剧本管理（dramas）：
  - `GET /api/v1/dramas`
  - `POST /api/v1/dramas`
  - `GET /api/v1/dramas/stats`
  - `GET /api/v1/dramas/{id}`
  - `PUT /api/v1/dramas/{id}`
  - `DELETE /api/v1/dramas/{id}`
- ✅ AI 配置（ai-configs）：
  - `GET /api/v1/ai-configs`
  - `POST /api/v1/ai-configs`
  - `POST /api/v1/ai-configs/test`
  - `GET /api/v1/ai-configs/{id}`
  - `PUT /api/v1/ai-configs/{id}`
  - `DELETE /api/v1/ai-configs/{id}`
- ✅ 任务查询（tasks）：
  - `GET /api/v1/tasks`
  - `GET /api/v1/tasks/{task_id}`

## 暂未迁移模块

其余 API 路径会命中统一降级路由，返回 `501` 和模块信息（用于提示迁移进度，而不是静默成功）。

## 数据层说明

- 使用 ThinkPHP 模型 + SQLite。
- 首次请求时会自动创建核心表：
  - `dramas`
  - `ai_configs`
- 数据库路径：`runtime/drama_generator.db`

## 目录结构

```text
thinkphp6/
├── app/
│   ├── common/BaseApiController.php
│   ├── controller/Api/V1/
│   │   ├── AIConfigController.php
│   │   ├── DramaController.php
│   │   ├── HealthController.php
│   │   ├── NotImplementedController.php
│   │   └── TaskController.php
│   ├── model/
│   │   ├── AIConfig.php
│   │   └── Drama.php
│   └── service/SchemaService.php
├── config/
├── public/index.php
├── route/app.php
└── composer.json
```

## 快速启动

```bash
cd thinkphp6
composer install
php think run
```

## 迁移下一步建议

1. 继续迁移 `images/videos/storyboards` 控制器与服务层。
2. 将目前控制器中的表结构初始化逻辑迁移到独立 migration。
3. 对齐 Go 版响应字段（如分页、错误码、任务状态枚举）并补齐集成测试。

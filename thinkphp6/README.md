# ThinkPHP 6.0 重写版本（可运行）

本目录是 Go(Gin) 后端的 ThinkPHP 6.0 重写版本，当前已经具备一套可运行的核心链路：

`Drama -> Episode -> Scene -> Storyboard`，以及 `AI 配置`、`任务状态`。

## 已实现接口

### 基础
- `GET /health`
- `GET /api/v1/health`

### Drama
- `GET /api/v1/dramas`
- `POST /api/v1/dramas`
- `GET /api/v1/dramas/stats`
- `GET /api/v1/dramas/{id}`
- `PUT /api/v1/dramas/{id}`
- `DELETE /api/v1/dramas/{id}`

### Episode
- `GET /api/v1/dramas/{dramaId}/episodes`
- `POST /api/v1/dramas/{dramaId}/episodes`
- `PUT /api/v1/episodes/{id}`
- `DELETE /api/v1/episodes/{id}`

### Scene
- `GET /api/v1/episodes/{episodeId}/scenes`
- `POST /api/v1/episodes/{episodeId}/scenes`
- `PUT /api/v1/scenes/{id}`
- `DELETE /api/v1/scenes/{id}`

### Storyboard
- `GET /api/v1/episodes/{episodeId}/storyboards`
- `POST /api/v1/episodes/{episodeId}/storyboards`
- `PUT /api/v1/storyboards/{id}`
- `DELETE /api/v1/storyboards/{id}`

### AI Config
- `GET /api/v1/ai-configs`
- `POST /api/v1/ai-configs`
- `POST /api/v1/ai-configs/test`（当前 mock）
- `GET /api/v1/ai-configs/{id}`
- `PUT /api/v1/ai-configs/{id}`
- `DELETE /api/v1/ai-configs/{id}`

### Task
- `GET /api/v1/tasks`
- `POST /api/v1/tasks`
- `GET /api/v1/tasks/{taskId}`
- `PUT /api/v1/tasks/{taskId}`

## 数据结构

`SchemaService` 会在首次请求时自动创建 SQLite 表：

- `dramas`
- `episodes`
- `scenes`
- `storyboards`
- `ai_configs`
- `tasks`

数据库文件：`runtime/drama_generator.db`

## 运行方式

```bash
cd thinkphp6
composer install
php -S 0.0.0.0:8000 -t public
```

然后访问：`http://localhost:8000/api/v1/health`

## 关于未迁移模块

未实现的模块会走兜底路由并返回 `501`，用于明确提示迁移进度，避免“假成功”的接口。

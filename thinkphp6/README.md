# ThinkPHP 6.0 重写版本（持续推进中）

当前已把 Go 版主要 API 分组逐步迁移到 ThinkPHP6 路由与控制器，项目可以运行，且大多数入口已可调用。

> 说明：部分 AI/媒体相关接口目前为 **mock 逻辑**（返回结构与状态流可用，具体第三方能力待继续接入）。

当前 images/videos/episode-workflow 已接入任务记录，调用会写入 `tasks` 表用于追踪；其中 images/videos 的关键写入路径已使用事务保证一致性。

## 当前覆盖模块

- `health`
- `dramas`（含 outline/characters/episodes/progress）
- `generation`
- `character-library`
- `characters`
- `props`
- `upload`
- `episodes`（workflow）
- `scenes`
- `images`
- `videos`
- `video-merges`
- `assets`
- `storyboards`
- `audio`
- `settings`
- `ai-configs`
- `tasks`

## 数据结构

`SchemaService` 首次请求自动建表（SQLite）：

- dramas / episodes / scenes / storyboards
- ai_configs / tasks
- character_library / characters / props
- image_generations / video_generations
- assets / app_settings

数据库文件：`runtime/drama_generator.db`

## 启动

```bash
cd thinkphp6
composer install
php -S 0.0.0.0:8000 -t public
```



## 代码说明（已注释）

本轮已对以下核心文件补充详细注释，便于后续维护与继续迁移：

- `app/common/BaseApiController.php`（统一响应规范）
- `app/common/RequestPayload.php`（请求参数归一化策略）
- `app/service/TaskService.php`（任务生命周期与 JSON 字段策略）
- `app/controller/Api/V1/HealthController.php`（数据库与 schema 实际健康检查）

## 本地自检

可在不启动服务情况下检查路由绑定是否存在对应控制器与方法：

```bash
php scripts/route_check.php
php scripts/go_route_parity_check.php
php scripts/schema_check.php
php scripts/task_contract_check.php
./scripts/check_all.sh
```

## 下一步

1. 将 mock 接口替换成真实 AI 图像/视频/音频服务调用。
2. 引入 ThinkPHP migration（替代运行时自动建表）。
3. 增加集成测试（接口级别），覆盖关键业务流。

# ThinkPHP 6.0 重写版本（后端）

这是把当前 Go(Gin) 后端迁移到 ThinkPHP 6 的第一版骨架，实现目标：

- 保留统一入口 `/api/v1/*`
- 保留健康检查 `/health`、`/api/v1/health`
- 通过网关控制器承接原有模块（如 dramas、images、videos、tasks 等）
- 提供统一 JSON 返回格式，便于前端逐步无缝切换

## 目录结构

```text
thinkphp6/
├── app/
│   ├── common/BaseApiController.php
│   └── controller/Api/V1/
│       ├── GatewayController.php
│       └── HealthController.php
├── config/
│   ├── app.php
│   └── database.php
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

默认接口：

- `GET /health`
- `GET /api/v1/health`
- `ANY /api/v1/{module}/{path?}`

## 路由迁移策略

Go 版 `api/routes/routes.go` 中所有模块路由，先迁移为：

- 一级模块：`{module}`（例如 `dramas`, `ai-configs`, `generation`, `images`）
- 其余路径：`{path}` 原样传入网关控制器

后续可按模块拆分：

1. 先落地 `app/model`（对应 domain/models）
2. 再拆 `app/service`（对应 application/services）
3. 最后把 `GatewayController` 按资源替换为独立控制器

## 与原 Go 版的关系

- 原项目仍可继续运行，ThinkPHP 版本位于 `thinkphp6/` 子目录
- 可以通过 Nginx / API 网关把 `/api/v1` 切换到 ThinkPHP 服务，进行灰度迁移

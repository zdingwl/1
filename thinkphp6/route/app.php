<?php

declare(strict_types=1);

use think\facade\Route;

Route::get('health', 'Api.V1.HealthController/index');

Route::group('api/v1', function () {
    // 精确保留健康检查（对齐 Go 版）
    Route::get('health', 'Api.V1.HealthController/index');

    // 兼容原有模块入口，由网关进一步分发。
    Route::any(':module/:path?', 'Api.V1.GatewayController/handle')
        ->pattern([
            'module' => '[a-z\-]+',
            'path' => '.*',
        ]);
});

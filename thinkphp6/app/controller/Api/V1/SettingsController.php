<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\AppSetting;
use app\service\SchemaService;
use think\Request;

class SettingsController extends BaseApiController
{
    use RequestPayload;
    public function language()
    {
        SchemaService::ensureCoreTables();
        $item = AppSetting::where('setting_key', 'language')->find();
        $language = $item ? (string)$item['setting_value'] : 'zh-CN';

        return $this->success(['language' => $language]);
    }

    public function updateLanguage(Request $request)
    {
        SchemaService::ensureCoreTables();

        $payload = $this->payload($request);
        $language = trim((string)($payload['language'] ?? 'zh-CN'));
        if ($language === '') {
            return $this->error('language is required', 422);
        }

        $item = AppSetting::where('setting_key', 'language')->find();
        if ($item) {
            $item->save(['setting_value' => $language, 'updated_at' => $this->now()]);
        } else {
            AppSetting::create(['setting_key' => 'language', 'setting_value' => $language, 'updated_at' => $this->now()]);
        }

        return $this->success(['language' => $language], 'updated');
    }
}

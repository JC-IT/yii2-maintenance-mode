<?php

declare(strict_types=1);

namespace JCIT\maintenance\actions;

use JCIT\maintenance\components\MaintenanceMode;
use yii\base\Action;

class Maintenance extends Action
{
    public function run(
        MaintenanceMode $maintenance,
    ): string {
        return $this->controller->render(
            '@vendor/jc-it/yii2-maintenance-mode/src/views/actions/maintenance.php',
            [
                'status' => $maintenance->get(),
            ]
        );
    }
}

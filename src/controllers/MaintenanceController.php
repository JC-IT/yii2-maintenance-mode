<?php
declare(strict_types=1);

namespace JCIT\maintenance\controllers;

use JCIT\maintenance\components\MaintenanceMode;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\di\Instance;
use yii\helpers\Console;

class MaintenanceController extends Controller
{
    public $defaultAction = 'status';

    public function actionEnable(
        int $duration = null,
        string $message = null,
        MaintenanceMode $maintenance,
    ): int {
        $maintenance->enable($duration, $message);
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    public function actionExtend(
        int $duration,
        MaintenanceMode $maintenance,
    ): int {
        $maintenance->extend($duration);
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    public function actionStatus(
        MaintenanceMode $maintenance,
    ): int {
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    public function actionUpdate(
        string $message,
        MaintenanceMode $maintenance,
    ): int {
        $maintenance->update($message);
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    protected function printStatus(MaintenanceMode $maintenanceMode): void
    {
        if ($status = $maintenanceMode->get()) {
            if (!empty($status['messages'])) {
                $messages = "with messages:" . implode(PHP_EOL, array_map(function ($key, $value) {
                        $datetime = date('d-m-Y H:i:s', $key);
                        return Console::ansiFormat("- ({$datetime}): {$value}", [Console::FG_YELLOW]);
                    }, array_keys($status['messages']), $status['messages']));
            } else {
                $messages = "without a message.";
            }

            echo strtr("Maintenance mode has been {maintenanceStatus}, from {from} until {until}, the platform is {platformStatus}, {messages}\n", [
                '{maintenanceStatus}' => Console::ansiFormat("ACTIVE", [Console::FG_GREEN]),
                '{from}' => Console::ansiFormat(date('d-m-Y H:i:s', $status['from']), [Console::FG_CYAN]),
                '{until}' => Console::ansiFormat(!empty($status['until']) ? date('d-m-Y H:i:s', $status['until']) : 'NO END', [Console::FG_CYAN]),
                '{platformStatus}' => Console::ansiFormat("NOT REACHABLE", [Console::FG_RED]),
                '{messages}' => $messages,
            ]);
        } else {
            echo strtr("Maintenance mode is currently {maintenanceStatus}, the platform should be {platformStatus}\n", [
                '{maintenanceStatus}' => Console::ansiFormat("NOT ACTIVE", [Console::FG_RED]),
                '{platformStatus}' => Console::ansiFormat("REACHABLE", [Console::FG_GREEN])
            ]);
        }
    }
}

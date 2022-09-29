<?php

declare(strict_types=1);

namespace JCIT\maintenance\controllers;

use JCIT\maintenance\components\MaintenanceMode;
use yii\base\InvalidArgumentException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class MaintenanceController extends Controller
{
    public $defaultAction = 'status';

    // Options
    public ?int $duration = null;
    public ?string $message = null;

    public function actionDisable(
        MaintenanceMode $maintenance,
    ): int {
        $maintenance->disable();
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    public function actionEnable(
        MaintenanceMode $maintenance,
    ): int {
        $maintenance->enable($this->duration, $this->message);
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    public function actionExtend(
        MaintenanceMode $maintenance,
    ): int {
        if (is_null($this->duration)) {
            $this->stdout('--duration option is required.' . PHP_EOL, Console::FG_RED);
            return ExitCode::IOERR;
        }

        $maintenance->extend($this->duration);
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
        MaintenanceMode $maintenance,
    ): int {
        if (is_null($this->message)) {
            $this->stdout('--message option is required.' . PHP_EOL, Console::FG_RED);
            return ExitCode::IOERR;
        }

        $maintenance->update($this->message);
        $this->printStatus($maintenance);

        return ExitCode::OK;
    }

    public function options($actionID)
    {
        $result = parent::options($actionID);

        $extraOptions = [
            'enable' => [
                'duration',
                'message',
            ],
            'extend' => [
                'duration',
            ],
            'update' => [
                'message',
            ],
        ];

        return ArrayHelper::merge(
            $result,
            $extraOptions[$actionID ?? []]
        );
    }

    protected function printStatus(MaintenanceMode $maintenanceMode): void
    {
        if ($status = $maintenanceMode->get()) {
            if (!empty($status['messages'])) {
                $messages = "with messages:" . PHP_EOL . implode(PHP_EOL, array_map(function ($key, $value) {
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
            echo strtr("Maintenance mode is currently {maintenanceStatus}, the platform should be {platformStatus}.\n", [
                '{maintenanceStatus}' => Console::ansiFormat("NOT ACTIVE", [Console::FG_RED]),
                '{platformStatus}' => Console::ansiFormat("REACHABLE", [Console::FG_GREEN])
            ]);
        }
    }
}

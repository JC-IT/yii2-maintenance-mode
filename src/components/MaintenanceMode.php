<?php
declare(strict_types=1);

namespace JCIT\maintenance\components;

use Closure;
use yii\base\BootstrapInterface;
use yii\base\InvalidCallException;
use yii\caching\CacheInterface;
use yii\di\Instance;
use yii\web\Application;

class MaintenanceMode implements BootstrapInterface
{
    public const MAINTENANCE_MODE = 'maintenanceMode';
    public const MAINTENANCE_MODE_DURATION = 'maintenanceModeDuration';
    public const MAINTENANCE_MODE_MESSAGES = 'maintenanceModeMessages';

    // The cache where the maintenance mode is stored. If you want maintenance mode not to be stored in the common
    // cache, add the name of the component or a configuration.
    public CacheInterface|array|string $cache = 'cache';

    public function __construct(
        private Closure|null $applyMaintenanceMode = null
    ) {
        if (!isset($this->applyMaintenanceMode)) {
            $this->applyMaintenanceMode = function(Application $application) {
                $application->catchAll = ['site/maintenance'];
                $application->set('session', null);
                $application->user->enableSession = false;
                $application->set('db', null);
            };
        }
    }

    public function bootstrap($app)
    {
        $this->cache = Instance::ensure($this->cache, CacheInterface::class);

        if ($app instanceof Application) {
            $this->checkMaintenanceMode($app);
        }
    }

    private function checkMaintenanceMode(Application $app): void
    {
        if ($this->cache->exists(self::MAINTENANCE_MODE)) {
            ($this->applyMaintenanceMode)($app);
        }
    }

    public function disable(): void
    {
        $this->cache->delete(self::MAINTENANCE_MODE);
        $this->cache->delete(self::MAINTENANCE_MODE_DURATION);
        $this->cache->delete(self::MAINTENANCE_MODE_MESSAGES);
    }

    public function enable(int $duration = null, string $message = null): void
    {
        if ($this->cache->exists(self::MAINTENANCE_MODE)) {
            throw new InvalidCallException('Cannot activate Maintenance Mode when it is already active, use update if needed.');
        }
        $this->cache->set(self::MAINTENANCE_MODE, time(), $duration);

        if (!empty($duration)) {
            $this->extend($duration);
        }

        if (!empty($message)) {
            $this->update($message);
        }
    }

    public function extend(int $duration): void
    {
        if (!$this->cache->exists(self::MAINTENANCE_MODE)) {
            throw new InvalidCallException('Cannot extend Maintenance Mode when it is not active, use enable if needed.');
        }

        $this->cache->set(self::MAINTENANCE_MODE, $this->cache->get(self::MAINTENANCE_MODE), $duration);
        $this->cache->set(self::MAINTENANCE_MODE_DURATION, $duration);
    }

    public function get(): array|bool
    {
        if (!$this->cache->exists(self::MAINTENANCE_MODE)) {
            return false;
        }

        $start = $this->cache->exists(self::MAINTENANCE_MODE);
        return [
            'from' => $start,
            'until' => $this->cache->exists(self::MAINTENANCE_MODE_DURATION) ? $start->clone()->addSeconds($this->cache->get(self::MAINTENANCE_MODE_DURATION)) : null,
            'duration' => $this->cache->get(self::MAINTENANCE_MODE_DURATION),
            'messages' => $this->cache->get(self::MAINTENANCE_MODE_MESSAGES),
        ];
    }

    public function update(string $message): void
    {
        if (!$this->cache->exists(self::MAINTENANCE_MODE)) {
            throw new InvalidCallException('Cannot update Maintenance Mode when it is not active, use enable if needed.');
        }

        $messages = $this->cache->exists(self::MAINTENANCE_MODE_MESSAGES) ? $this->cache->get(self::MAINTENANCE_MODE_MESSAGES) : [];
        $messages[time()] = $message;
        $this->cache->set(self::MAINTENANCE_MODE_MESSAGES, $messages);
    }
}

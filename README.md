# Maintenance mode for Yii2

This extension provides a maintenance mode implementation for Yii2.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require jc-it/yii2-maintenance-mode
```

or add

```
"jc-it/yii2-maintenance-mode": "^<latest version>"
```

to the `require` section of your `composer.json` file.

## Configuration
- Add maintenance mode as component
```php
...
'bootstrap' => ['maintenance'],
'components' => [
    'maintenance' => [
        'class' => \JCIT\maintenance\components\MaintenanceMode::class,
    ],
],
```
- Add the console controller
```php
class MaintenanceController extens \JCIT\maintenance\controllers\Maintenance
```
- Add a Maintenance action to your SiteController (i.e.)
```php
class SiteController extends \yii\web\Controller
{
    public function actions(): array
    {
        return [
            'maintenance' => \JCIT\maintenance\actions\Maintenance::class,
        ];      
    }
}
```

## Usage

To enable maintenance mode, run the `maintenance/enable` console command with optionally a duration and message.

```
src/yii maintenance/enable --message "We are performing maintenance." --duration 3600
```

To extend the duration of the maintenance mode use the `maintenance/extend` console command.

```
src/yii maintenance/extend --duration 3600
```

To add an update to the maintenance mode use the `maintenance/update` console command.

```
src/yii maintenance/update --message "Maintenance update"
```

To disable maintenance mode, run the `maintenance/disable` console command.

```
src/yii maintenance/disable
```

## TODO
- Add tests

## Credits
- [Joey Claessen](https://github.com/joester89)
- [HeRAMS WHO](https://github.com/HeRAMS-WHO)
- [Sam Mousa](https://github.com/SamMousa)

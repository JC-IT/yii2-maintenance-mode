<?php
declare(strict_types=1);

use yii\web\View;

/**
 * @var array $status
 * @var View $this
 */

?>

<div class="container">
    <?php if (!empty($status)) { ?>
        <h1>Maintenance mode</h1>
        <div class="message"><?php echo
            empty($status['until'])
            ? 'We are in maintenance mode. Please try again later.'
            : 'We are in maintenance mode. We expect this to take until ' . date('d-m-Y H:i:s', $status['until']) . '.'
        ?></div>
        <?php if (!empty($status['messages'])) { ?>
            <div class='updates'>
                Updates:<br>
                <?php
                echo \yii\helpers\Html::ul(array_map(function ($key, $value) {
                    $datetime = date('d-m-Y H:i:s', $key);
                    return "({$datetime}): {$value}";
                }, array_keys($status['messages']), $status['messages']));
                ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <h1>Everything is normal.</h1>
    <?php } ?>
</div>

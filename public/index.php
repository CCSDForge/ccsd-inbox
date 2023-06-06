<?php
/**
 * Based on vendor/cottagelabs/coar-notifications/docker/inbox.php
 */


use cottagelabs\coarNotifications\COARNotificationManager;
use cottagelabs\coarNotifications\orm\OutboundCOARNotification;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();


$logger = new Logger('NotifyCOARLogger');
$handler = new RotatingFileHandler(__DIR__ . '/../log/NotifyCOARLogger.log',
    0, Logger::DEBUG, true, 0664);
$formatter = new LineFormatter(null, null, false, true);
$handler->setFormatter($formatter);
$logger->pushHandler($handler);

// the connection configuration
$conn = [];
require __DIR__ . '/db-config.php';

try {
    $coarNotificationManager = new COARNotificationManager($conn, $logger);
} catch (\cottagelabs\coarNotifications\orm\COARNotificationNoDatabaseException $e) {
    http_send_status('500');
    echo 'Database unavailable';
    if (isset($logger)) {
        $logger->error($e->getMessage());
    }
    return;
}

$notifications = [];


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $coarNotificationManager->setOptionsResponseHeaders();
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $coarNotificationManager->getPostResponse();
    } catch (\cottagelabs\coarNotifications\orm\COARNotificationNoDatabaseException $e) {
        http_send_status('500');
        echo 'Service unavailable';
        if (isset($logger)) {
            $logger->error($e->getMessage());
        }
        return;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET["id"]) && ($_GET["id"] !== '')) {
        $oneNotification = $coarNotificationManager->getNotificationById($_GET["id"]);
        if ($oneNotification === null) {
            $notificationNotFound = sprintf('<div class="alert alert-warning" role="alert">%s Not found</div>', htmlspecialchars($_GET["id"]));
            $notifications = [];
        } else {
            $notifications[] = $oneNotification;
            $notificationFound = sprintf('<div class="alert alert-info" role="alert">%s found</div>', htmlspecialchars($_GET["id"]));
        }

    } else {
        $notifications = $coarNotificationManager->getNotifications();
    }
}


?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_ENV["APP_NAME"] ?> - COAR Notification Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.8.0/build/styles/default.min.css">
</head>

<body>

<style>
    td {
        font-size: 90%;
    }
</style>

<?php include_once 'navbar-top.php'; ?>

<?php if (isset($notificationNotFound) && $notificationNotFound !== '') {
    echo $notificationNotFound;
}
if (isset($notificationFound) && $notificationFound !== '') {
    echo $notificationFound;
}
?>

<div class="container-fluid">

    <div class="row">
        <div class="col">
            <?php

            $inCounter = 0;
            $outCounter = 0;

            $headerTpl = '<table id="inbound" class="table table-striped table-hover">
                            <caption>%s</caption>
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Id</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Type</th>
                                    <th>Show Content</th>
                                </tr>
                            </thead>';

            $lineTpl = '<tr>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td><span class="badge bg-dark">%s</span></td>
                            <td><button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#%s" aria-expanded="false" aria-controls="%s">Notification</button></td>
                            </tr>
                            <tr>
                            <td colspan="6">
                                <div class="collapse" id="%s">
                                    <div class="card card-body">
                                        <pre><code class="language-json">%s</code></pre>
                                    </div>
                                </div>
                            </td>
                        </tr>';


            $inBound = sprintf($headerTpl, 'Inbound');
            $outBound = sprintf($headerTpl, 'Outbound');

            foreach ($notifications as $oneNotification) {

                /**
                 * @var $oneNotification OutboundCOARNotification
                 */
                $cnTime = $oneNotification->getTimestamp()->format('D, d M Y H:i:s');
                $cnId = htmlspecialchars($oneNotification->getId());
                $cnFromId = htmlspecialchars($oneNotification->getFromId());
                $cnToId = htmlspecialchars($oneNotification->getToId());
                $cnIdHash = htmlspecialchars('_' . md5($oneNotification->getId()));
                $cnType = htmlspecialchars($oneNotification->getType());

                try {
                    $cnOriginal = $oneNotification->getOriginal();
                    $json_decodedOriginalNotification = json_decode($cnOriginal, false, 512, JSON_THROW_ON_ERROR);
                    $cnOriginal = json_encode($json_decodedOriginalNotification, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    $cnOriginal = htmlspecialchars($cnOriginal, true);
                } catch (JsonException $e) {
                    $cnOriginal = '';
                }


                $cnLineData = sprintf($lineTpl, $cnTime, $cnId, $cnFromId, $cnToId, $cnType, $cnIdHash, $cnIdHash, $cnIdHash, $cnOriginal);

                if ($oneNotification instanceof OutboundCOARNotification) {
                    $outBound .= $cnLineData;
                    $outCounter++;
                } else {
                    $inBound .= $cnLineData;
                    $inCounter++;
                }
            }
            echo '<h1>Inbox Notifications</h1>';
            ?>
            <nav class="nav">
                <a class="nav-link" href="#inbound">Inbound&nbsp;<span
                            class="badge bg-success"><?= $inCounter ?></span></a> <a class="nav-link" href="#outbound">Outbound&nbsp;<span
                            class="badge bg-primary"><?= $outCounter ?></span></a>
            </nav>
            <?php if ($inCounter > 0) : ?>
                <h2>Inbound</h2>
                <?= $inBound; ?>
                </tbody></table>
                <hr>
            <?php endif; ?>

            <?php if ($outCounter > 0) : ?>
                <h2>Outbound</h2>
                <?= $outBound; ?>
                </tbody></table>
                <hr>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'navbar-bottom.php' ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.8.0/build/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
</body>
</html>

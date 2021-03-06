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

$coarNotificationManager = new COARNotificationManager($conn, true, $logger);
$notifications = $coarNotificationManager->get_notifications();

?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_ENV["APP_NAME"] ?> - COAR Notification Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.5.1/build/styles/default.min.css">
</head>

<body>

<style>
    td { font-size:90%; }
</style>

<?php include 'navbar-top.php'; ?>

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
                                        <pre><code class="language-php">%s</code></pre>
                                    </div>
                                </div>
                            </td>
                        </tr>';


            $inBound = sprintf($headerTpl, 'Inbound');
            $outBound = sprintf($headerTpl, 'Outbound');

            foreach ($notifications->findAll() as $notification) {

                /**
                 * @var $notification OutboundCOARNotification
                 */
                $cnTime = $notification->getTimestamp()->format('D, d M Y H:i:s');
                $cnId = htmlspecialchars($notification->getId());
                $cnFromId = htmlspecialchars($notification->getFromId());
                $cnToId = htmlspecialchars($notification->getToId());
                $cnIdHash = htmlspecialchars('_' . md5($notification->getId()));
                $cnType = htmlspecialchars($notification->getType());

                try {
                    $cnOriginal = json_decode($notification->getOriginal(), true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    $cnOriginal = '';
                }
                $cnOriginal = htmlspecialchars(var_export($cnOriginal,true));

                $cnLineData = sprintf($lineTpl, $cnTime, $cnId, $cnFromId, $cnToId, $cnType, $cnIdHash, $cnIdHash,$cnIdHash, $cnOriginal);

                if ($notification instanceof OutboundCOARNotification) {
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
                            class="badge bg-success"><?= $inCounter ?></span></a>
                <a class="nav-link" href="#outbound">Outbound&nbsp;<span
                            class="badge bg-primary"><?= $outCounter ?></span></a>
            </nav>
            <?php
            if ($inCounter > 0) {
                echo '<h2>Inbound</h2>';
                print("$inBound</tbody></table><hr>");
            }

            if ($outCounter > 0) {
                echo '<h2>Outbound</h2>';
                print("$outBound</tbody></table>");
            }
            ?>
        </div>

    </div>
</div>

<?php include 'navbar-bottom.php' ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha256-cMPWkL3FzjuaFSfEYESYmjF25hCIL6mfRSPnW8OVvM4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.5.1/build/highlight.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.5.1/build/languages/php.min.js"></script>
<script>hljs.highlightAll();</script>
</body>
</html>
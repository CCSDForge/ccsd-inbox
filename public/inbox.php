<?php
/**
 * Based on vendor/cottagelabs/coar-notifications/docker/inbox.php
 */


use cottagelabs\coarNotifications\COARNotificationManager;
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
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">
            <img src="/img/episciences.svg" alt="application logo" height="30" class="d-inline-block align-text-top">
            <?= $_ENV["APP_NAME"] ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Inbox</a>
                <a class="nav-link active" aria-current="page" href="#">List content</a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">

    <div class="row">
        <div class="col">
            <?php

            $inCounter = 0;
            $outCounter = 0;
            $inBound = '<table id="inbound" class="table table-striped table-hover"><caption>Inbound</caption><thead class="table-light"><tr><th>Time</th><th>Id</th></tr></thead>';
            $outBound = '<table id="outbound"class="table table-striped table-hover"><caption>Outbound</caption><thead class="table-light"><tr><th>Time</th><th>Id</th></tr></thead><tbody>';

            foreach ($notifications->findAll() as $notification) {
                $time = $notification->getTimestamp()->format('D, d M Y H:i:s');
                $id = $notification->getId();

                if ($notification instanceof \cottagelabs\coarNotifications\orm\OutboundCOARNotification) {
                    $outBound .= "<tr>";
                    $outBound .= "<td>$time</td><td>$id</td></tr>";
                    $outCounter++;
                } else {
                    $inBound .= "<tr>";
                    $inBound .= "<td>$time</td><td>$id</td></tr>";
                    $inCounter++;
                }
            }
            echo '<h1>Inbox Notifications</h1>';
            ?>
            <nav class="nav">
                <a class="nav-link" href="#inbound">Inbound&nbsp;<span
                            class="badge bg-secondary"><?= $inCounter ?></span></a>
                <a class="nav-link" href="#outbound">Outbound&nbsp;<span
                            class="badge bg-secondary"><?= $outCounter ?></span></a>
            </nav>
            <?php
            //   print("<h2>Inbound: $inCounter</h2>");
            // print("<h2>Outbound: $outCounter</h2>");

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

<div class="container" style="margin-top: 5%;">
    <nav class="navbar fixed-bottom navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><img
                        src="<?= $_ENV["APP_BRANDING_ICON"] ?>"
                        style="height: 50px" alt="brand logo"></a><?= $_ENV["APP_BRANDING"] ?>
        </div>
    </nav>
</div>

</body>
</html>
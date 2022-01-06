<?php
/**
 * Based on vendor/cottagelabs/coar-notifications/docker/index.php
 */

use cottagelabs\coarNotifications\COARNotificationActor;
use cottagelabs\coarNotifications\COARNotificationContext;
use cottagelabs\coarNotifications\COARNotificationManager;
use cottagelabs\coarNotifications\COARNotificationObject;
use cottagelabs\coarNotifications\COARNotificationTarget;
use cottagelabs\coarNotifications\COARNotificationURL;
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

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coarNotificationManager = new COARNotificationManager($conn, false, $logger);

    $actor = new COARNotificationActor($_POST["actor_id"],
        $_POST["actor_name"], $_POST["actor_type"]);

    $object = new COARNotificationObject($_POST["object_id"],
        $_POST["object_ietf:cite-as"], explode(",", $_POST["object_type"]));

    $url = new COARNotificationURL($_POST["context_url_id"],
        $_POST["context_url_media-type"],
        explode(",", $_POST["context_url_type"]));

    $context = new COARNotificationContext($_POST["context_id"],
        $_POST["context_ietf:cite-as"],
        explode(",", $_POST["context_type"]), $url);

    $target = new COARNotificationTarget($_POST["target_id"],
        $_POST["target_inbox"]);


    $notification = $coarNotificationManager->createOutboundNotification($actor, $object, $context, $target);

    $type = explode(",", $_POST["type"]);

    if (in_array("Announce", $type, true) && in_array("coar-notify:ReviewAction", $type, true)) {
        $coarNotificationManager->announceReview($notification);
    } else if (in_array("Announce", $type, true) && in_array("coar-notify:EndorsementAction", $type, true)) {
        $coarNotificationManager->announceEndorsement($notification);
    } else {
        $coarNotificationManager->requestReview($notification);
    }

    $msg = $notification->getId() . " created";
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_ENV["APP_NAME"] ?> - COAR Notification Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="/js/formTo.js"></script>

</head>

<body>

<?php include 'navbar-top.php'; ?>

<div class="container-fluid">

    <div class="row">
        <div class="col">
            <form method="post">

                <fieldset>
                    <legend>Actor</legend>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="actor_id">Id:</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" id="actor_id">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="actor_name">Name:</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" id="actor_name"></div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="actor_type">Type:</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" id="actor_type">
                        </div>
                    </div>

                </fieldset>

                <fieldset>
                    <legend>Object</legend>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="object_id">Id:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="object_id"></div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="object_ietf:cite-as">Cite as:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="object_ietf:cite-as"></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="object_type">Type:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="object_type"></div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Context</legend>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="context_id">Id:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="context_id"></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="context_ietf:cite-as">Cite as:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="context_ietf:cite-as"></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="context_type">Type:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="context_type"></div>
                    </div>

                    <fieldset>
                        <legend>URL</legend>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label" for="context_url_id">Id:</label>
                            <div class="col-sm-10"><input class="form-control" type="text" id="context_url_id"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label"
                                   for="context_url_media-type">Media-type:</label>
                            <div class="col-sm-10"><input class="form-control" type="text" id="context_url_media-type">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label"
                                   for="context_url_type">Type:</label>
                            <div class="col-sm-10"><input class="form-control" type="text" id="context_url_type"></div>
                        </div>
                    </fieldset>
                </fieldset>

                <fieldset>
                    <legend>Origin</legend>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="origin_id">Id:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="origin_id"></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="origin_inbox">Inbox:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="origin_inbox"></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="origin_type">Type:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="origin_type"></div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Target</legend>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="target_id">Id:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="target_id"></div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="target_inbox">Inbox:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="target_inbox"></div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="target_type">Type:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="target_type"></div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Type</legend>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="type">Type:</label>
                        <div class="col-sm-10"><input class="form-control" type="text" id="type"></div>
                    </div>

                </fieldset>
        </div>

        <div class="col">

            <fieldset>
                <legend>Preview</legend>
                <pre id="preview" style="background-color: #cecece;padding:1em;"></pre>
            </fieldset>
            <br><?= $msg ?>

            <input class="btn btn-primary btn-lg" type="submit" value="Send">
            </form>

        </div>

    </div>
</div>

<?php include 'navbar-bottom.php' ?>

</body>
</html>

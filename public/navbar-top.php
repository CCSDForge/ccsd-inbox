<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">
            <img src="<?= $_ENV["APP_LOGO"] ?>" alt="application logo" height="30" class="d-inline-block align-text-top">
            Inbox&nbsp;<?= $_ENV["APP_NAME"] ?>&nbsp<span class="badge bg-info" style="font-size:40%"><?= $_ENV['ENV'] ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link btn btn-outline-secondary" href="/">Inbox</a>
                &nbsp;
                <a class="nav-link btn btn-outline-secondary" href="send.php">Send</a>
            </div>
        </div>
    </div>
</nav>
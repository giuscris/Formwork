<!DOCTYPE html>
<html lang="<?= $site->languages()->current() ?>">
<head>
    <title><?= $this->escape($page->title()) ?> | <?= $this->escape($site->title()) ?></title>
    <?= $this->insert('_meta') ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?= $this->assets()->uri('css/style.min.css') ?>">
    <script src="<?= $this->assets()->uri('js/script.min.js') ?>"></script>
</head>
<body>
<?= $this->insert('_menu') ?>
<?= $this->insert('_cover-image') ?>
<?= $this->content() ?>
    <footer>
        <div class="container small">
            &copy; 2017-2020 &mdash; Made with <a href="https://github.com/getformwork/formwork">Formwork</a>
        </div>
    </footer>
</body>
</html>

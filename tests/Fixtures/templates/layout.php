<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $this->show('title', 'Layout Title') ?></title>
</head>
<body>
  <?php $this->section('body') ?>
    Content from Layout
  <?php $this->section(true) ?>
  <?= $this->show('another', 'No another') ?>
</body>
</html>

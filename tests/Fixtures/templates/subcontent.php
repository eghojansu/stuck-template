<?php $this->extends('content') ?>

<?php $this->section('title') ?>Subcontent Title | <?= $this->parent() ?><?php $this->section() ?>

<?php $this->section('body') ?>
  Override by Subcontent

  <?php $this->section('content') ?>
    Subcontent inside body
    Chain result: "<?= $this->chain(' <b>foo</b>  ')->ltrim()->rtrim() ?>"
  <?php $this->section(true) ?>
<?php $this->section() ?>

<?php $this->extends('layout') ?>

<?php $this->section('title', 'Content Title') ?>

<?php $this->section('body') ?>
  Body from content
<?php $this->section(true) ?>

<?php $this->section('another') ?>
  Another content
<?php $this->section(true) ?>

<?php
/**
 * @file
 * media.inc
 */

function _skyword_media_index($page, $per_page, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/MediaController.php';
  $controller = new MediaController();

  return $controller->index($page, $per_page, $fields);
}

function _skyword_media_retrieve($id, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/MediaController.php';
  $controller = new MediaController();

  return $controller->retrieve($id, $fields);
}

function _skyword_media_create($file) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/MediaController.php';
  $controller = new MediaController();
  return $controller->create($file);
}

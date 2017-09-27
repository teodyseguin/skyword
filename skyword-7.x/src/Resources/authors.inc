<?php

/**
 * Resource callback for Authors Index.
 */
function _skyword_authors_index($page, $per_page, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/AuthorController.php';
  $controller = new AuthorController();

  return $controller->index($page, $per_page, $fields);
}

function _skyword_authors_retrieve($id, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/AuthorController.php';
  $controller = new AuthorController();

  return $controller->retrieve($id, $fields);
}

/**
 * Resource callback for Creating a Author.
 */
function _skyword_authors_create($data) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/AuthorController.php';
  $controller = new AuthorController();

  return $controller->create($data);
}


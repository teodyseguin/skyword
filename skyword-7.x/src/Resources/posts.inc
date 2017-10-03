<?php

/**
 * @file
 * posts.inc
 */

function _skyword_posts_index($page, $per_page, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/PostsController.php';
  $controller = new PostsController();

  return $controller->index($page, $per_page, $fields);
}

function _skyword_posts_retrieve($id, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/PostsController.php';
  $controller = new PostsController();

  return $controller->retrieve($id, $fields);
}

function _skyword_posts_create($data) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/PostsController.php';
  $controller = new PostsController($data);

  return $controller->create();
}

function _skyword_posts_update() {}


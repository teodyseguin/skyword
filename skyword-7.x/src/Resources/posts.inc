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

function _skyword_posts_retrieve() {
  require drupal_get_path('module', 'skyword') . '/src/Controller/PostsController.php';
  $controller = new PostsController();

  return $controller->retrieve($id);
}

function _skyword_posts_create() {
  require drupal_get_path('module', 'skyword') . '/src/Controller/PostsController.php';
  $controller = new PostsController();

  return $controller->create();
}

function _skyword_posts_update() {}


<?php
/**
 * @file
 * posts.inc
 */

function _skyword_posts_index() {
  include_once '../Controller/PostsController.php';
  $controller = new PostsController();
  return $controller->index($page, $per_page, $fields);
}

function _skyword_posts_retrieve() {
  include_once '../Controller/PostsController.php';
  $controller = new PostsController();
  return $controller->retrieve($id);
}

function _skyword_posts_create() {
  include_once '../Controller/PostsController.php';
  $controller = new PostsController();
  return $controller->create();
}

function _skyword_posts_update() {

}

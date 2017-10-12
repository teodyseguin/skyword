<?php

/**
 * @file
 * content_types.inc
 *  -- We need to return field types
 *  -- If list field or other limited-value, send allowed value list.
 */

function _skyword_content_types_index($page, $per_page, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/ContentTypesController.php';
  $controller = new ContentTypesController();

  return $controller->index($page, $per_page, $fields);
}

function _skyword_content_types_retrieve($type, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/ContentTypesController.php';
  $controller = new ContentTypesController();

  return $controller->retrieve($type, $fields);
}

function _skyword_content_types_create($data) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/ContentTypesController.php';
  $controller = new ContentTypesController();

  return $controller->create($data);
}


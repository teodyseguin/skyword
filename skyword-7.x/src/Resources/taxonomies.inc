<?php
/**
 * @file
 * taxonomies.inc
 */

function _skyword_taxonomies_index($page, $per_page, $fields) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->index($page, $per_page, $fields);
}

function _skyword_taxonomies_retrieve($id, $terms) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->retrieve($id, $terms);
}

function _skyword_taxonomies_create($data, $id, $terms) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->create($data, $id, $terms);
}


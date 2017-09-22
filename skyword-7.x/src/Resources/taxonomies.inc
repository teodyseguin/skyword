<?php
/**
 * @file
 * taxonomies.inc
 */


function _skyword_taxonomies_index($page = 1, $per_page = 250, $fields = NULL) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->index($page, $per_page, $fields);
}

function _skyword_taxonomies_retrieve($id) {
  require drupal_get_path('module', 'skyword') . '/src/Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->retrieve($id);
}

function _skyword_taxonomies_create() {
  require drupal_get_path('module', 'skyword') . '/src/Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->create();
}

<?php
/**
 * @file
 * taxonomies.inc
 */


function _skyword_taxonomies_index($page = 1, $per_page = 250, $fields = NULL) {
  include_once '../Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->index($page, $per_page, $fields);
}

function _skyword_taxonomies_retrieve($id) {
  include_once '../Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->retrieve($id);
}

function _skyword_taxonomies_create() {
  include_once '../Controller/TaxonomyController.php';
  $controller = new TaxonomyController();
  return $controller->create();
}

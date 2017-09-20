<?php
/**
 * @file
 * taxonomies.inc
 */

include '../Controller/TaxonomyController.php';

$controller = new TaxonomyController();

function _skyword_taxonomies_index($page = 1, $per_page = 250, $fields = NULL) {
  return $controller->index($page, $per_page, $fields); 
}

function _skyword_taxonomies_retrieve($id) {
  return $controller->retrieve($id);
}

function _skyword_taxonomies_create() {
  return $controller->create();
}


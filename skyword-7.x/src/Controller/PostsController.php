<?php

include 'BaseController.php';
include 'ControllerInterface.php';

class PostController extends BaseController implements ControllerInterface {

  /**
   * Returns a list of Taxonomies
   *
   * @param $page
   *   determins the number of page to return. default to 1
   * @param $per_page
   *   determines the number of items to be included in a page. default to 250
   * @param $fields
   *   determines the field names to be included on the data. default to NULL
   */
  public function index($page = 1, $per_page = 250, $fields = NULL) {

  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $fields = NULL) {

  }

  /**
   * Create a Taxonomy
   */
  public function create($name, $description) {

  }

  /**
   * Update a Taxonomy
   */
  public function update() {}

  /**
   * Delete a Taxonomy
   */
  public function delete() {}

  /**
   * Build the data normally
   *
   * @param $taxonomies
   *   an array of taxonomies
   */
  private function buildData($taxonomies) {

  }

  /**
   * Build the data per count
   *
   * @param $per_page
   *   the number of items to be included in a page
   * @param $taxonomies
   *   an array of taxonomies
   */
  private function buildDataWithCount($per_page, $taxonomies) {

  }

}

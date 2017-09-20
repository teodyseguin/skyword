<?php

include 'BaseController.php';
include 'ControllerInterface.php';

class TaxonomyController extends BaseController implements ControllerInterface {

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
    $taxonomies = taxonomy_get_vocabularies();
    $data = NULL;

    if (count($taxonomies) < $per_page) {
      $data = $this->buildData($taxonomies); 
    }
    else {
      $data = $this->buildDataWithCount($per_page, $taxonomies);
    } 

    if ($fields != NULL) {
      parent::limitOutputByFields($fields, $data);
    }

    return $data;
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $fields = NULL) {
    $taxonomy = taxonomy_vocabulary_lod($id); 
    $data = $this->buildData($taxonomy);

    if ($fields != NULL) {
      parent::limitOutputByFields($fields, $data);
    }

    return $data;
  }

  /**
   * Create a Taxonomy
   */
  public function create($name, $description) {
    $explodeName = explode(' ', $name);
    $machineName = implode('_', $explodeName);

    $taxonomy = new stdClass();
    $taxonomy->name = $name;
    $taxonomy->machine_name = $machineName;
    $taxonomy->description = t($description);
    $taxonomy->module = 'taxonomy';

    try {
      taxonomy_vocabulary_save($taxonomy);
    }
    catch (Exception $e) {
      throw new Exception("Unable to create a Taxonomy named $name"); 
    }
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
    if (is_array($taxonomies)) {
      $container = [];

      foreach ($taxonomies as $taxonomy) {
        $obj = new stdClass();
        $obj->id = $taxonomy->vid;
        $obj->name = $taxonomy->name;
        $obj->description = $taxonomy->description;
        $obj->numTerms = $this->getTaxonomyTermsCount($taxonomy->vid);

        $container[] = $obj; 
      } 

      return $container;
    } 
    else {
      $obj = new stdClass();
      $obj->id = $taxonomies->vid;
      $obj->name = $taxnomies->name;
      $obj->description = $taxonomies->description;
      $obj->numTerms = $this->getTaxonomyTermsCount($taxonomies->vid);

      return $obj;
    }
  }

  /**
   * Build the data per count
   *
   * @param $per_page
   *   the number of items to be included in a page
   * @param $taxonomies
   *   an array of taxonomies
   */
  private buildDataWithCount($per_page, $taxonomies) {
    $counter = 0;
    $container = [];

    foreach ($taxonomies as $taxonomy) {
      if ($counter < $per_page) {
        $obj = new stdClass();
        $obj->id = $taxonomy->vid;
        $obj->name = $taxonomy->name;
        $obj->description = $taxonomy->description;
        $obj->numTerms = $this->getTaxonomyTermsCount($taxonomy->vid);

        $container[] = $obj;
      } 

      $counter++;
    }

    return $container;
  }

  /**
   * Get the number of terms associated to a Taxonomy
   *
   * @param $vid
   *   the vocabulary id of a Taxonomy
   */
  private function getTaxonomyTermsCount($vid) {
    return db_query("SELECT * FROM {taxonomy_term_data} WHERE vid = :vid", [':vid' => $vid])->rowCount();
  }
}


<?php

include 'BaseController.php';

class TaxonomyController extends BaseController {

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
  public function index($page = 1, $per_page = 250, $fields = NULL, $id = NULL) {
    try {
      $this->page = $page;
      $this->per_page = $per_page;
      $this->fields = $fields;

      $this->query = db_select('taxonomy_vocabulary', 'v');

      if ($id != NULL) {
        $this->query->join('skyword_entities', 'e');
        $this->query->condition('v.vid', $id);
        $this->query->condition('e.status', 1);
        $this->query->fields('v', ['vid' => 'vid', 'machine_name' => 'machine_name', 'description' => 'description']);

        return $this->buildData($this->query->execute(), FALSE);
      }

      $this->query->join('skyword_entities', 'e', 'e.bundle = v.machine_name');
      $this->query->condition('e.status', 1);
      $this->query->fields('v', ['vid' => 'vid', 'machine_name' => 'machine_name', 'description' => 'description']);
      $this->pager();

      return $this->buildData($this->query->execute());
    }
    catch (Exception $e) {
      return $this->showErrors('Unable to query taxonomy table', $e);
    }
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $terms = NULL, $page = 1, $per_page = 250, $fields = NULL) {
    try {
      $this->page = $page;
      $this->per_page = $per_page;
      $this->fields = $fields;

      $taxonomy = taxonomy_vocabulary_load($id);

      if ($terms == 'terms') {
        return $this->getTerms($taxonomy, $per_page, $fields);
      }

      $data = $this->buildData($taxonomy, FALSE);

      if ($fields != NULL) {
        parent::limitOutputByFields($fields, $data);
      }

      return $data;
    }
    catch (Exception $e) {
      return $this->showErrors('Unable to query taxonomy table', $e);
    }
  }

  /**
   * Create a Taxonomy
   * Create a Taxonomy term if $id and $terms are present
   *
   * @param $data
   *   The post data
   *   For creating a taxonomy, the post data should contain
   *   - name
   *   - description
   *   For creating a taxonomy term, the post data should contain
   *   - name
   * @param $id
   *   The identifier of the Taxonomy
   * @param $terms
   *   Identify if we are creating a taxonomy term
   */
  public function create($data, $id = NULL, $terms = NULL) {
    if (!$id && !$terms) {
      return $this->createTaxonomy($data);
    }

    if ($id && $terms && $terms == 'terms') {
      return $this->createTerm($data, $id);
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
  protected function buildData($taxonomies, $list = TRUE) {
    try {
      if ($list) {
        $data = [];

        foreach ($taxonomies as $taxonomy) {
          $obj = new stdClass();
          $obj->id = $taxonomy->vid;
          $obj->name = $taxonomy->machine_name;
          $obj->description = $taxonomy->description;
          $obj->numTerms = $this->getTaxonomyTermsCount($taxonomy->vid);

          $data[] = $obj;
        }

        return $data;
      }
      else {
        $obj = new stdClass();
        $obj->id = $taxonomies->vid;
        $obj->name = $taxonomies->machine_name;
        $obj->description = $taxonomies->description;
        $obj->numTerms = $this->getTaxonomyTermsCount($taxonomies->vid);

        return $obj;
      }
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Build the data for taxonomy terms
   */
  private function buildTermsData($terms) {
    try {
      $data = [];

      foreach ($terms as $term) {
        $obj = new stdClass();
        $obj->id = $term->tid;
        $obj->value = $term->name;

        $data[] = $obj;
      }

      return $data;
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
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

  /**
   * Get the taxonomy terms based on taxonomy vid
   *
   * @param $taxonomy
   *   the taxonomy object
   * @param $per_page
   *   the number of items to display per page
   * @param $fields
   *   a comma separated name of fields
   */
  private function getTerms($taxonomy, $per_page, $fields) {
    try {
      $this->query = db_select('taxonomy_term_data', 'tt');
      $this->query->condition('tt.vid', $taxonomy->vid);
      $this->query->fields('tt', ['tid' => 'tid', 'name' => 'name']);
      $this->query->orderBy('tt.tid', 'ASC');
      $this->pager();

      $terms = $this->query->execute()->fetchAll();

      $data = $this->buildTermsData($terms);

      if ($fields != NULL) {
        parent::limitOutputByFields($fields, $data);
      }

      return $data;
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Create a taxonomy term based on taxonomy id
   *
   * @param $data
   *   the post request payload object
   * @param $id
   *   the unique identifier of a taxonomy
   */
  private function createTerm($data, $id) {
    try {
      $obj = new stdClass();
      $obj->name = $data['name'];
      $obj->vid = $id;
      taxonomy_term_save($obj);

      return (object)[
        'value' => $obj->name,
        'parent' => $obj->vid,
      ];
    }
    catch(Exception $e) {
      return $this->showErrors('Unable to create a taxonomy term ' . $data['name'], $e);
    }
  }

  /**
   * Create a taxonomy term
   *
   * @param $data
   *   the post request payload object
   */
  private function createTaxonomy($data) {
    $machineName = str_replace(' ', '_', $data['name']);

    $taxonomy = new stdClass();
    $taxonomy->name = $data['name'];
    $taxonomy->machine_name = strtolower($machineName);
    $taxonomy->description = t($data['description']);
    $taxonomy->module = 'taxonomy';

    try {
      taxonomy_vocabulary_save($taxonomy);

      return (object)[
        'name' => $taxonomy->name,
        'description' => $taxonomy->description,
      ];
    }
    catch (Exception $e) {
      return $this->showErrors('Unable to create a Taxonomy named ' . $data['name'], $e);
    }
  }
}


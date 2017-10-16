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
      $this->query->join('skyword_entities', 'e', 'e.bundle = v.machine_name');
      $this->query->condition('e.status', 1);

      if ($id !== NULL) {
        $this->query->condition('v.vid', $id);
      }

      $this->query->fields('v', ['vid' => 'vid', 'machine_name' => 'machine_name', 'description' => 'description']);

      $this->pager();
      $result = $this->query->execute();

      return $this->buildData($this->query->execute());
    }
    catch (Exception $e) {
      $errorMessage = $e->getMessage();

      if ($errorMessage) {
        return services_error(t($errorMessage), 500);
      }

      return services_error(t('Unable to query taxonomy table.'), 500);
    }
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $terms = NULL, $page = 1, $per_page = 250, $fields = NULL) {
    try {
      $taxonomy = taxonomy_vocabulary_load($id);

      if (!$terms) {
        $data = $this->buildData($taxonomy, FALSE);

        if ($fields != NULL) {
          parent::limitOutputByFields($fields, $data);
        }

        return $data;
      }

      if ($terms == 'terms') {
        $terms = entity_load('taxonomy_term', FALSE, ['vid' => $taxonomy->vid]);
        $data = NULL;

        if (count($terms) < $per_page) {
          $data = $this->buildTermsData($terms);
        }
        else {
          $data = $this->buildDataWithCount($per_page, $terms, 'terms');
        }

        if ($fields != NULL) {
          parent::limitOutputByFields($fields, $data);
        }

        return $data;
      }
    }
    catch (Exception $e) {
      $errorMessage = $e->getMessage();

      if ($errorMessage) {
        return services_error(t($errorMessage), 500);
      }

      return services_error(t('Unable to query taxonomy table'), 500);
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
      $explodeName = explode(' ', $data['name']);
      $machineName = implode('_', $explodeName);

      $taxonomy = new stdClass();
      $taxonomy->name = $data['name'];
      $taxonomy->machine_name = $machineName;
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
        return services_error(t('Unable to create a Taxonomy named ' . $data['name']), 500);
      }
    }

    if ($id && $terms && $terms == 'terms') {
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
        return services_error(t('Unable to crete a taxonomy term ' . $data['name']), 500);
      }
    }
    else {
      return services_error(t('Unable to crete a taxonomy term ' . $data['name']), 500);
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
   * Build the data per count
   *
   * @param $per_page
   *   the number of items to be included in a page
   * @param $taxonomies
   *   an array of taxonomies
   */
  private function buildDataWithCount($per_page, $data, $type) {
    $counter = 0;
    $container = [];

    switch ($type) {
      case 'taxonomies':
        foreach ($data as $taxonomy) {
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

        break;

      case 'terms':
        foreach ($data as $term) {
          if ($counter < $per_page) {
            $obj = new stdClass();
            $obj->id = $term->tid;
            $obj->value = $term->name;

            $container[] = $obj;
          }

          $counter++;
        }

        break;
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

<?php

include 'BaseController.php';

class PostsController extends BaseController {

  function __construct() {}

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
    try {
      return $this->getPosts();
    }
    catch (Exception $e) {
      return services_error(t('Unable to query posts table.'), 500);
    }
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $fields = NULL) {}

  /**
   * Create a Taxonomy
   */
  public function create($name, $description) {}

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
  protected function buildData($taxonomies) {}

  private function getPosts() {
    $ln = LANGUAGE_NONE;

    $contentTypes = parent::getEnabledContentTypes();

    $query = db_select('node', 'n');
    $query->condition('n.type', $contentTypes, 'IN');
    $query->fields('n', ['nid']);
    $result = $query->execute()->fetchAll();

    $data = [];
    $post = new stdClass();
    $post->elements = [];

    foreach ($result as $row) {
      $node = node_load($row->nid);

      $element= new stdClass();
      $element->id = $node->nid;
      $element->type = $node->type;
      $element->title = $node->title;
      $element->url = url('node/' . $node->uid, ['absolute' => TRUE]);
      $element->created = format_date($node->created);

      $author = user_load($node->uid);

      $element->author = new stdClass();
      $element->author->id = $author->uid;
      $element->author->byline = '';

      $fields = field_info_instances('node', $node->type);
      $element->fields = [];

      foreach ($fields as $id => $field) {
        $fieldName = $field['field_name'];
        $fieldLabel = $field['label'];
        $fieldModule = $field['widget']['module'];

        $fieldData = new stdClass();
        $fieldData->name = $fieldLabel;

        if ($fieldModule == 'image') {
          $fieldData->value = isset($node->{$field['field_name']}[$ln])
          ? file_create_url($node->{$field['field_name']}[$ln][0]['uri'])
          : '';
        }
        elseif ($fieldModule == 'taxonomy') {
          if (isset($node->{$fieldName}[$ln])) {
            $term = taxonomy_term_load($node->{$fieldName}[$ln][0]['tid']);
            $fieldData->value = $term->name;
          }
          else {
            $fieldData->value = '';
          }
        }
        else {
          $fieldData->value = isset($node->{$fieldName}[$ln])
          ? $node->{$fieldName}[$ln][0]['value']
          : '';
          $fieldData->type = $fieldModule;
        }

        $element->fields[] = $fieldData;
      }

      $post->elements[] = $element;
      $data[] = $post;
    }

    return $data;
  }
}


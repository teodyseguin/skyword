<?php

include_once 'BaseController.php';
include_once 'AuthorController.php';

class PostsController extends BaseController {
   private $authorEnabledFields;

   /**
    * Initialize some values
    */
   public function __construct() {
     $author = new AuthorController();
     $this->authorEnabledFields = $author->getEnabledFields();
   }

  /**
   * Returns a list of Posts
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
   * Retrieve a specific Post
   */
  public function retrieve($id, $fields = NULL) {
    try {
      return $this->getPosts($id);
    }
    catch (Exception $e) {
      return services_error(t('Unable to query posts table.'), 500);
    }
  }

  /**
   * Create a Post
   */
  public function create($data) {
    $this->validatePostData($data);

    try {
      return 'test';  
    }
    catch (Exception $e) {
      return services_error(t('Cannot create a post.'), 500);
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
   * Get the posts from the node table
   */
  private function getPostsTypes($id = NULL) {
    $contentTypes = parent::getEnabledContentTypes();

    $query = db_select('node', 'n');
    $query->condition('n.type', $contentTypes, 'IN');
    $query->condition('n.status', 1);

    if ($id != NULL) {
      $query->condition('n.nid', $id);
    }

    $query->fields('n', ['nid']);
    $result = $query->execute();

    return (object)[
      'result' => $result->fetchAll(),
      'count' => $result->rowCount(),
    ];
  }

  /**
   * Build the field info object
   *
   * @param $fields
   *   an array of field info with keys and values
   * @param $node
   *   the node object
   * @param &$element
   *   a reference to the $element object
   */
  private function buildFieldsData($node, &$element) {
    $ln = LANGUAGE_NONE;
    $fields = field_info_instances('node', $node->type);
    $element->fields = [];

    foreach ($fields as $id => $field) {
      $fieldID = $field['id'];
      $fieldName = $field['field_name'];
      $fieldLabel = $field['label'];
      $fieldModule = $field['widget']['module'];

      $fieldData = new stdClass();
      $fieldData->id = $fieldID;
      $fieldData->name = $fieldLabel;
      $fieldData->type = $fieldModule;

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
      }

      $element->fields[] = $fieldData;
    }
  }

  /**
   * Build the author object's data
   *
   * @param $node
   *   the loaded node object
   * @param &$element
   *   a reference to the element object
   */
  private function buildAuthorData($node, &$element) {
    $ln = LANGUAGE_NONE;
    $author = user_load($node->uid);

    $element->author = new stdClass();
    $element->author->id = $author->uid;

    $byline = [];

    foreach ($this->authorEnabledFields as $machineName => $field) {
      if ($field['mapto'] == 'firstName' || $field['mapto'] == 'lastName') {
        $byline[] = isset($author->{$machineName}[$ln])
        ? $author->{$machineName}[$ln][0]['value'] : '';
      }
    }

    $element->author->byline = implode(' ', $byline);
  }

  /**
   * Build the posts data object
   *
   * @param $resultTypes
   *   an array of content types enabled for skyword use.
   *
   * @return $data
   *   an array of post objects
   */
  private function buildPosts($resultTypes) {
    $posts = new stdClass();
    $posts->elements = [];

    $posts->total = $resultTypes->count;
    $posts->page = 1;

    foreach ($resultTypes->result as $row) {
      $node = node_load($row->nid);

      $element = new stdClass();
      $element->id = $node->nid;
      $element->type = $node->type;
      $element->title = $node->title;
      $element->url = url('node/' . $node->uid, ['absolute' => TRUE]);
      $element->created = format_date($node->created);

      $this->buildAuthorData($node, $element);
      $this->buildFieldsData($node, $element);

      $posts->elements[] = $element;
    }

    return $posts;
  }

  /**
   * Get the possible posts contents for the API
   */
  private function getPosts($id = NULL) {
    $resultTypes = $this->getPostsTypes($id);
    return $this->buildPosts($resultTypes);
  }

  private function validatePostData($data) {
    $field_match = 0;

    if (!isset($data['type'])) return FALSE;
    if (!isset($data['author'])) return FALSE;

    $fields = field_info_instances('node', $data['type']);

    object_log('loaded fields', $fields);

    foreach ($data['fields'] as $key => $field) {
      foreach ($fields as $machineName => $f) {
        if ($f['label'] == $field['name']) $field_match++;
      }      
    }

    if ($field_match == 0) return FALSE;
  }
}


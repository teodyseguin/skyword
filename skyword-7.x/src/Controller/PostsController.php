<?php

include_once 'BaseController.php';
include_once 'AuthorController.php';

class PostsController extends BaseController {
   private $authorEnabledFields;
   private $dataFields;
   private $data;

   /**
    * Initialize some values
    */
   public function __construct($data = NULL) {
     $author = new AuthorController();
     $this->authorEnabledFields = $author->getEnabledFields();

     if ($data != NULL) {
       $this->data = $data;
       $this->dataFields = field_info_instances('node', $data['type']);
     }
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
  public function create() {
    $test = $this->validatePostData();

    if (!$test)  return services_error(t('Required fields are missing.'), 500);

    try {
      return $this->buildPostData();
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

  /**
   * Validate the post request data if it has the minimal
   * required fields for creating a certain type of node
   */
  private function validatePostData() {
    $field_match = 0;

    if (!isset($this->data['type'])) return FALSE;
    if (!isset($this->data['author'])) return FALSE;

    // check if the submitted fields are within the
    // structure of the given node type ($this->data['type'])
    foreach ($this->data['fields'] as $key => $field) {
      foreach ($this->dataFields as $machineName => $f) {
        if ($f['label'] == $field['name']) $field_match++;
      }      
    }

    if ($field_match == 0) return FALSE;
  }

  private function buildPostData() {
    $post = new stdClass();
    $post->title = $this->data['title'];
    $post->type = $this->data['type'];
    node_object_prepare($post);
    $post->language = LANGUAGE_NONE;
    $post->uid = $this->data['id'];
    $post->status = 1;
    $post->promote = 0;
    $post->comment = 1;
    $post->created = time();

    // foreach ($this->dataFields as $key => $field) {
    // }
  }
}


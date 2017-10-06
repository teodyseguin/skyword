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
    $test = $this->validatePostData($this->data, $this->dataFields);

    if (!$test) return services_error(t('Required fields are missing.'), 500);

    try {
      return $this->buildPostData($this->data, $this->dataFields);
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
   * Delete a Post based on id
   *
   * @param $id
   *   the id of the post to delete
   */
  public function delete($id) {
    try {
      $deleted = db_delete('node');
      $deleted->condition('nid', $id);
      $deleted->execute();

      return $id;
    }
    catch (Exception $e) {
      return services_error(t('Cannot delete a post'), 500);
    }
  }

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
   *
   * @param $data
   *   the post request data object
   */
  private function validatePostData($data, $dataFields) {
    $field_match = 0;

    if (empty($data['type'])) return FALSE;
    if (empty($data['author'])) return FALSE;
    if (empty($data['title'])) return FALSE;

    // check if the submitted fields are within the
    // structure of the given node type ($this->data['type'])
    foreach ($data['fields'] as $key => $field) {
      foreach ($dataFields as $machineName => $f) {
        if ($f['label'] == $field['name']) $field_match++;
      }      
    }

    if ($field_match == 0) return FALSE;

    return TRUE;
  }

  /**
   * Build the post node data
   *
   * @parm $data
   *   the post request data object
   */
  private function buildPostData($data, $dataFields) {
    $ln = LANGUAGE_NONE;
    $post = new stdClass();
    $post->title = $data['title'];
    $post->type = $data['type'];
    node_object_prepare($post);
    $post->language = LANGUAGE_NONE;
    $post->uid = $data['author'];
    $post->status = 1;
    $post->promote = 0;
    $post->created = time();

    foreach ($data['fields'] as $key => $field) {
      foreach ($dataFields as $machineName => $f) {
        if ($field['type'] != 'image') {
          if ($f['label'] == $field['name']) {
            $post->{$machineName}[$ln][0]['value'] = $field['value'];
          }
          elseif ($field['type'] == 'image') {
            $image = file_get_contents($field['value']);
            $file = file_save_data($image, NULL, FILE_EXISTS_REPLACE);
            $post->{$machineName}[$ln][0]['fid'] = $file->fid;
          }
        }
      }
    }

    node_save($post);

    return $data;
  }
}


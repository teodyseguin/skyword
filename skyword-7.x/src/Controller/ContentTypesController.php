<?php

include 'BaseController.php';

class ContentTypesController extends BaseController {

  public function __construct() {}

  /**
   * Get a list of content types enabled for skyword
   *
   * @param $page
   *   the default number of page to return
   * @param $per_page
   *   the number of items per page
   * @param $fields
   *   the fields to include from the result
   */
  public function index($page = 1, $per_page = 250, $fields = NULL) {
    try {
      return $this->getTypes();
    }
    catch (Exception $e) {
      return services_error(t('Cannot query content types table.'), 500);
    }
  }

  /**
   * Get a specific content type
   *
   * @param $type
   *   the type of content type to retrieve
   * @param $fields
   *   the fields to include from the result
   */
  public function retrieve($type, $fields) {
    try {
      return $this->getTypes($type);
    }
    catch (Exception $e) {
      return services_error(t('Cannot query content types table.'), 500);
    }
  }

  public function create($data) {
    try {
      watchdog('skyword', '<pre>'. print_r($data, true) . '</pre>');

        if (!$this->valid($data)) {

        }

      // use get_t() to get the name of our localization function for translation
      // during install, when t() is not available.
      $t = get_t();
      // Define the node type.
      $skyword_content_type = array(
        'type' => $data['id'],
        'name' => $data['name'],
        'base' => 'node_content',
        'description' => $data['description'],
        'body_label' => ''
      );

      // Complete the node type definition by setting any defaults not explicitly
      // declared above.
      // http://api.drupal.org/api/function/node_type_set_defaults/7
      $content_type = node_type_set_defaults($skyword_content_type);
      node_type_save($content_type);

      // Next we want to programmatically add our fields.
      if ($data['fields']) {
        foreach($data['fields'] as $field) {
          if ($field['id'] !== 'title' && !field_info_field($field['id'])) {
            switch($field['datatype']) {
              case 'text':
                // Create the field base.
                $field = array(
                  'field_name' => $field['id'],
                  'type' => 'text',
                );
                field_create_field($field);

                // Create the field instance on the bundle.
                $instance = array(
                  'field_name' => $field['id'],
                  'entity_type' => 'node',
                  'label' => 'My Field Name',
                  'bundle' => $data['id'],
                  // If you don't set the "required" property then the field wont be required by default.
                  'required' => $field['required'],
                  'widget' => array(
                    'type' => 'textfield',
                  ),
                );
                field_create_instance($instance);
              break;
              case 'richtext':
                // Create the field base.
                $field = array(
                  'field_name' => $field['id'],
                  'type' => 'text_long',
                );
                field_create_field($field);

                // Create the field instance on the bundle.
                $instance = array(
                  'field_name' => $field['id'],
                  'entity_type' => 'node',
                  'label' => 'My Field Name',
                  'bundle' => $data['id'],
                  // If you don't set the "required" property then the field wont be required by default.
                  'required' => $field['required'],
                  'widget' => array(
                    'type' => 'text_textarea',
                  ),
                  'format' => 'filter_html',
                );
                field_create_instance($instance);
              break;
            }
          }
        }
      }

      return [];
    }
    catch (Exception $e) {
      return services_error(t('Cannot create a content type.'), 500);
    }
  }

  /**
  * Validation checks to prevent us from breaking Drupal!
  */
  private function valid($data) {

    return true;
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
  private function buildFieldsData($type, &$element) {
    $fields = field_info_instances('node', $type);
    $element->fields = [];

    foreach ($fields as $id => $field) {
      $fieldID = $field['id'];
      $fieldName = $field['field_name'];
      $fieldLabel = $field['label'];
      $fieldModule = $field['widget']['module'];
      $fieldWidgetType = $field['widget']['type'];
      $fieldRequired = $field['required'] ? TRUE : FALSE;
      $fieldDescription = $field['description'];

      $fieldData = new stdClass();
      $fieldData->id = $fieldID;
      $fieldData->name = $fieldLabel;
      $fieldData->help = $fieldDescription;
      $fieldData->required = $fieldRequired;
      $fieldData->dataType = $fieldModule;
      $fieldData->{'ui-type'} = $fieldWidgetType;

      $element->fields[] = $fieldData;
    }
  }

  /**
   * Helper method to retrieve the types from the database
   *
   * @param $type
   *   default to NULL.
   *   if specified, will retrieve that specific type
   */
  private function getTypes($type = NULL) {
    $types = parent::getEnabledContentTypes();

    $this->query = db_select('node_type', 'nt');
    $this->query->condition('nt.type', $types, 'IN');

    if ($type != NULL) {
      $this->query->condition('nt.type', $type);
      $this->query->fields('nt', ['type', 'name', 'description']); 
      $this->pager();

      $obj = $this->query->execute()->fetchObject();

      $this->buildFieldsData($type, $obj);

      return $obj;
    }

    $this->query->fields('nt', ['type', 'name', 'description']);
    $this->pager();

    return $this->query->execute()->fetchAll();
  }

}

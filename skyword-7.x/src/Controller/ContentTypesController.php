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
      return $this->createType($data); 
    }
    catch (Exception $e) {
      return services_error(t('Cannot create a content type.'), 500);
    }
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

    $query = db_select('node_type', 'nt');
    $query->condition('nt.type', $types, 'IN');

    if ($type != NULL) {
      $query->condition('nt.type', $type);
      $query->fields('nt', ['type', 'name', 'description']); 

      $obj = $query->execute()->fetchObject();

      $this->buildFieldsData($type, $obj);

      return $obj;
    }

    $query->fields('nt', ['type', 'name', 'description']);

    return $query->execute()->fetchAll();
  }

  private function createType($data) {
    /*$form_state = array();
    $form_state['values']['type'] = $data['name'];
    $form_state['values']['name'] = $data['name'];
    $form_state['values']['description'] = $data['description'];
    $form_state['values']['title_label'] = 'Title';
    $form_state['values']['node_preview'] = 1;
    $form_state['values']['comment'] = 1;
    $form_state['op'] = 'Save content type';
    drupal_form_submit('node_type_form', $form_state);*/

    return new stdClass();
  }
}


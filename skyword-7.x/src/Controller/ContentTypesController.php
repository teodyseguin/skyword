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
    $this->page = $page;
    $this->per_page = $per_page;
    $this->fields = $fields;

    try {
      return $this->getTypes();
    }
    catch (Exception $e) {
      $errorMessage = $e->getMessage();

      if ($errorMessage) {
        return services_error(t($errorMessage), 500);
      }

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
      $errorMessage = $e->getMessage();

      if ($errorMessage) {
        return services_error(t($errorMessage), 500);
      } 

      return services_error(t('Cannot query content types table.'), 500);
    }
  }

  public function create($data) {
    try {
      // use get_t() to get the name of our localization function for translation
      // during install, when t() is not available.
      $t = get_t();

      $this->removeContentTypeNameSpaces($data);

      // Define the node type.
      $skyword_content_type = array(
        'type' => $data['name'],
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
              case 'text field':
                $this->createTextField($field, $data);
                break;

              case 'text area':
                $this->createTextAreaField($field, $data, $content_type);
                break;

              case 'image':
                $this->createImageField($field, $data);
                break;

              case 'boolean':
                $this->createBooleanField($field, $data);
                break;

              case 'single select':
                $this->createSingleSelectField($field, $data);
                break;

              case 'multi select':
                $this->createMultiSelectField($field, $data);
                break;

              case 'date':
                $this->createDateField($field, $data);
                break;

              case 'datetime':
                $this->createDatetimeField($field, $data);
                break;
            }
          }
        }
      }

      return $this->getTypes($data['name']);
    }
    catch (Exception $e) {
      $errorMessage = $e->getMessage();

      if ($errorMessage) {
        return services_error(t($errorMessage), 500);
      }

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
      $fieldData->datatype = $fieldModule;
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

    if ($type != NULL) {
      $this->query->condition('nt.type', $type);
      $this->query->fields('nt', ['type', 'name', 'description']);
      $this->pager();

      $obj = $this->query->execute()->fetchObject();

      $this->buildFieldsData($type, $obj);

      return $obj;
    }

    $this->query->condition('nt.type', $types, 'IN');
    $this->query->fields('nt', ['type', 'name', 'description']);
    $this->pager();

    $obj = new stdClass();
    $obj->elements = $this->query->execute()->fetchAll();
    $obj->total = $this->query->execute()->rowCount();
    $obj->page = $this->page ? $this->page : 1;

    return $obj;
  }

  private function removeContentTypeNameSpaces(&$data) {
    if (empty($data['name'])) return;
    $data['name'] = strtolower(str_replace(' ', '_', $data['name']));
  }

  private function removeFieldNameSpaces(&$field) {
    if (empty($field['name'])) return;
    return strtolower(str_replace(' ', '_', $field['name']));
  }

  private function createTextField($field, $data) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      // Create the field base.
      $baseField = array(
        'field_name' => $fieldMachineName,
        'type' => 'text',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        'bundle' => $data['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'widget' => array(
          'type' => 'textfield',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createTextAreaField($field, $data, $contentType = NULL) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      if ($field['name'] == 'Body' && $contentType != NULL) {
        node_add_body_field($contentType);
      }
      else {
        // Create the field base.
        $baseField = array(
          'field_name' => $fieldMachineName,
          'module' => 'text',
          'translatable' => 0,
          'type' => 'text_long',
        );

        if ($field['summary'] == 'true') $baseField['type'] = 'text_with_summary';

        field_create_field($baseField);

        // Create the field instance on the bundle.
        $instance = array(
          'description' => $field['help'],
          'display' => array(
            'default' => array(
              'label' => 'above',
              'module' => 'text',
              'settings' => array(),
              'type' => 'text_default',
            ),
          ),
          'field_name' => $fieldMachineName,
          'entity_type' => 'node',
          'label' => $field['name'],
          'bundle' => $data['name'],
          // If you don't set the "required" property then the field wont be required by default.
          'required' => $field['required'],
          'widget' => array(
            'type' => 'text_textarea',
          ),
          'format' => 'filter_html',
        );

        field_create_instance($instance);
      }
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createImageField($field, $data) {
    try {
      $fieldMachineName = $this->removeFieldNameSpaces($field);

      // Create the field base.
      $baseField = array(
        'field_name' => $fieldMachineName,
        'type' => 'image',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'description' => $field['help'],
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        'bundle' => $data['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'widget' => array(
          'active' => 1,
          'module' => 'image',
          'settings' => array(
            'preview_image_style' => 'thumbnail',
            'progress_indicator' => 'throbber',
          ),
          'type' => 'image_image',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createBooleanField($field, $data) {
    try {
      $fieldMachineName = $this->removeFieldNameSpaces($field);

      // Create the field base.
      $baseField = array(
        'field_name' => $fieldMachineName,
        'module' => 'list',
        'settings' => array(
          'allowed_values' => array(0 => 'false', 1 => 'true'),
          'allowed_values_function' => '',
        ),
        'translatable' => 0,
        'type' => 'list_boolean',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'bundle' => $data['name'],
        'default_value' => NULL,
        'description' => $field['help'],
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'widget' => array(
          'active' => 1,
          'module' => 'options',
          'type' => 'options_buttons',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createSingleSelectField($field, $data) {
    try {
      $fieldMachineName = $this->removeFieldNameSpaces($field);

      // Create the field base.
      $baseField = array(
        'cardinality' => 1,
        'field_name' => $fieldMachineName,
        'indexes' => array(
          'value' => array(0 => 'value'),
        ),
        'module' => 'list',
        'settings' => array(
          'allowed_values' => array(),
          'allowed_values_function' => '',
        ),
        'translatable' => 0,
        'type' => 'list_text',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'bundle' => $data['name'],
        'default_value' => NULL,
        'description' => $field['help'],
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'widget' => array(
          'active' => 1,
          'module' => 'options',
          'settings' => array(),
          'type' => 'options_select',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createMultiSelectField($field, $data) {
    try {
      $fieldMachineName = $this->removeFieldNameSpaces($field);

      // Create the field base.
      $baseField = array(
        'cardinality' => -1,
        'field_name' => $fieldMachineName,
        'indexes' => array(
          'value' => array(0 => 'value'),
        ),
        'module' => 'list',
        'settings' => array(
          'allowed_values' => array(),
          'allowed_values_function' => '',
        ),
        'translatable' => 0,
        'type' => 'list_text',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'bundle' => $data['name'],
        'default_value' => NULL,
        'description' => $field['help'],
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'widget' => array(
          'active' => 1,
          'module' => 'options',
          'settings' => array(),
          'type' => 'options_select',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createDateField($field, $data) {
    if (!module_exists('date')) throw new Exception('Date module is not enabled');

    try {
      $fieldMachineName = $this->removeFieldNameSpaces($field);

      // Create the field base.
      $baseField = array(
        'cardinality' => 1,
        'field_name' => $fieldMachineName,
        'indexes' => array(),
        'module' => 'date',
        'settings' => array(
          'cache_count' => 4,
          'cache_enabled' => 0,
          'granularity' => array(
            'day' => 'day',
            'hour' => 0,
            'minute' => 0,
            'month' => 'month',
            'second' => 0,
            'year' => 'year',
          ),
          'timezone_db' => '',
          'todate' => '',
          'tz_handling' => 'none',
        ),
        'translatable' => 0,
        'type' => 'datetime',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'bundle' => $data['name'],
        'default_value' => NULL,
        'description' => $field['help'],
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'widget' => array(
          'active' => 1,
          'module' => 'date',
          'settings' => array(
            'increment' => 15,
            'input_format' => 'm/d/Y - H:i:s',
            'input_format_custom' => '',
            'label_position' => 'above',
            'no_fieldset' => 0,
            'text_parts' => array(),
            'year_range' => '-3:+3',
          ),
          'type' => 'date_select',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function createDatetimeField($field, $data) {
    if (!module_exists('date')) throw new Exception('Date module is not enabled');

    try {
      $fieldMachineName = $this->removeFieldNameSpaces($field);

      // Create the field base.
      $baseField = array(
        'cardinality' => 1,
        'field_name' => $fieldMachineName,
        'indexes' => array(),
        'module' => 'date',
        'settings' => array(
          'cache_count' => 4,
          'cache_enabled' => 0,
          'granularity' => array(
            'day' => 'day',
            'hour' => 'hour',
            'minute' => 'minute',
            'month' => 'month',
            'second' => 0,
            'year' => 'year',
          ),
          'timezone_db' => 'UTC',
          'todate' => '',
          'tz_handling' => 'site',
        ),
        'translatable' => 0,
        'type' => 'datetime',
      );

      field_create_field($baseField);

      // Create the field instance on the bundle.
      $instance = array(
        'bundle' => $data['name'],
        'default_value' => NULL,
        'description' => $field['help'],
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'label' => $field['name'],
        // If you don't set the "required" property then the field wont be required by default.
        'required' => $field['required'],
        'settings' => array(
          'default_value' => 'now',
          'default_value2' => 'same',
          'default_value_code' => '',
          'default_value_code2' => '',
          'user_register_form' => FALSE,
        ),
        'widget' => array(
          'active' => 1,
          'module' => 'date',
          'settings' => array(
            'increment' => 15,
            'input_format' => 'm/d/Y - H:i:s',
            'input_format_custom' => '',
            'label_position' => 'above',
            'no_fieldset' => 0,
            'text_parts' => array(),
            'year_range' => '-3:+3',
          ),
          'type' => 'date_select',
        ),
      );

      field_create_instance($instance);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }
}

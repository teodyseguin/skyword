<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\skyword\Plugin\rest\resource\SkywordCommonTools;
use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "skyword_content_type_rest_resource",
 *   label = @Translation("Skyword content type rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/content-type",
 *     "https://www.drupal.org/link-relations/create" = "/skyword/v1/content-type"
 *   }
 * )
 */
class SkywordContentTypeRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new SkywordAuthorsRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('skyword'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of content types.
   */
  public function get() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $types = SkywordCommonTools::getTypes();

      return new ResourceResponse($types);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Responds to POST requests.
   *
   * Creates a Content Type.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $fieldMachineName = $this->removeContentTypeNameSpaces($data);
      $fieldName = ucwords($data['name']);
      $fieldDescription = $data['description'];

      $type = NodeType::create([
        'type' => $fieldMachineName,
        'name' => $fieldName,
        'description' => $fieldDescription,
        'revision' => FALSE,
      ]);

      $type->save();
      node_add_body_field($type);

      $this->createFields($data, $type);

      return new ResourceResponse($data);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Helper to remove spaces from the content type name.
   *
   * @param array $data
   *   The post request payload data.
   */
  private function removeContentTypeNameSpaces(array $data) {
    if (empty($data['name'])) {
      return;
    }

    return strtolower(str_replace(' ', '_', $data['name']));
  }

  /**
   * Helper method to decide what type of field needs to be created.
   *
   * @param array $data
   *   The submitted data from post request.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createFields(array $data, $type) {
    if (!$data['fields']) {
      return;
    }

    foreach ($data['fields'] as $field) {
      switch ($field['datatype']) {
        case 'text field':
          $this->createTextField($field, $type);
          break;

        case 'text area':
          $this->createTextAreaField($field, $type);
          break;

        case 'image':
          $this->createImageField($field, $type);
          break;

        case 'boolean':
          $this->createBooleanField($field, $type);
          break;

        case 'single select':
          $this->createSingleSelectField($field, $type);
          break;

        case 'multi select':
          $this->createMultiSelectField($field, $type);
          break;

        case 'date':
          $this->createDateField($field, $type);
          break;

        case 'datetime':
          $this->createDatetimeField($field, $type);
          break;
      }
    }
  }

  /**
   * Create a Text Field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createTextField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [],
        'type' => 'string',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'text_textfield',
        ])->save();

      entity_get_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'text_default',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Create a Text area field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createTextAreaField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [],
        'type' => 'string_long',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'text_textarea',
        ])->save();

      entity_get_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'text_default',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Creates an Image field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createImageField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [
          'file_directory' => '[date:custom:Y]-[date:custom:m]',
          'file_extensions' => 'png gif jpg jpeg',
          'alt_field' => TRUE,
          'title_field' => TRUE,
        ],
        'type' => 'image',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'image_image',
        ])->save();

      entity_get_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'image',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Creates Boolean field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createBooleanField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [
          'on_label' => 'On',
          'off_label' => 'Off',
        ],
        'type' => 'boolean',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'options_buttons',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Creates Date field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createDateField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [
          'datetime_type' => 'date',
        ],
        'type' => 'datetime',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'datetime_default',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Creates Datetime field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createDatetimeField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [
          'datetime_type' => 'datetime',
        ],
        'type' => 'datetime',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'datetime_default',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Creates Single select field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createSingleSelectField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [
          'allowed_values' => [],
        ],
        'type' => 'list_string',
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'options_select',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Creates Multi select field.
   *
   * @param array $field
   *   One of the properties of the post request payload.
   * @param object $type
   *   The newly created entity type bundle.
   */
  private function createMultiSelectField(array $field, $type) {
    $fieldMachineName = $this->removeFieldNameSpaces($field);

    try {
      FieldStorageConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
        'settings' => [
          'allowed_values' => [],
        ],
        'type' => 'list_string',
        'cardinality' => -1,
      ])->save();

      FieldConfig::create([
        'field_name' => $fieldMachineName,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'label' => $field['name'],
      ])->save();

      entity_get_form_display('node', $type->id(), 'default')
        ->setComponent($fieldMachineName, [
          'type' => 'options_select',
        ])->save();
    }
    catch (Exception $e) {
      throw new Exception("Cannot create $fieldMachineName");
    }
  }

  /**
   * Helper to remove spaces from a field's name.
   *
   * @param array $field
   *   The field definition object from the post payload.
   */
  private function removeFieldNameSpaces(array &$field) {
    if (empty($field['name'])) {
      return;
    }

    return strtolower(str_replace(' ', '_', $field['name']));
  }

}

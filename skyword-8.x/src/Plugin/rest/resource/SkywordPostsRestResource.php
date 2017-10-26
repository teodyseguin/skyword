<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\skyword\Plugin\rest\resource\SkywordCommonTools;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "skyword_posts_rest_resource",
 *   label = @Translation("Skyword posts rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/posts",
 *     "https://www.drupal.org/link-relations/create" = "/skyword/v1/posts"
 *   }
 * )
 */
class SkywordPostsRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  private $fieldDefinitions;

  /**
   * Constructs a new SkywordPostsRestResource object.
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
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $test = $this->validatePostData($data);

    if (!$test) throw new ConflictHttpException('Required fields are missing');

    try {
      return new ResourceResponse($this->buildPostData($data));
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of Posts from the site.
   */
  public function get() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $posts = $this->getPosts();
      return new ResourceResponse($posts);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Helper to get all the posts from the site.
   *
   * @param string id
   *   The unique identifier of the node. Default to NULL.
   */
  private function getPosts() {
    $types = $this->getPostsTypes();

    return $this->buildPosts($types);
  }

  /**
   * Get all the posts type from the site.
   *
   * @param string id
   *   The unique identifier of the node. Default to NULL.
   */
  private function getPostsTypes() {
    $types = \Drupal::entityQuery('node_type')->execute();
    $result = \Drupal::entityQuery('node')
      ->condition('type', $types, 'IN')
      ->condition('status', 1)
      ->execute();

    return (object)[
      'result' => $result,
      'count' => count($result),
    ];
  }

  /**
   * Build the Posts Object.
   *
   * @param object $types
   *   An array of node types.
   */
  private function buildPosts($types) {
    global $base_url;

    $posts = [
      'elements' => [],
      'total' => $types->count,
      'page' => $_GET['page'] ? $_GET['page'] : 1,
    ];

    foreach ($types->result as $nid) {
      $node = Node::load($nid);

      $id = $node->id();
      $type = $node->bundle();
      $title = $node->getTitle();

      $url = $base_url . \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $id);
      $created = format_date($node->getCreatedTime());

      $element = [
        'id' => $id,
        'type' => $type,
        'title' => $title,
        'url' => $url,
        'created' => $created,
      ];

      $this->buildAuthorData($node, $element);
      $this->buildFieldsData($node, $element);

      $posts['elements'][] = $element;
    }

    return $posts;
  }

  /**
   * Build the Authors' data
   *
   * @param object $node
   *   The node entity object.
   * @param array &$element
   *   A passed by reference array of node elements.
   */
  private function buildAuthorData($node, array &$element) {
    $user = $node->getOwner();

    $element['author'] = [
      'id' => $node->getOwnerId(),
      'byline' => $user->field_byline->value,
    ];
  }

  /**
   * Helper to build the field definitions for the given Node type.
   *
   * @param object $node
   *   The node entity.
   * @param array &$element
   *   A passed by reference array of elements
   */
  private function buildFieldsData($node, array &$element) {
    $entityManager = \Drupal::service('entity_field.manager');
    $entityTypeId = 'node';
    $bundle = $node->bundle();

    $fieldDefinitions = $entityManager->getFieldDefinitions($entityTypeId, $bundle);

    foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
      if (!empty($fieldDefinition->getTargetBundle()) && $fieldName != 'promote') {
        $element['fields'][] = [
          'id' => $fieldDefinition->id(),
          'name' => $fieldDefinition->getLabel(),
          'type' => $fieldDefinition->getType(),
          'value' => $node->get($fieldName)->value,
        ];
      }
    }
  }

  /**
   * Validate the post request data if it has the minimal
   * required fields for creating a certain type of node
   *
   * @param $data
   *   the post request data object
   */
  private function validatePostData($data) {
    if (empty($data['type'])) return FALSE;
    if (empty($data['author'])) return FALSE;
    if (empty($data['title'])) return FALSE;

    $entityTypeId = 'node';
    $entityFieldManager = \Drupal::service('entity_field.manager');

    $this->fieldDefinitions = $entityFieldManager->getFieldDefinitions($entityTypeId, $data['type']);

    return TRUE;
  }

  /**
   * Build the Post Entity
   *
   * @param array $data
   *   The post request payload, submitted to the API
   */
  private function buildPostData(array $data) {
    try {
      $type = $data['type'];
      $author = $data['author'];
      $title = $data['title'];
      $dataFields = $data['fields'];

      $prepareEntity = [
        'type' => $type,
        'uid' => $author,
        'title' => $title,
      ];

      foreach ($dataFields as $key => $dataField) {
        foreach ($this->fieldDefinitions as $fieldName => $fieldDefinition) {
          if ($fieldDefinition->getLabel() == $dataField['name']) {
            if ($dataField['type'] == 'image') {
              $file = SkywordCommonTools::storeFile($dataField['value']);
              $prepareEntity[$fieldName] = ['target_id' => $file->id()];
            }
            else {
              $prepareEntity[$fieldName] = $dataField['value'];
            }
          }
        }
      }

      $entity = Node::create($prepareEntity);
      $entity->save();

      // if all are successful, we will just return the post payload
      // which indicates that the process of creation is a success
      return $data;
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

}


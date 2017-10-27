<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\rest\ModifiedResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "skyword_post_rest_resource",
 *   label = @Translation("Skyword post rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/posts/{postId}",
 *   }
 * )
 */
class SkywordPostRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
   * Responds to GET requests.
   *
   * Returns a list of Posts from the site.
   *
   * @param int $postId
   *   The unique identifier of a node.
   */
  public function get($postId) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $posts = $this->getPosts($postId);
      return new ResourceResponse($posts);
    }
    catch (Exception $e) {
      return new ResourceResponse("Cannot fetch the post with ID $postId", 500);
    }
  }

  /**
   * Responds to DELETE requests.
   *
   * Delete a certain post.
   *
   * @param int $postId
   *   The unique identifier of a node.
   */
  public function delete($postId) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $ids = \Drupal::entityQuery('node')->condition('nid', $postId)->execute();
      $posts = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);

      foreach ($posts as $post) {
        $post->delete();
      }

      return new ModifiedResourceResponse(NULL, 204);
    }
    catch (Exception $e) {
      return new ResourceResponse("Cannot delete the post with id $postId", 500);
    }
  }

  /**
   * Helper to get all the posts from the site.
   *
   * @param string $id
   *   The unique identifier of the node. Default to NULL.
   */
  private function getPosts($id) {
    $types = $this->getPostsTypes($id);

    return $this->buildPosts($types);
  }

  /**
   * Get all the posts type from the site.
   *
   * @param string $id
   *   The unique identifier of the node. Default to NULL.
   */
  private function getPostsTypes($id) {
    $result = \Drupal::entityQuery('node')
      ->condition('nid', $id)
      ->condition('status', 1)
      ->execute();

    return (object) [
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
   * Build the Authors' data.
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
   *   A passed by reference array of elements.
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

}

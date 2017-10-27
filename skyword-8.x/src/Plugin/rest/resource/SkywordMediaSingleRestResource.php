<?php

namespace Drupal\skyword\Plugin\rest\resource;

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
 *   id = "skyword_media_single_rest_resource",
 *   label = @Translation("Skyword media single rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/media/{mediaId}"
 *   }
 * )
 */
class SkywordMediaSingleRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new SkywordMediaRestResource object.
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
   * Returns a list of Media Entity.
   *
   * @param int $mediaId
   *   The unique Identifier of the File Entity.
   */
  public function get($mediaId) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $query = \Drupal::entityQuery('file');
      $query->condition('fid', $mediaId);
      $files = $query->execute();
      $entities = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($files);

      foreach ($entities as $entity) {
        $id = $entity->id();
        $type = $entity->getMimeType();
        $url = file_create_url($entity->getFileUri());

        $data = [
          'id' => $id,
          'type' => $type,
          'url' => $url,
        ];
      }

      return new ResourceResponse($data);
    }
    catch (Exception $e) {
      return new ResourseResponse("Cannot fetch the media with ID $mediaId", 500);
    }
  }

}

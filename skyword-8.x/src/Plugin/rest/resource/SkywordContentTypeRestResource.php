<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\skyword\Plugin\rest\resource\SkywordCommonTools;
use Drupal\user\Entity\User;
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
   * Responds to POST requests.
   *
   * Creates a Content Type.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of content types
   */
  public function get() {
    try {
      $types = SkywordCommonTools::getTypes();

      return new ResourceResponse($types);  
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }    
  }

}


<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\skyword\Plugin\rest\resource\SkywordCommonTools;
use Drupal\Component\Serialization\Json;
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
 *   id = "skyword_taxonomy_terms_rest_resource",
 *   label = @Translation("Skyword taxonomy terms rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/taxonomies/{taxonomy}/terms"
 *   }
 * )
 */
class SkywordTaxonomyTermsRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Temporary holder of our query.
   */
  private $query;

  /**
   * Temporary holder of our response.
   */
  private $response;

  /**
   * Keeper for cache max age.
   */
  private $build;

  /**
   * Constructs a new SkywordTaxonomyRestResource object.
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

    $this->build = ['#cache' => ['#max-age' => 0]];

    $this->response = (new ResourceResponse())->addCacheableDependency($this->build);    
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
   * @param string $id
   *   The unique identifier of the Taxonomy.
   */
  public function get($id) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $entities = $this->getTaxonomyTerms($id);
    $data = $this->buildData($entities);

    return $this->response->setContent(Json::encode($data));
  }

  /**
   * Get all the Taxonomies.
   *
   * @param string $id
   *   the unique identifier of the Taxonomy Vocabulary e.g. tags.
   */
  private function getTaxonomyTerms($id) {
    $this->query = \Drupal::entityQuery('taxonomy_term');
    $this->query->condition('vid', $id);

    SkywordCommonTools::pager($this->response, $this->query);

    $terms = $this->query->execute();

    return \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->loadMultiple($terms);
  }

  /**
   * Build the structure of the data to be return.
   *
   * @param array $taxonomies
   *   an array of Taxonomy entities.
   */
  private function buildData(array $taxonomies) {
    foreach ($taxonomies as $entity) {
      $data = [
        'id' => $entity->id(),
        'value' => $entity->get('name')->value,
      ];
    }

    return $data;
  }

}

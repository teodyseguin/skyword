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
 *   id = "skyword_taxonomy_rest_resource",
 *   label = @Translation("Skyword taxonomy rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/taxonomies/{taxonomy}"
 *   }
 * )
 */
class SkywordTaxonomyRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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

    try {
      $entities = $this->getTaxonomy($id);

      return new ResourceResponse($this->buildData($entities, FALSE));
    }
    catch (Exception $e) {
      return new ResourceResponse("Cannot fetch taxonomy with ID $id", 500);
    }
  }

  /**
   * Get all the Taxonomies.
   *
   * @param string $id
   *   the unique identifier of the Taxonomy Vocabulary e.g. tags.
   */
  private function getTaxonomy($id) {
    $query = \Drupal::entityQuery('taxonomy_vocabulary');
    $query->condition('vid', $id);
    $taxonomyId = $query->execute();

    return \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->loadMultiple($taxonomyId);
  }

  /**
   * Build the structure of the data to be return.
   *
   * @param array $taxonomies
   *   an array of Taxonomy entities.
   */
  private function buildData(array $taxonomies) {
    foreach ($taxonomies as $entity) {
      $id = $entity->id();
      $description = $entity->get('description');
      $numTerms = $this->getTaxonomyTermsCount($id);

      $data = [
        'id' => $id,
        'name' => $id,
        'description' => $description,
        'numTerms' => $numTerms,
      ];
    }

    return $data;
  }

  /**
   * Get the number of Taxonomy Terms via Taxonomy ID.
   *
   * @param int $id
   *   the unique identifier of the Taxonomy.
   */
  private function getTaxonomyTermsCount($id) {
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $id);
    $count = $query->count()->execute();

    return intval($count);
  }

}

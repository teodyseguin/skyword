<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\skyword\Plugin\rest\resource\SkywordCommonTools;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "skyword_taxonomies_rest_resource",
 *   label = @Translation("Skyword taxonomies rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/taxonomies",
 *     "https://www.drupal.org/link-relations/create" = "/skyword/v1/taxonomies"
 *   }
 * )
 */
class SkywordTaxonomiesRestResource extends ResourceBase {

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
   * Constructs a new SkywordTaxonomiesRestResource object.
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
   * Responds to POST requests.
   *
   * @param array $data
   *   The post request payload object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $id = str_replace(' ', '', strtolower($data['name']));
      $name = str_replace(' ', '', $data['name']);
      $description = $data['description'];

      $taxonomy = Vocabulary::create([
        'vid' => $id,
        'name' => $name,
        'description' => $description,
      ]);

      $taxonomy->save();

      return new ResourceResponse($data);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Responds to GET requests.
   */
  public function get() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $entities = $this->getTaxonomies();
    $data = $this->buildData($entities);

    return $this->response->setContent(Json::encode($data));
  }

  /**
   * Get all the Taxonomies.
   */
  private function getTaxonomies() {
    $this->query = \Drupal::entityQuery('taxonomy_vocabulary');

    SkywordCommonTools::pager($this->response, $this->query);

    $taxonomyIds = $this->query->execute();

    return \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->loadMultiple($taxonomyIds);
  }

  /**
   * Build the structure of the data to be return.
   *
   * @param array $taxonomies
   *   an array of Taxonomy entities.
   */
  private function buildData(array $taxonomies) {
    $data = [];

    foreach ($taxonomies as $entity) {
      $id = $entity->id();
      $description = $entity->get('description');
      $numTerms = $this->getTaxonomyTermsCount($id);

      $data[] = [
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

<?php

namespace Drupal\skyword\Plugin\rest\resource;

use Drupal\skyword\Plugin\rest\resource\SkywordCommonTools;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Component\Serialization\Json;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "skyword_media_rest_resource",
 *   label = @Translation("Skyword media rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/media",
 *     "https://www.drupal.org/link-relations/create" = "/skyword/v1/media"
 *   }
 * )
 */
class SkywordMediaRestResource extends ResourceBase {

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
   * Creates a File Entity based on the POST Request Payload.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $headers = $this->getHeaders();

      preg_match('/filename\=(\".*\")/', $headers['Content-Disposition'], $matches);

      $file = $this->argValue($data, 'file');
      $filename = rtrim($matches[1], '"');
      $filename = substr($filename, 1, strlen($filename));

      $this->validateFile($data);
      $destination = $this->createDestination($data);

      $fileSaved = $this->fileSave($data['file'], $destination);
      \Drupal::service('file.usage')->add($fileSaved, 'skyword', 'files', $fileSaved->id());

      $id = $fileSaved->id();
      $url = file_create_url($fileSaved->getFileUri());

      $result = ['id' => $id, 'location' => $url];

      return new ResourceResponse($result);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of Media Entity.
   */
  public function get() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $this->query = \Drupal::entityQuery('file');

      SkywordCommonTools::pager($this->response, $this->query);

      $files = $this->query->execute();
      $entities = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($files);

      foreach ($entities as $entity) {
        $id = $entity->id();
        $type = $entity->getMimeType();
        $url = file_create_url($entity->getFileUri());

        $data[] = [
          'id' => $id,
          'type' => $type,
          'url' => $url,
        ];
      }

      return $this->response->setContent(Json::encode($data));
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Used for helping with the posting data.
   */
  private function argValue($data, $field) {
    if (isset($data[$field]) && count($data) == 1 && is_array($data[$field])) {
      return $data[$field];
    }

    return $data;
  }

  /**
   * Helper to validate if the post request payload has the required array keys.
   *
   * @param array $file
   *   The post request payload submitted to the API.
   */
  private function validateFile(array $file) {
    if (!isset($file['file']) || empty($file['filename'])) {
      throw new ConflictHttpException('Missing data. The file upload cannot be completed');
    }
  }

  /**
   * Do some sanitation onf the destination and path specified.
   *
   * @param string $uri
   *   The path of the file.
   */
  private function fileCheckDestinationUri($uri) {
    $scheme = strstr($uri, '://', TRUE);
    $path = $scheme ? substr($uri, strlen("$scheme://")) : $uri;

    return "$scheme://$path";
  }

  /**
   * Helper to sanitize the destination scheme.
   *
   * @param string $scheme
   *   The scheme path.
   */
  private function sanitizeDestinationScheme($scheme) {
    $unsage = ['temporary', 'file', 'http', 'https', 'ftp'];
    $fileSystemService = \Drupal::service('file_system');

    if (!empty($scheme) && !in_array($scheme, $unsafe) && $fileSystemService->validScheme($scheme)) {
      return $scheme;
    }

    return file_default_scheme();
  }

  /**
   * Helper method to save the file.
   *
   * @param object $file
   *   A base_64 representation of a file.
   * @param string $destination
   *   The place where to store the file.
   */
  private function fileSave($file, $destination) {
    if (!$fileSaved = file_save_data(base64_decode($file), $destination)) {
      throw new ConflictHttpException('Could not write file to destination');
    }

    return $fileSaved;
  }

  /**
   * Helper method to create the destination.
   *
   * @param array $data
   *   The post request payload submitted to the API.
   */
  private function createDestination(array $data) {
    // Sanitize the file extension, name, path and scheme provided by the user.
    return empty($data['filepath'])
      ? file_default_scheme() . '://' . $data['filename']
      : $this->fileCheckDestinationUri($data['filepath']);
  }

  /**
   * Check if the function for getting the headers is present.
   */
  private function getHeaders() {
    if (function_exists('getallheaders')) {
      return getallheaders();
    }
    elseif (function_exists('apache_request_headers')) {
      return apache_request_headers();
    }
  }

}

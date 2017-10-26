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
 *   id = "skyword_authors_rest_resource",
 *   label = @Translation("Skyword authors rest resource"),
 *   uri_paths = {
 *     "canonical" = "/skyword/v1/authors",
 *     "https://www.drupal.org/link-relations/create" = "/skyword/v1/authors"
 *   }
 * )
 */
class SkywordAuthorsRestResource extends ResourceBase {

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
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $this->createNewUser($data);

      return new ResourceResponse($data);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of users/authors.
   */
  public function get() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    try {
      $query = \Drupal::entityQuery('user');
      $nids = $query->execute();
      $entities = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($nids);

      foreach ($entities as $user) {
        if ($user->id() != 0) {
          $id = $user->id();
          $mail = $user->getEmail();
          $firstName = $this->getFirstName($user);
          $lastName = $this->getLastName($user);
          $byline = $this->getByline($user);
          $icon = $this->getUserPicture($user);

          $data[] = [
            'id' => $id,
            'mail' => $mail,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'byline' => $byline,
            'icon' => $icon,
          ];
        }
      }

      return new ResourceResponse($data);
    }
    catch (Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * Helper function to retrieve user first name.
   *
   * Validate if the accessibility for each property and methods are present.
   *
   * @param object $user
   *   The User Entity.
   */
  private function getFirstName($user) {
    if (!isset($user->field_first_name)) {
      return NULL;
    }

    if (!isset($user->field_first_name->value)) {
      return NULL;
    }

    return $user->field_first_name->value;
  }

  /**
   * Helper function to retrieve user last.
   *
   * Validate if the accessibility for each property and methods are present.
   *
   * @param object $user
   *   The User Entity.
   */
  private function getLastName($user) {
    if (!isset($user->field_last_name)) {
      return NULL;
    }

    if (!isset($user->field_last_name->value)) {
      return NULL;
    }

    return $user->field_last_name->value;
  }

  /**
   * Helper function to retrieve user byline.
   *
   * Validate if the accessibility for each property and methods are present.
   *
   * @param object $user
   *   The User Entity.
   */
  private function getByline($user) {
    if (!isset($user->field_byline)) {
      return NULL;
    }

    if (!isset($user->field_byline->value)) {
      return NULL;
    }

    return $user->field_byline->value;
  }

  /**
   * Helper function to retrieve user picture.
   *
   * Validate if the accessibility for each property and methods are present.
   *
   * @param object $user
   *   The User Entity.
   */
  private function getUserPicture($user) {
    if (!isset($user->get('user_picture')->entity)) {
      return NULL;
    }

    if (NULL == $user->get('user_picture')->entity->url()) {
      return NULL;
    }

    return $user->get('user_picture')->entity->url();
  }

  /**
   * Prepare a new user object for saving.
   *
   * @param array $data
   *   The request post data.
   */
  private function createNewUser(array $data) {
    try {
      $userName = str_replace(' ', '', strtolower($data['firstName']) . strtolower($data['lastName']));
      $mail = $data['email'];
      $firstName = $data['firstName'];
      $lastName = $data['lastName'];
      $byline = $data['byline'];
      $status = 1;
      $password = rand();

      if (!empty($data['icon'])) {
        $file = SkywordCommonTools::storeFile($data['icon']);
      }

      $prepareEntity = [
        'name' => $userName,
        'pass' => $password,
        'mail' => $mail,
        'status' => $status,
        'user_picture' => ['target_id' => $file->id()],
        'field_first_name' => $firstName,
        'field_last_name' => $lastName,
        'field_byline' => $byline,
      ];

      $entity = User::create($prepareEntity);
      $entity->save();
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

}

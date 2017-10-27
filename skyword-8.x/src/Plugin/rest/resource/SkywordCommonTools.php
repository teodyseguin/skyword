<?php

namespace Drupal\skyword\Plugin\rest\resource;

/**
 * Common Tools that Skyword uses.
 */
class SkywordCommonTools {

  /**
   * Helper method to store the file.
   *
   * @param string $file
   *   The string url of the file.
   */
  public static function storeFile($file) {
    $fileContent = file_get_contents($file);
    $directory = 'public://Image';

    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    return file_save_data($fileContent, $directory . basename($file), FILE_EXISTS_REPLACE);
  }

  /**
   * Get a list of Content Types or a single one.
   *
   * @param string $id
   *   The unique identifier of the content type.
   * @param object $query
   *   A passed reference of the query object.
   * @param object $response
   *   A passed reference of the response object.
   */
  public static function getTypes($id = NULL, &$query, &$response = NULL) {
    try {
      $query = \Drupal::entityQuery('node_type');

      if ($response != NULL) {
        static::pager($response, $query);
      }

      if ($id != NULL) {
        $query->condition('type', $id);
      }

      $types = $query->execute();

      $entities = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple($types);

      $data = [
        'elements' => [],
        'total' => count($types),
        'page' => $_GET['page'] ? $_GET['page'] : 1,
      ];

      foreach ($entities as $entity) {
        $data['elements'][] = [
          'type' => $entity->id(),
          'name' => $entity->label(),
          'description' => $entity->getHelp(),
        ];
      }

      return $data;
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Modify the header response and include some header properties.
   *
   * One important thing to note is the pagination.
   */
  public static function pager(&$response, &$query) {
    $currentPage = $_GET['page'];
    $perPage = $_GET['per_page'];

    if (!$currentPage) {
      return;
    }

    if (!$perPage) {
      return;
    }

    $firstRecord = $currentPage * $perPage;
    $next = $currentPage + 1;
    $prev = $currentPage - 1;
    $total = count($query->execute());
    $last = $total % $perPage;

    $url = (isset($_SERVER['HTTPS']) ? 'https:' : 'http:') . '//' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');

    $response->headers->add(['X-TOTAL-Count' => $total]);

    $headerLink = [];

    if ($next < $last) {
      $headerLink[] = "<{$url}?page={$next}&per_page={$perPage}>; rel=\"next\"";
    }

    $headerLink[] = "<{$url}?page=$last&per_page={$perPage}>; rel=\"last\"";
    $headerLink[] = "<{$url}?page=1&per_page={$perPage}>; rel=\"first\"";

    if ($prev > 0) {
      $headerLink[] = "<{$url}?page={$prev}&per_page={$perPage}>; rel=\"prev\"";
    }

    $response->headers->add(['LINK' => implode(',', $headerLink)]);

    if ($perPage > $total) {
      $query->range(0, $total);
    }
    else {
      $query->range($firstRecord, $perPage);
    }
  }

}

<?php

namespace Drupal\skyword\Plugin\rest\resource;

class SkywordCommonTools {

  /**
   * Helper method to store the file.
   *
   * @param string $data
   *   The string url of the file.
   */
  public static function storeFile($file) {
    $fileContent = file_get_contents($file);
    $directory = 'public://Image';

    file_prepare_directory($directory, FILE_CREATE_DIRECTORY); 

    return file_save_data($fileContent, $directory . basename($file), FILE_EXISTS_REPLACE);
  }

  public static function getTypes($id = NULL) {
    try {
      $query = \Drupal::entityQuery('node_type');

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

}


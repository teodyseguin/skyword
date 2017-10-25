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

}


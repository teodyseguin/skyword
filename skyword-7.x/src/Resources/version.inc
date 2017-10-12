<?php

/**
 * @file
 * version.inc
 */

/**
 * - Returns the site version number to the Skyword Service.
 */
function _skyword_version_index() {
  try {
    return (object) array(
      'version' => SKYWORD_VERSION,
      'cms' => array(
        'name' => 'Drupal',
        'version' => VERSION
      )
    );
  }
  catch (Exception $e) {
    throw $e;
  }
}

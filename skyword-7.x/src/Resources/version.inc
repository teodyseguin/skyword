<?php

/**
* - Returns the site version number to the Skyword Service.
*/
function _skyword_version_retrieve() {
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
    watchdog_exception($e, 'skyword');
    throw $e;
  }
}

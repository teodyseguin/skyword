<?php

/**
* - Returns the site version number to the Skyword Service.
*/
function skyword_version_retrieve() {
  return (object)array(
    'version' => SKYWORD_VERSION,
    'cms' => array(
      'name' => 'Drupal',
      'version' => VERSION
    )
  );
}

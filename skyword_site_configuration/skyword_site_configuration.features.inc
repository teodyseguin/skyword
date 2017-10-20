<?php
/**
 * @file
 * skyword_site_configuration.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function skyword_site_configuration_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "services" && $api == "services") {
    return array("version" => "3");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

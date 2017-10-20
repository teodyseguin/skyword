<?php
/**
 * @file
 * skyword_site_configuration.features.user_role.inc
 */

/**
 * Implements hook_user_default_roles().
 */
function skyword_site_configuration_user_default_roles() {
  $roles = array();

  // Exported role: skyword_authors.
  $roles['skyword_authors'] = array(
    'name' => 'skyword_authors',
    'weight' => 3,
  );

  return $roles;
}

<?php
/**
 * @file
 * skyword_site_configuration.features.user_permission.inc
 */

/**
 * Implements hook_user_default_permissions().
 */
function skyword_site_configuration_user_default_permissions() {
  $permissions = array();

  // Exported permission: 'administer skyword'.
  $permissions['administer skyword'] = array(
    'name' => 'administer skyword',
    'roles' => array(
      'administrator' => 'administrator',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'author create'.
  $permissions['author create'] = array(
    'name' => 'author create',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'authors retrieve'.
  $permissions['authors retrieve'] = array(
    'name' => 'authors retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'content types create'.
  $permissions['content types create'] = array(
    'name' => 'content types create',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'content types retrieve'.
  $permissions['content types retrieve'] = array(
    'name' => 'content types retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'media create'.
  $permissions['media create'] = array(
    'name' => 'media create',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'media retrieve'.
  $permissions['media retrieve'] = array(
    'name' => 'media retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'post create'.
  $permissions['post create'] = array(
    'name' => 'post create',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'post delete'.
  $permissions['post delete'] = array(
    'name' => 'post delete',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'posts retrieve'.
  $permissions['posts retrieve'] = array(
    'name' => 'posts retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'taxonomies retrieve'.
  $permissions['taxonomies retrieve'] = array(
    'name' => 'taxonomies retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'taxonomy create'.
  $permissions['taxonomy create'] = array(
    'name' => 'taxonomy create',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'token retrieve'.
  $permissions['token retrieve'] = array(
    'name' => 'token retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  // Exported permission: 'use oauth2 server'.
  $permissions['use oauth2 server'] = array(
    'name' => 'use oauth2 server',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'oauth2_server',
  );

  // Exported permission: 'version retrieve'.
  $permissions['version retrieve'] = array(
    'name' => 'version retrieve',
    'roles' => array(
      'administrator' => 'administrator',
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'skyword',
  );

  return $permissions;
}

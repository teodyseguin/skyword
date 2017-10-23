<?php
/**
 * @file
 * skyword_site_configuration.services.inc
 */

/**
 * Implements hook_default_services_endpoint().
 */
function skyword_site_configuration_default_services_endpoint() {
  $export = array();

  $endpoint = new stdClass();
  $endpoint->disabled = FALSE; /* Edit this to true to make a default endpoint disabled initially */
  $endpoint->api_version = 3;
  $endpoint->name = 'skyword';
  $endpoint->server = 'rest_server';
  $endpoint->path = 'skyword/v1';
  $endpoint->authentication = array(
    'oauth2_server' => array(
      'server' => 'skyword',
    ),
  );
  $endpoint->server_settings = array(
    'formatters' => array(
      'json' => TRUE,
      'bencode' => FALSE,
      'jsonp' => FALSE,
      'php' => FALSE,
      'xml' => FALSE,
    ),
    'parsers' => array(
      'application/json' => TRUE,
      'application/x-www-form-urlencoded' => FALSE,
      'application/xml' => FALSE,
      'multipart/form-data' => FALSE,
      'text/xml' => FALSE,
    ),
  );
  $endpoint->resources = array(
    'authors' => array(
      'operations' => array(
        'create' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'retrieve' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'update' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'index' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
      ),
    ),
    'content-type' => array(
      'operations' => array(
        'create' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'retrieve' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'index' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
      ),
    ),
    'media' => array(
      'operations' => array(
        'create' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'retrieve' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'index' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
      ),
    ),
    'posts' => array(
      'operations' => array(
        'create' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'retrieve' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'delete' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'index' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
      ),
    ),
    'taxonomies' => array(
      'operations' => array(
        'create' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'retrieve' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
        'index' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
      ),
    ),
    'version' => array(
      'operations' => array(
        'index' => array(
          'enabled' => '1',
          'settings' => array(
            'oauth2_server' => array(
              'require_authentication' => '1',
              'scope' => 'skyword',
            ),
          ),
        ),
      ),
    ),
  );
  $endpoint->debug = 0;
  $export['skyword'] = $endpoint;

  return $export;
}

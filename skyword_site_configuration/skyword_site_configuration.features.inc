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

/**
 * Implements hook_default_oauth2_server().
 */
function skyword_site_configuration_default_oauth2_server() {
  $items = array();
  $items['skyword'] = entity_import('oauth2_server', '{
    "name" : "skyword",
    "label" : "Skyword",
    "settings" : {
      "enforce_state" : true,
      "default_scope" : "skyword",
      "allow_implicit" : 1,
      "use_openid_connect" : 0,
      "use_crypto_tokens" : 0,
      "grant_types" : {
        "authorization_code" : "authorization_code",
        "client_credentials" : "client_credentials",
        "refresh_token" : "refresh_token",
        "password" : "password",
        "urn:ietf:params:oauth:grant-type:jwt-bearer" : 0
      },
      "always_issue_new_refresh_token" : 0,
      "unset_refresh_token_after_use" : 1,
      "access_lifetime" : "3600",
      "id_lifetime" : "3600",
      "refresh_token_lifetime" : "1209600",
      "require_exact_redirect_uri" : 0
    },
    "rdf_mapping" : [],
    "scopes" : [
      {
        "name" : "skyword",
        "description" : "Skyword scope",
        "rdf_mapping" : []
      }
    ]
  }');
  return $items;
}

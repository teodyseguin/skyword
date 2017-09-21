<?php

/**
 * @file
 * token.inc
 */

function _skyword_token_retrieve($data) {
  return (object)[
    'access_token' => drupal_random_key(),
    'token_type' => 'bearer',
    'expires_in' => 3600,
  ];
}


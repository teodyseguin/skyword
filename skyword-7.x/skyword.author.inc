<?php

/**
* Resource callback for a single author.
*/
function _skyword_authors_get($id) {
  $results = skyword_author_load_multiple(array(intval($id)));
  return !empty($results) ? (object) reset($results) : (object) array( );
}

/**
* Resource callback for Authors Index.
*/
function _skyword_authors_index() {
  $results = skyword_author_load_multiple();
  watchdog('skyword', '<pre>' . print_r($results, true) . '</pre>');
  return (object) $results;
}

/**
* Resource callback for Creating a Author.
*/
function _skyword_authors_create($data) {
  try {
    $id = variable_get('skyword_author_id', 'field_id');
    $firstName = variable_get('skyword_author_firstname', 'field_first_name');
    $lastName = variable_get('skyword_author_lastname', 'field_last_name');
    $email = variable_get('skyword_author_email', 'field_email');
    $byline = variable_get('skyword_author_byline', 'field_byline');
    $icon = variable_get('skyword_author_icon', 'field_icon');
    $result = $data;

    $node = new stdClass();
    $node->type = "author";
    $node->language = "und";
    $node->title = $data['firstName'] . " " . $data['lastName'];

    $node->{$id} = array(
      'und' => array(
        array(
          'value' => $data['id']
        )
      )
    );

    $node->{$firstName} = array(
      'und' => array(
        array(
          'value' => $data['firstName']
        )
      )
    );

    $node->{$lastName} = array(
      'und' => array(
        array(
          'value' => $data['lastName']
        )
      )
    );

    $node->{$email} = array(
      'und' => array(
        array(
          'value' => $data['email']
        )
      )
    );

    $node->{$byline} = array(
      'und' => array(
        array(
          'value' => $data['byline']
        )
      )
    );

    if (isset($data['icon'])) {
      $file = system_retrieve_file($data['icon'], 'public://', TRUE, FILE_EXISTS_RENAME);

      $node->{$icon} = array(
        'und' => array(
          array(
            'fid' => $file->fid
          )
        )
      );
    }
    node_submit($node);
    if (node_save($node)) {
      return (object) array(
        'location' => url('node/' . $node->nid)
      );
    }
  }
  catch(Exceptioin $e) {
    watchdog_exception($e);
    return $e;
  }
}

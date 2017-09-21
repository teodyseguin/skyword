<?php

/**
* Configure system for Skyword managed vocabularies.
*/
function skyword_vocabulary_manage($form, &$form_state, $vocabulary = 'tags') {
  $form['skyword_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use for Skyword'),
  );

  // get fields to send to skyword

  $form['fields'] = array(
    '#tree' => TRUE,
    '#theme' => 'table',
    '#header' => array(
      'name' => t('Field'),
      'description' => t('Description'),
      'required' => t('Required'),
      'enabled' => t('Enabled')
    ),
    '#rows' => array(),
  );

  $fields = field_info_instances('taxonomy_term', $vocabulary);

  foreach($fields as $id => $field) {
    $enabled = array(
      '#type' => 'checkbox'
    );
    $form['fields'][$id] = &$enabled;
    $form['fields']['#rows'][] = array(
      $field['label'],
      $field['description'],
      $field['required'] ? t('Yes') : t('No'),
      &$enabled
    );
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}

/**
* Configure system for Skyword managed content types.
*/
function skyword_content_manage($form, &$form_state, $content_type = 'page') {

  $form['skyword_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use for Skyword'),
  );

  // get fields to send to skyword

  $form['fields'] = array(
    '#tree' => TRUE,
    '#theme' => 'table',
    '#header' => array(
      'name' => t('Field'),
      'description' => t('Description'),
      'required' => t('Required'),
      'enabled' => t('Enabled')
    ),
    '#rows' => array(),
  );

  $fields = field_info_instances('node', $content_type);

  foreach($fields as $id => $field) {
    $enabled = array(
      '#type' => 'checkbox'
    );
    $form['fields'][$id] = &$enabled;
    $form['fields']['#rows'][] = array(
      $field['label'],
      $field['description'],
      $field['required'] ? t('Yes') : t('No'),
      &$enabled
    );
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}


/**
* Returns the administration form for Skyword service.
*/
function skyword_admin_settings($form, &$form_state) {

  $form['#submit'] = array( 'skyword_admin_settings_form_submit' );

  $form['resources'] = array(
    '#type' => 'vertical_tabs',
    '#title' => t('Example vertical tab'),
    // '#group' => 'additional_settings',
  );

  $form['author'] = array(
    '#group' => 'resources',
    '#type' => 'fieldset',
    '#title' => t('Authors'),
    '#tree' => TRUE,
    '#description' => t('Define which elements of a single author (= Sources) will
      map to which content pieces in Drupal (= Targets).
      Make sure that at least one definition has a Unique target.
      A unique target means that a value for a target can only occur once.
      E. g. only one item with the URL http://example.com/content/1 can exist.'),
  );

  $author_content_type = isset($form_state['values']['author']['skyword_author_content_type']) ?
    $form_state['values']['author']['skyword_author_content_type'] : variable_get('skyword_author_content_type', 'author');

  $form['author']['skyword_author_content_type'] = array(
    '#type' => 'select',
    '#title' => t('Content Type'),
    '#description' => t('Select the Content type the Author should be mapped to in Drupal.'),
    '#options' => _skyword_available_content_types(),
    '#default_value' => $author_content_type
  );

  if ($author_content_type !== '') {
    $mappings = variable_get('skyword_author_field_mappings', array());
    $form['author']['skyword_author_field_mappings'] = array(
      '#theme' => 'table',
      '#header' => array(
        'source' => t('Source'),
        'target' => t('Target'),
        'target_configuration' => t('Target Configuration'),
        'remove' => ''
      ),
      '#rows' => array(),
      '#empty' => t('Please add mappings for the fields.')
    );
    $source_fields = array(
      'id' => t('Author Unique​ ​Identifier​'),
      'firstName' => t('First Name'),
      'lastName' => t('Last Name'),
      'email' => t('Email'),
      'byline' => t('Byline'),
      'icon' => t('Icon')
    );

    foreach($mappings as $id => $mapping) {
      _skyword_admin_field_map_form($form['author']['skyword_author_field_mappings'], $form_state, $mapping, 'node', 'author', $source_fields);
    }
    _skyword_admin_field_map_form($form['author']['skyword_author_field_mappings'], $form_state, array(), 'node', 'author', $source_fields);
  }

  $form['taxonomy'] = array(
    '#group' => 'resources',
    '#type' => 'fieldset',
    '#title' => t('Taxonomies'),
    '#tree' => TRUE,
  );

  $form['taxonomy']['vocabularies'] = array(
    '#theme' => 'table',
    '#header' => array(
      'id' => t('ID'),
      'name' => t('Name'),
      'remove' => t('Remove')
    )
  );

  _skyword_admin_taxonomy_vocabulary_form($form, $form_state);

  $form['actions'] = array(
    '#type' => 'actions',
    '#weight' => 100,
  );

  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}


/**
* Helper function for building taxonomy vocabulary table.
*/
function _skyword_admin_taxonomy_vocabulary_form(&$form, $form_state, $vocabulary_map = array()) {
  $id = array(
    '#type' => 'textfield',
    '#size' => 10,
    '#default_value' => isset($vocabulary_map['id']) ? $vocabulary_map['id'] : '',
  );
  $vocabularies = taxonomy_get_vocabularies();
  $options = array( -1 => t('Select a vocabulary') );

  foreach($vocabularies as $vocabulary) {
    $options[$vocabulary->machine_name] = $vocabulary->name;
  }

  $name = array(
    '#type' => 'select',
    '#default_value' => isset($vocabulary_map['name']) ? $vocabulary_map['name'] : '',
    '#options' => $options
  );

  $form['taxonomy']['vocabularies'][] = array(
    &$id,
    &$name,
  );

  $form['taxonomy']['vocabularies']['#rows'][] = array(
    array( 'data' => &$id ),
    array( 'data' => &$name ),
    array( 'data' => '' )
  );

  // Add field mappings for this taxonomy term immediately below.
  $taxonomy_table = array(
    '#theme' => 'table',
    '#tree' => true,
    '#title' => t('Field Mapping'),
    '#description' => t('Define which elements of a single author (= Sources) will
      map to which content pieces in Drupal (= Targets).
      Make sure that at least one definition has a Unique target.
      A unique target means that a value for a target can only occur once.
      E. g. only one item with the URL http://example.com/content/1 can exist.'),
  );

  $field_sources = array(
    'id',
    'value',
    'parent'
  );
  _skyword_admin_field_map_form($taxonomy_table, $form_state, array(), 'taxonomy_term', 'tags', $field_sources);

  $form['taxonomy']['vocabularies'][] = array(
    &$taxonomy_table
  );

  $form['taxonomy']['vocabularies']['#rows'][] = array(
    array(
      'data' => &$taxonomy_table,
      'colspan' => 3
    )
  );

  $save = array(
    '#type' => 'button',
    '#value' => t('Save & Add Mapping')
  );

  $form['taxonomy']['vocabularies'][] = array(
    &$save
  );

  $form['taxonomy']['vocabularies']['#rows'][] = array(
    array(
      'data' => &$save,
      'colspan' => 3
    )
  );

}


/**
* Helper function for building field mapped rows.
*/
function _skyword_admin_field_map_form(&$form, $form_state, $field_map = FALSE, $type = 'node', $bundle = 'author', $source_fields = array()) {
  $source = array(
    '#type' => 'select',
    '#options' => $source_fields,
    '#default_value' => isset($field_map['source']) ? $field_map['source'] : -1
  );

  $target_fields = _skyword_content_type_fields($type, $bundle);
  $target = array(
    '#type' => 'select',
    '#options' => $target_fields,
    '#default_value' => isset($field_map['target']) ? $field_map['target'] : -1
  );

  $target_settings = array();

  $remove = array();

  $form[] = array(
    'source' => &$source,
    'target' => &$target,
    'target_settings' => &$target_settings,
    'remove' => &$remove
  );

  $form['#rows'][] = array(
    array('data' => &$source),
    array('data' => &$target),
    array('data' => &$target_settings),
    array('data' => &$remove)
  );

}

/**
* Submit handler for the administration form.
*/
function skyword_admin_settings_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  // Handle mappings for the Author Content type.
  $author_settings = $values['author'];

  variable_set('skyword_author_content_type', $author_settings['skyword_author_content_type']);
  variable_set('skyword_author_field_mappings', $author_settings['skyword_author_field_mappings']);

}

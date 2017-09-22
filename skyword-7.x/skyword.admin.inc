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
    '#title' => t('Enable this content type for skyword use.'),
  );

  $form['description'] = array(
    '#markup' => '<p>Then choose which field you like to be enabled from this content type.</p>',
  );

  // get fields to send to skyword

  $fields = field_info_instances('node', $content_type);

  $options = array();
  $default_values = array();

  foreach($fields as $id => $field) {
    $options[$id] = '';

    $form['rows'][$id]['field'] = array(
      '#markup' => $field['label'],
    );

    $form['rows'][$id]['description'] = array(
      '#markup' => $field['description'],
    );

    $form['rows'][$id]['required'] = array(
      '#markup' => $field['required'] ? t('Yes') : t('No'),
    );

    $default_values[] = variable_get("skyword_$id", '');
  }

  $form['checkboxes'] = array(
    '#type' => 'checkboxes',
    '#options' => $options,
    '#default_value' => $default_values,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  $form_state['content_type'] = $content_type;

  return $form;
}

/**
 * Implements hook_submit().
 */
function skyword_content_manage_submit($form, &$form_state) {
  // we do upsert (update/insert) for the status
  db_merge('skyword_entities')
    ->key(array('entity_type' => $form_state['content_type']))
    ->fields(array(
      'bundle' => 'node',
      'status' => $form_state['values']['skyword_status'],
    ))
    ->execute();

  // save which enabled fields are chosen
  foreach ($form_state['values']['checkboxes'] as $id => $value) {
    variable_set("skyword_$id", $value);
  }
}


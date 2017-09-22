<?php

/**
 * Configure system for Skyword managed vocabularies.
 */
function skyword_vocabulary_manage($form, &$form_state, $vocabulary = 'tags') {
  $form['skyword_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this vocabulary for skyword use'),
    '#default_value' => skyword_get_status($vocabulary),
  );

  $form['description'] = array(
    '#markup' => '<p>Then choose which field you like to be enabled from this vocabulary</p>',
  );

  // get fields to send to skyword
  $fields = field_info_instances('taxonomy_term', $vocabulary);

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

    $default_values[] = variable_get('skyword_' . $vacabulary . '_' . $id, '');
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

  $form_state['vocabulary'] = $vocabulary;

  return $form;
}

/**
 * Configure system for Skyword managed content types.
 */
function skyword_content_manage($form, &$form_state, $content_type = 'page') {
  $form['skyword_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this content type for skyword use.'),
    '#default_value' => skyword_get_status($content_type),
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

    $default_values[] = variable_get('skyword_' . $content_type . '_' . $id, '');
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
  $content_type = $form_state['content_type'];

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
    variable_set('skyword_' . $content_type . '_' . $id, $value);
  }
}

/**
 * Implements hook_submit().
 */
function skyword_vocabulary_manage_submit($form, &$form_state) {
  $vocabulary = $form_state['vocabulary'];

  // we do upsert (update/insert) for the status
  db_merge('skyword_entities')
    ->key(array('entity_type' => $form_state['vocabulary']))
    ->fields(array(
      'bundle' => 'taxonomy',
      'status' => $form_state['values']['skyword_status'],
    ))
    ->execute();

  foreach ($form_state['values']['checkboxes'] as $id => $value) {
    variable_set('skyword_' . $vocabulary . '_' . $id, $value);
  }
}

/**
 * Get the status of the entity type
 *
 * @param $entity_type
 *   the type of entity stored
 *
 * @return status 0 || 1
 */
function skyword_get_status($entity_type) {
  return db_query("SELECT status FROM {skyword_entities} WHERE entity_type = :entity_type", array(':entity_type' => $entity_type))->fetchField();
}


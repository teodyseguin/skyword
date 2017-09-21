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

  foreach($fields as $id => $field) {
    $options[$id] = array(
      'name' => $field['label'],
      'description' => $field['description'],
      'required' => $field['required'] ? t('Yes') : t('No'),
    );
  }

  $form['fields'] = array(
    '#type' => 'tableselect',
    '#header' => array(
      'name' => t('Fields'),
      'description' => t('Description'),
      'required' => t('Required'),
    ),
    '#options' => $options,
    '#multiple' => TRUE,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}

function skyword_content_manage_submit($form, &$form_state) {
  object_log('skyword content manage', $form_state);
}


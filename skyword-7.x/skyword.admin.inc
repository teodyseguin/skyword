<?php

/**
 * Configure system for Skyword managed vocabularies.
 */
function skyword_vocabulary_manage($form, &$form_state, $vocabulary = 'tags') {
  $data = skyword_entity_data('taxonomy_term', $vocabulary);
  $form['id'] = array(
    '#type' => 'value',
    '#value' => $data['id']
  );

  $form['vocabulary'] = array(
    '#type' => 'value',
    '#value' => $vocabulary
  );

  $form['skyword_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this vocabulary for skyword use'),
    '#default_value' => isset($data['status']) ? $data['status'] : 0,
  );

  $form['description'] = array(
    '#markup' => '<p>Then choose which field you like to be enabled from this vocabulary</p>',
  );

  // get fields to send to skyword
  $fields = field_info_instances('taxonomy_term', $vocabulary);

  $options = array();
  $default_values = array();
  $form['data'] = array(
    '#type' => 'fieldset',
    '#tree' => TRUE
  );
  $form['data']['fields'] = array(
    '#theme' => 'table',
    '#tree' => TRUE,
    '#rows' => [],
    '#header' => array(
      array('data' => t('Field')),
      array('data' => t('Description')),
      array('data' => t('Required')),
      array('data' => t('Status'))
    )
  );

  foreach($fields as $id => $field) {
    $status = array(
      '#type' => 'checkbox',
      '#default_value' => $data['data'][$id]['status']
    );

    $form['data']['fields'][$id]['status'] = &$status;
    $form['data']['fields']['#rows'][$id] = array(
      $field['label'],
      $field['description'],
      $field['required'] ? t('Yes') : t('No'),
      array('data' => &$status )
    );
    unset($status);
    unset($id);
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  $form_state['vocabulary'] = $vocabulary;

  return $form;
}

/**
 * Implements hook_submit().
 */
function skyword_vocabulary_manage_submit($form, &$form_state) {
  $vocabulary = $form_state['values']['vocabulary'];

  try {
    db_merge('skyword_entities')
    ->key(array(
      'entity_type' => 'taxonomy_term',
      'bundle' => $form_state['values']['vocabulary']
    ))
    ->fields(array(
      'entity_type' => 'taxonomy_term',
      'bundle' => $form_state['values']['vocabulary'],
      'status' => $form_state['input']['skyword_status'],
      'data' => isset($form_state['input']['data']) ? serialize($form_state['input']['data']['fields']) : '',
    ))
    ->execute();
  }
  catch (Exception $e) {
    throw new Exception('Unable to update skyword_entities table', $e);
  }
}

/**
 * Configure system for Skyword managed content types.
 */
function skyword_content_manage($form, &$form_state, $content_type = 'page') {
  $data = skyword_entity_data('node', $content_type);

  $form['id'] = array(
    '#type' => 'value',
    '#value' => $data['id']
  );

  $form['content_type'] = array(
    '#type' => 'value',
    '#value' => $content_type
  );

  $form['skyword_status'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this content type for skyword use.'),
    '#default_value' => $data['status'],
  );

  $form['description'] = array(
    '#markup' => '<p>Then choose which field you like to be enabled from this content type.</p>',
  );

  $form['data'] = array(
    '#type' => 'fieldset',
    '#tree' => TRUE,
  );

  $form['data']['fields'] = array(
    '#theme' => 'table',
    '#header' => array(
      array('data' => t('Field')),
      array('data' => t('Description')),
      array('data' => t('Required')),
      array('data' => t('Status'))
    ),
    '#rows' => array()
  );

  // get fields to send to skyword
  $fields = field_info_instances('node', $content_type);
  foreach ($fields as $id => $field) {

    $enable = array(
      '#type' => 'checkbox',
      '#default_value' => isset($data['data'][$id]) ? $data['data'][$id]['status'] : 0
    );

    $form['data']['fields'][$id]['status'] = &$enable;

    $form['data']['fields']['#rows'][$id] = array(
      array('data' => $field['label']),
      array('data' => $field['description']),
      array('data' => $field['required'] ? t('Yes') : t('No') ),
      array('data' => &$enable )
    );

    unset($enable);
    unset($id);
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}

/**
 * Implements hook_submit().
 */
function skyword_content_manage_submit($form, &$form_state) {
  $content_type = $form_state['values']['content_type'];

  try {
    db_merge('skyword_entities')
    ->key(array(
      'entity_type' => 'node',
      'bundle' => $content_type
    ))
    ->fields(array(
      'entity_type' => 'node',
      'bundle' => $content_type,
      'status' => $form_state['input']['skyword_status'],
      'data' => serialize($form_state['input']['data']['fields'])
    ))
    ->execute();
  }
  catch (Exception $e) {
    throw new Exception('Unable to update skyword_entities table', $e);
  }
}


/**
 * Configure system for Skyword managed users.
 */
function skyword_user_manage($form, &$form_state) {
  $roles = array('-- Select --') + user_roles();

  // remove anonyous user from the selection
  unset($roles[1]);

  $data = skyword_entity_data('user', 'user');
  
  $form['role'] = array(
    '#type' => 'select',
    '#title' => t('Skyword Author Role'),
    '#options' => $roles,
    '#default_value' => isset($data['data']['role']) ? $data['data']['role'] : 2,
  );

  $form['data'] = array(
    '#type' => 'fieldset',
    '#tree' => TRUE,
  );

  $form['data']['fields'] = array(
    '#theme' => 'table',
    '#header' => array(
      array('data' => t('Field')),
      array('data' => t('Description')),
      array('data' => t('Map To')),
      array('data' => t('Required')),
      array('data' => t('Status'))
    ),
    '#rows' => array()
  );

  // get fields to send to skyword
  $fields = field_info_instances('user', 'user');

  $mail_enable = array(
    '#type' => 'checkbox',
    '#default_value' => isset($data['data']['fields']['mail']) ? $data['data']['fields']['mail']['status'] : 0,
  );
  $mail_mapto = array(
    '#type' => 'select',
    '#options' => skyword_user_expected_fields(),
    '#default_value' => isset($data['data']['fields']['mail']) ? $data['data']['fields']['mail']['mapto'] : 'none',
  );
  $form['data']['fields']['mail']['status'] = &$mail_enable;
  $form['data']['fields']['mail']['mapto'] = &$mail_mapto;
  $form['data']['fields']['#rows']['mail'] = array(
    array('data' => 'E-mail'),
    array('data' => 'The user e-mail'),
    array('data' => &$mail_mapto),
    array('data' => t('No')),
    array('data' => &$mail_enable),
  );

  foreach ($fields as $id => $field) {
    $enable = array(
      '#type' => 'checkbox',
      '#default_value' => isset($data['data']['fields'][$id]) ? $data['data']['fields'][$id]['status'] : 0,
    );

    $mapto = array(
      '#type' => 'select',
      '#options' => skyword_user_expected_fields(),
      '#default_value' => isset($data['data']['fields'][$id]) ? $data['data']['fields'][$id]['mapto'] : 'none',
    );

    $form['data']['fields'][$id]['status'] = &$enable;
    $form['data']['fields'][$id]['mapto'] = &$mapto;

    $form['data']['fields']['#rows'][$id] = array(
      array('data' => $field['label']),
      array('data' => $field['description']),
      array('data' => &$mapto),
      array('data' => $field['required'] ? t('Yes') : t('No') ),
      array('data' => &$enable)
    );

    unset($enable);
    unset($mapto);
    unset($id);
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );

  return $form;
}

function skyword_user_expected_fields() {
  return array(
    'none' => '-- Select --',
    'id' => 'ID',
    'mail' => 'E-mail',
    'firstName' => 'First Name',
    'lastName' => 'Last Name',
    'byline' => 'Byline',
    'icon' => 'Icon',
  );
}

/**
 * Implements hook_submit().
 */
function skyword_user_manage_submit($form, &$form_state) {
  $data['fields'] = $form_state['input']['data']['fields'];
  $data['role'] = $form_state['values']['role'];
  $status = $form_state['values']['role'] == 0 ? 0 : 1;

  try {
    db_merge('skyword_entities')
    ->key(array(
      'entity_type' => 'user',
      'bundle' => 'user'
    ))
    ->fields(array(
      'entity_type' => 'user',
      'bundle' => 'user',
      'status' => $status,
      'data' => serialize($data)
    ))
    ->execute();

    drupal_set_message(t('Account settings saved.'));
  }
  catch (Exception $e) {
    throw new Exception('Unable to update skyword_entities table', $e);
  }
}


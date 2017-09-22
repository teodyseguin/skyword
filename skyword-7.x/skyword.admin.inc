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

  // @TODO this is not properly creating / removing values...... remove db_delete when we sort this out....
  $vocabulary = $form_state['values']['vocabulary'];
  db_delete('skyword_entities')
    ->condition('entity_type','taxonomy_term')
    ->condition('bundle', $form_state['values']['vocabulary'])
    ->execute();

  db_merge('skyword_entities')
    ->key(array(
      'entity_type' => 'taxonomy_term',
      'bundle' => $form_state['values']['vocabulary']
    ))
    ->fields(array(
      'entity_type' => 'taxonomy_term',
      'bundle' => $form_state['values']['vocabulary'],
      'status' => $form_state['input']['skyword_status'],
      'data' => serialize($form_state['input']['data']['fields'])
    ))
    ->execute();
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
  db_delete('skyword_entities')
    ->condition('entity_type', 'node')
    ->condition('bundle', $content_type)
    ->execute();

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


/**
* Configure system for Skyword managed users.
*/
function skyword_user_manage($form, &$form_state) {
  $roles = user_roles();
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
      array('data' => t('Required')),
      array('data' => t('Status'))
    ),
    '#rows' => array()
  );

  // get fields to send to skyword
  $fields = field_info_instances('user', 'user');


  foreach ($fields as $id => $field) {
    $enable = array(
      '#type' => 'checkbox',
      '#default_value' => isset($data['data']['fields'][$id]) ? $data['data']['fields'][$id]['status'] : 0
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

function skyword_user_manage_submit($form, &$form_state) {

  db_delete('skyword_entities')
    ->condition('entity_type', 'user')
    ->condition('bundle', 'user')
    ->execute();

  $data['fields'] = $form_state['input']['data']['fields'];
  $data['role'] = $form_state['values']['role'];

  db_merge('skyword_entities')
    ->key(array(
      'entity_type' => 'user',
      'bundle' => 'user'
    ))
    ->fields(array(
      'entity_type' => 'user',
      'bundle' => 'user',
      'status' => 1,
      'data' => serialize($data)
    ))
    ->execute();
}

<?php

/**
 * @file
 * skyword.theme.inc.
 */

function theme_skyword_content_manage($variables) {
  $form = $variables['form'];
  $columns = array('field', 'description', 'required');
  $rows = array();

  foreach (element_children($form['rows']) as $key) {
    $row = array();

    foreach ($columns as $column) {
      $row[] = array(
        'data' => drupal_render($form['rows'][$key][$column]),
      );
    }

    $row[] = array(
      'data' => drupal_render($form['checkboxes'][$key]),
    );

    $rows[] = $row;
  }

  $header = array('Fields', 'Description', 'Required', 'Enable');

  $output = drupal_render($form['skyword_status']);
  $output .= drupal_render($form['description']);
  $output .= theme('table', array(
    'header' => $header,
    'rows' => $rows,
  ));
  $output .= drupal_render($form['submit']);

  $output .= drupal_render_children($form);

  return $output;
}

function theme_skyword_vocabulary_manage($variables) {
  $form = $variables['form'];
  $columns = array('field', 'description', 'required');
  $rows = array();

  foreach (element_children($form['rows']) as $key) {
    $row = array();

    foreach ($columns as $column) {
      $row[] = array(
        'data' => drupal_render($form['rows'][$key][$column]),
      );
    }

    $row[] = array(
      'data' => drupal_render($form['checkboxes'][$key]),
    );

    $rows[] = $row;
  }

  $header = array('Fields', 'Description', 'Required', 'Enable');

  $output = drupal_render($form['skyword_status']);
  $output .= drupal_render($form['description']);
  $output .= theme('table', array(
    'header' => $header,
    'rows' => $rows,
  ));
  $output .= drupal_render($form['submit']);

  $output .= drupal_render_children($form);

  return $output;
}

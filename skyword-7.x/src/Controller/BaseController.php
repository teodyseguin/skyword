<?php

class BaseController {

  protected $query;
  protected $page;
  protected $per_page;
  protected $fields;

  /**
  * Used to get and return Pager data sent via post requests.
  */
  protected function pager() {
    $current_page = $this->page;
    $per_page = $this->per_page;

    if (!$current_page) return;
    if (!$per_page) return;

    $first_record = $current_page * $per_page;
    $next = $current_page + 1;
    $prev = $current_page - 1;
    $total = $this->query->countQuery()->execute()->fetchField();

    $last =  $total % $per_page;

    $url = (isset($_SERVER['HTTPS']) ? "https:" : "http:") . '//' . $_SERVER["HTTP_HOST"].strtok($_SERVER["REQUEST_URI"],'?');

    drupal_add_http_header('X-Total-Count', $total, TRUE);

    if ($next < $last) {
      drupal_add_http_header('LINK', "<{$url}?page={$next}&per_page={$per_page}>; rel=\"next\"", TRUE);
    }

    drupal_add_http_header('LINK', "<{$url}?page={$last}&per_page={$per_page}>; rel=\"last\"", TRUE);
    drupal_add_http_header('LINK', "<{$url}?page=1&per_page={$per_page}>; rel=\"first\"", TRUE);

    if ($prev > 0) {
      drupal_add_http_header('LINK', "<{$url}?page={$prev}&per_page={$per_page}>; rel=\"prev\"", TRUE);
    }

    if ($first_record >= $per_page) {
      $this->query->range(0, $per_page);
    }
    else {
      $this->query->range($first_record, $per_page);
    }
  }

  /**
   * Build the data normally
   *
   * @param $entities
   *   an array of entity object
   */
  protected function buildData($entities, $fields, $list = TRUE) {
    if ($list) {
      $data = [];

      foreach ($entities as $entity) {
        $obj = new stdClass();

        foreach ($fields as $field) {
          if (property_exists($entity, $field)) $obj->{$field} = $entity->{$field};
        }

        $data[] = $obj;
      }

      return $data;
    }
    else {
      $obj = new stdClass();

      foreach ($fields as $field) {
        $obj->{$field} = $entities->{$field};
      }

      return $obj;
    }
  }

  /**
   * Extract the fields from a string
   */
  protected function extractFields($fields) {
    $f = explode(',', $fields);
    return array_flip($f);
  }

  /**
   * Limit remove the fields from an object
   */
  protected function limitOutputByFields($fields, &$data) {
    $f = explode(',', $fields);
    $fieldsOutput = array_flip($f);

    foreach ($data as $record) {
      foreach ($fieldsOutput as $field => $index) {
        if (property_exists($record, $field)) {
          unset($record->{$field});
        }
      }
    }
  }

  /**
   * Return the content types that are enabled for skyword use.
   */
  protected function getEnabledContentTypes() {
    $query = db_select('skyword_entities', 'se');
    $query->condition('se.status', 1);
    $query->condition('se.entity_type', 'node');
    $query->fields('se', ['bundle']);
    $result = $query->execute()->fetchAll();

    foreach ($result as $row) {
      $data[] = $row->bundle;
    }

    return $data;
  }
}

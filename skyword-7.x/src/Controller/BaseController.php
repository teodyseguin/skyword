<?php

class BaseController {
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
          $obj->{$field} = $entity->{$field};
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
}


<?php

class BaseController {
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


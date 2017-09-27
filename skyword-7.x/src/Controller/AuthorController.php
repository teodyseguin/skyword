<?php

include 'BaseController.php';
include 'ControllerInterface.php';

class AuthorController extends BaseController {
  private $enabledFields;

  /**
   * Returns a list of Taxonomies
   *
   * @param $page
   *   determins the number of page to return. default to 1
   * @param $per_page
   *   determines the number of items to be included in a page. default to 250
   * @param $fields
   *   determines the field names to be included on the data. default to NULL
   */
  public function index($page = 1, $per_page = 250, $fields = NULL) {
    try {
      $users = $this->loadUsers();

      if ($users) {
        return $users;
      }
      else {
        return services_error(t('There are no authors found.'), 404);
      }
    }
    catch (Exception $e) {
      return services_error(t('Unable to query authors table'), 404);
    }
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $fields = NULL) {
    try {
      $user = $this->loadUsers($id);

      if ($user) {
        return $user;
      }
      else {
        return services_error(t("No author of id #$id was found."), 404);
      }
    }
    catch (Exception $e) {
      return services_error(t('Unable to query authors table'), 404);
    }
  }

  /**
   * Create a Taxonomy
   */
  public function create($data) {
    $newUser = $this->prepareNewUser($data);

    try {
      user_save(NULL, $newUser);

      return (object)$data;
    }
    catch (Exception $e) {
      return services_error(t('Unable to create a new author.'), 500);
    }
  }

  /**
   * Update a Taxonomy
   */
  public function update() {}

  /**
   * Delete a Taxonomy
   */
  public function delete() {}

  /**
   * Build the data per count
   *
   * @param $per_page
   *   the number of items to be included in a page
   * @param $taxonomies
   *   an array of taxonomies
   */
  private function buildDataWithCount($per_page, $taxonomies) {}

  /**
   * Check which user role are enabled for skyword use
   */
  private function checkUserEntityRoleEnabled() {
    $query = db_select('skyword_entities', 's');
    $query->condition('s.status', 1);
    $query->condition('s.bundle', 'user');
    $query->fields('s', ['data']);
    $result = $query->execute()->fetchObject();

    return unserialize($result->data);
  }

  /**
   * Get the enabled fields from account settings
   */
  private function setEnabledFields($fields) {
    foreach ($fields as $machineName => $field) {
      if (!$field['status']) unset($fields[$machineName]);
    }

    $this->enabledFields = $fields;
  }

  /**
   * Load the user fields
   */
  private function loadUsers($id = NULL) {
    $role = $this->checkUserEntityRoleEnabled();

    $this->setEnabledFields($role['fields']);

    $query = db_select('users_roles', 'ur');

    if ($id != NULL) {
      $query->condition('ur.uid', $id);
      $query->condition('ur.rid', $role['role']);
    }
    else {
      $query->condition('ur.rid', $role['role']);
    }

    $query->fields('ur', ['uid' => 'uid']);

    $results = $query->execute();

    if (!$results->rowCount()) {
      return FALSE;
    }

    $data = [];

    if ($id) {
      $user = user_load($results->fetchObject()->uid);
      $data = $this->mapAuthorFields($user);
    }
    else {
      foreach ($results as $row) {
        $user = user_load($row->uid);
        $data[] = $this->mapAuthorFields($user);
      }
    }

    return $data;
  }

  /**
   * Map the enabled fields to the returning object
   *
   * @param $user
   *   the user object
   *
   * @return array of objects || object
   */
  private function mapAuthorFields($user) {
    $d = new stdClass();
    $ln = LANGUAGE_NONE;

    foreach ($this->enabledFields as $machineName => $field) {
      if ($machineName == 'mail') {
        $d->{$field['mapto']} = $user->{$machineName};
      }
      else {
        $d->{$field['mapto']} = isset($user->{$machineName}[$ln])
        ? $field['mapto'] != 'icon'
          ? $user->{$machineName}[$ln][0]['value']
          : file_create_url($user->{$machineName}[$ln][0]['uri'])
        : '';
      }
    }

    return $d;
  }

  /**
   * Prepare a new user object for saving
   *
   * @param $data
   *   the request post data
   */
  private function prepareNewUser($data) {
    $userName = strtolower($data['lastName']) . strtolower($data['firstName']);
    $mail = $data['email'];
    $ln = LANGUAGE_NONE;

    $role = $this->checkUserEntityRoleEnabled();
    $this->setEnabledFields($role['fields']);

    $newUser = [
      'name' => $userName,
      'password' => rand(),
      'mail' => $mail,
      'status' => 1,
      'init' => $mail,
      'roles' => [
        DRUPAL_AUTHENTICATED_RID => 'authenticated user',
      ],
    ];

    foreach ($this->enabledFields as $machineName => $field) {
      if ($machineName != 'mail') {
        if ($field['mapto'] != 'icon') {
          $newUser[$machineName][$ln][0]['value'] = !empty($data[$field['mapto']]) ? $data[$field['mapto']] : '';
        }
        else {
          $icon = file_get_contents($data[$field['mapto']]);
          $file = file_save_data($icon, NULL, FILE_EXISTS_REPLACE);
          $newUser[$machineName][$ln][0]['fid'] = $file->fid;
        }
      }
    }

    return $newUser;
  }
}


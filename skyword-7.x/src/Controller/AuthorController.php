<?php

include 'BaseController.php';
include 'ControllerInterface.php';

class AuthorController extends BaseController {
  private $authorFields = ['id', 'firstName', 'lastName', 'email', 'byline', 'icon'];

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
        return parent::buildData($users, $fields ? parent::extractFields($fields) : $this->authorFields);
      }
      else {
        return services_error(t('There are no authors found.'), 404);
      }
    }
    catch (Exception $e) {
      return services_error(t('Unable to query authors table'), 500);
    }
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $fields = NULL) {
    try {
      $user = $this->loadUsers($id);

      if ($user) {
        return $this->buildData($user, $fields ? parent::extractFields($fields) : $this->authorFields, FALSE);
      }
      else {
        return services_error(t("No author of id #$id was found."), 404);
      }
    }
    catch (Exception $e) {
      return services_error(t('Unable to query authors table'), 500);
    }
  }

  /**
   * Create a Taxonomy
   */
  public function create($name, $description) {}

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
   * Load the user fields
   */
  private function loadUsers($id = NULL) {
    $role = $this->checkUserEntityRoleEnabled();
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
      $data = (object)[
        'firstName' => $user->field_first_name[LANGUAGE_NONE][0]['value'],
        'lastName' => $user->field_last_name[LANGUAGE_NONE][0]['value'],
        'email' => $user->mail,
        'id' => $user->uid,
        'byline' => $user->field_byline[LANGUAGE_NONE][0]['value'],
        'icon' => file_create_url($user->field_icon[LANGUAGE_NONE][0]['uri'])
      ];
    }
    else {
      foreach ($results as $row) {
        $user = user_load($row->uid);

        $data[] = (object)[
          'firstName' => $user->field_first_name[LANGUAGE_NONE][0]['value'],
          'lastName' => $user->field_last_name[LANGUAGE_NONE][0]['value'],
          'email' => $user->mail,
          'id' => $user->uid,
          'byline' => $user->field_byline[LANGUAGE_NONE][0]['value'],
          'icon' => file_create_url($user->field_icon[LANGUAGE_NONE][0]['uri'])
        ];
      }
    }

    return $data;
  }
}


<?php

include 'BaseController.php';
include 'ControllerInterface.php';

class AuthorController extends BaseController {
  protected $authorFields = ['id', 'firstName', 'lastName', 'email', 'byline', 'icon'];

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

      return parent::buildData($users, $fields ? parent::extractFields($fields) : $this->authorFields);
    }
    catch (Exception $e) {
      return services_error(t('Unable to query authors table index'), 500);
    }
  }

  /**
   * Retrieve a specific Taxonomy
   */
  public function retrieve($id, $fields = NULL) {}

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
  private function loadUsers() {
    $role = $this->checkUserEntityRoleEnabled();
    $query = db_select('users', 'u');
    $query->leftjoin('users_roles', 'ur', 'ur.uid = u.uid');
    $query->condition('ur.rid', $role['role']);
    $query->fields('ur', ['uid' => 'uid']);
    $results = $query->execute();

    $data = [];

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

    return $data;
  }
}


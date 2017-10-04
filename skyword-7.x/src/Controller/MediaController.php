<?php

include 'BaseController.php';

class MediaController extends BaseController {

  /**
   * Class constructor
   */
  public function __construct() {}

  /**
   * Get a list of media
   *
   * @param $page
   *   the default number of page to return
   * @param $per_page
   *   the default number of items per page to return
   * @param $fields
   *   a list of fields to include on the data result
   *
   * @return an array of media objects OR services error
   */
  public function index($page = 1, $per_page = 250, $fields = NULL) {
    try {
      return $this->getMedias();
    }
    catch (Exception $e) {
      return services_error(t('Cannot fetch all the medias.'), 500); 
    } 
  }

  /**
   * Retrieve a specific media
   *
   * @param $id
   *   the unique identifier of the media
   * @param $fields
   *   a list of fields to include on the data result
   *   default is set to NULL
   *
   * @return a media object OR services error
   */
  public function retrieve($id, $fields = NULL) {
    try {
      return $this->getMedias($id);
    }
    catch (Exception $e) {
      return services_error(t('Cannot fetch the media'), 500);
    } 
  }

  public function create() {}

  public function update() {}

  public function delete() {}

  /**
   * Construct a single media object
   *
   * @param $obj
   *   a reference to the query result object
   *
   * @return media object
   */
  private function buildSingleMedia($obj) {
    $file = new stdClass();
    $file->id = $obj->fid;
    $file->type = $obj->filemime;
    $file->url = file_create_url($obj->uri);

    return $file; 
  }

  /**
   * Construct a list of media objects
   *
   * @param $result
   *   a reference to the query result
   *
   * @return an array of media objects
   */
  private function buildMedias($result) {
    $files = [];

    foreach ($result as $file) {
      $f = new stdClass();
      $f->id = $file->fid;
      $f->type = $file->filemime;
      $f->url = file_create_url($file->uri);

      $files[] = $f;
    }

    return $files; 
  }

  /**
   * Wrapper method to get a list of media
   * OR single item media object
   *
   * @param $id
   *   the unique identifier of the media
   *
   * @return an array or a single object
   */
  private function getMedias($id = NULL) {
    $query = db_select('file_managed', 'f');

    if ($id != NULL) {
      $query->condition('f.fid', $id);
      $query->fields('f', ['fid', 'uri', 'filemime']);
      $obj = $query->execute()->fetchObject();

      return $this->buildSingleMedia($obj);
    }

    $query->fields('f', ['fid', 'uri', 'filemime']);
    $result = $query->execute()->fetchAll();

    return $this->buildMedias($result);
  }
}


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

  /**
  * Used to create a file.
  */
  public function create($file) {
    try {
      $headers = getallheaders();
      preg_match('/filename\=(\".*\")/', $headers['Content-Disposition'], $matches);

      // Adds backwards compatability with regression fixed in #1083242
      // $file['file'] can be base64 encoded file so we check whether it is
      // file array or file data.
      $file = $this->arg_value($file, 'file');
      $filename = rtrim($matches[1], '"');
      $filename = substr($filename, 1, strlen($filename));

      if (!isset($file['file']) || empty($file['filename'])) {
        return services_error(t("Missing data the file upload can not be completed"), 500);
      }

      // Sanitize the file extension, name, path and scheme provided by the user.
      $destination = empty($file['filepath'])
        ? file_default_scheme() . '://' . $file['filename']
        : $this->file_check_destination_uri($file['filepath']);

      $dir = drupal_dirname($destination);
      // Build the destination folder tree if it doesn't already exists.
      if (!file_prepare_directory($dir, FILE_CREATE_DIRECTORY)) {
        return services_error(t("Could not create destination directory for file."), 500);
      }

      // Write the file
      if (!$file_saved = file_save_data(base64_decode($file['file']), $destination)) {
        return services_error(t("Could not write file to destination"), 500);
      }

      if (isset($file['status']) && $file['status'] == 0) {
        // Save as temporary file.
        $file_saved->status = 0;
        file_save($file_saved);
      }
      else {
        // Required to be able to reference this file.
        file_usage_add($file_saved, 'services', 'files', $file_saved->fid);
      }

      return array(
        'id' => $file_saved->fid,
        'location' => $file_saved->uri,
      );
    } catch (Exeption $e) {
      watchdog_exception('skyword', $e);
      return array(
        'status' => 0
      );
    }
  }

  public function update() {}

  public function delete() {}


  /**
  * Used for helping with the posting data.
  */
  private function arg_value($data) {
    if (isset($data[$field]) && count($data) == 1 && is_array($data[$field])) {
      return $data[$field];
    }
    return $data;
  }

  /**
  * Check the file destination.
  */
  private function file_check_destination_uri($uri) {
    $scheme = strstr($uri, '://', TRUE);
    $path = $scheme ? substr($uri, strlen("$scheme://")) : $uri;

    // Sanitize the file extension, name, path and scheme provided by the user.
    $scheme = _services_file_check_destination_scheme($scheme);
    $path = _services_file_check_destination($path);
    return "$scheme://$path";
  }

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
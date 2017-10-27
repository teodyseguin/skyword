<?php

namespace Drupal\skyword\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
 
/**
 * Skyword Controller Class
 */
class SkywordController extends ControllerBase {

  /**
   * Proxy for simple oauth's /ouath/token path.
   *
   * @param $request
   *   The request object containing request payload.
   */
  public function token(Request $request) {
    global $base_url;

    $payload = Json::decode($request->getContent());

    $response = \Drupal::httpClient()->post($base_url . '/oauth/token', [
      'verify' => TRUE,
      'form_params' => [
        'grant_type' => 'password',
        'client_id' => $payload['client_id'],
        'client_secret' => $payload['client_secret'],
        'scope' => 'skyword',
        'username' => 'admin',
        'password' => 'admin1234',
      ],
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
    ])->getBody()->getContents();

    return new JsonResponse(Json::decode($response));
  }

}

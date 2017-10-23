<?php

/**
 * This is a code sample for how to upload file to drupal services.
 *
 * This code sample focuses on uploading files and assumes that you are successfully autheticated.
 */

$httpMethod = 'POST';
$resourceUrl = 'http://www.skyword.local:32769/skyword/publish/v1/media';
$headers = array(
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ABC-MNO-XYZ',
    'Content-Disposition: attachment; filename="drupalIcon.png"'
);

$filePath = "./images/drupalIcon.png";
$requestFields = array(
    'filename'      => basename($filePath),
    'filesize'      => filesize($filePath),
    'file'          => base64_encode(file_get_contents($filePath)),
);


$datastring = json_encode($requestFields);
$headers[] = 'Content-Length: ' . strlen($datastring);
$curl = curl_init($resourceUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FAILONERROR, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLINFO_HEADER_OUT, true);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);
$response = curl_exec($curl);
curl_close($curl);

echo "Response: " . print_r($response, true) . "\n\r";

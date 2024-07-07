<?php

require 'vendor/autoload.php';

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
$client = new Client();

// Prevent CORS error
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

// let's disable this OPTION method for now to get rid of the error
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
  die();
}

try {

  $canLoadMore = true;
  $queryAfter = "";
  $contacts = [];
  $headers = apache_request_headers();

  // Throw an error if there's no Authorization attached to the headers
  if(!isset($headers["Authorization"])){
    throw new Exception("Invalid access token");
  }

  // let's get the Authoriation from headers
  $bearer_token = $headers["Authorization"];

  // We need to get all the contacts in one go so we created a loop
  // this loop will merge all the result contacts to 1 variable
  // we limit it to 100 because this is the maximum limit we can set for our HS account
  while ($canLoadMore) {
    $response = $client->get('https://api.hubapi.com/crm/v3/objects/contacts?properties=closedate,email,firstname,lastname&limit=100' . $queryAfter, [
      "headers" => [
        "Authorization" => $bearer_token
      ]
    ]);
    $response_data = json_decode((string) $response->getBody());
  
    if(isset($response_data -> paging)){
      $batch_contacts = $response_data -> results;
      $contacts = array_merge($contacts, $batch_contacts);
      $queryAfter = "&after=" . $response_data -> paging -> next -> after;
    }else{
      $canLoadMore =false;
    }
  }
  
  header('Content-Type: application/json');
  echo json_encode(["results" => $contacts]);

} catch (ClientException $e) {
  header('Content-Type: application/json');
  $response = $e->getResponse();
  $responseBodyAsString = $response->getBody()->getContents();

  $status_code = $response->getStatusCode();
  // set whatever returned status code
  http_response_code($status_code);
  echo $responseBodyAsString;

} catch (Exception $e) {
  http_response_code(500);
  echo 'Message: ' .$e->getMessage();
}


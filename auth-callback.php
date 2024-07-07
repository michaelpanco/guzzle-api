<?php

require 'vendor/autoload.php';
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

// Our HS client ID
$client_id = "89554a62-1a3b-4b93-a4dc-633004b21b40";
// Our HS client secret
$client_secret = "b6ef646d-6efe-4692-95f3-f05df8057099";

try {

  // Prevent CORS error
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');
  header("Access-Control-Allow-Headers: X-Requested-With");

  // throw an error when user requesting outside POST method
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception("Invalid request");
  }

  // Retrieve the raw POST data
  $jsonData = file_get_contents('php://input');
  // Decode the JSON data into a PHP associative array
  $data = json_decode($jsonData, true);
  // map the code value
  $code = $data["code"];
  $client = new Client();
  
  // Connect to Hubspot API with the code and credentials
  $response = $client->post('https://api.hubapi.com/oauth/v1/token', [
    "form_params" => [
      "grant_type" => "authorization_code",
      "client_id" => $client_id,
      "client_secret" => $client_secret,
      "code" => $code,
      "redirect_uri" => "http://localhost:3000/auth-callback"
    ]
  ]);

  $ok = $response->getReasonPhrase();
  if($ok !== "OK"){
    throw new Exception("Invalid");
  }
  
  header('Content-Type: application/json');
  echo $response->getBody();

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


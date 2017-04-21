<?php
  // SITE CONFIG
  $host_name = "http://tnc.example.com/";
  $site_title = "TotallyNotCorrent";
  $reload_assets = true;
  $debug_mode = false;
  $permissions = array(
    0 => "User",
    90 => "Admin",
    100 => "Owner"
  );
  $file_prefix = "[TNC] ";
  $fail_max = strval(10);
  $site_name = "TotallyNotCorrent";

  $test_file = "FILE ID";

  // DATABASE SETTINGS
  $db_username = ''; // MySQL username
  $db_password = ''; // MySQL password
  $db_hostname = ''; // MySQL host
  $db_name = 'google';
  $db = mysqli_connect($db_hostname, $db_username, $db_password, $db_name) or die("{\"status\":0,\"content\":\"Failed to connect to database\"}");

  // GOOGLE API
  $google_primary = "";
  $oauth_client = "";
  $oauth_secret = "";
  $scopes = array(
    "https://www.googleapis.com/auth/drive",
    "https://www.googleapis.com/auth/userinfo.profile",
    "https://www.googleapis.com/auth/userinfo.email"
  );

  $client = new Google_Client();
  foreach ($scopes as $scope) {
    $client->addScope($scope);
  }
  $client->setClientId($oauth_client);
  $client->setClientSecret($oauth_secret);
  $client->setDeveloperKey($google_primary);
  $client->setRedirectUri($host_name."session.php");
?>

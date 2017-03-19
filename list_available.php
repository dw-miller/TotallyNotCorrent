<?php
  include("assets/includes/google-api/vendor/autoload.php");
  include("assets/includes/config.php");
  try {
    session_start();
    $client->setAccessToken($_SESSION["token"]);
    $oauth = new Google_Service_Oauth2($client);
    $drive = new Google_Service_Drive($client);
    $dump = $oauth->userinfo->get();
    $list_options = array();
    $list_options["fields"] = "files(id,md5Checksum,name,permissions/id,size,trashed,webContentLink,webViewLink)";
    $files_list = $drive->files->listFiles($list_options)->getFiles();
    $return_val = array();
    $return_val["status"] = 1;
    $return_val["content"] = array();
    $return_val["content"]["count"] = 0;
    $return_val["content"]["files"] = array();
    foreach ($files_list as $file) {
      if ($file->trashed) {
        continue;
      }
      $is_public = false;
      foreach ($file->permissions as $permission) {
        if ($permission->id == "anyoneWithLink") {
          $is_public = true;
          break;
        }
      }
      if ($is_public) {
        $single_file = array();
        $single_file["id"] = $file->id;
        $single_file["name"] = $file->name;
        $single_file["size"] = $file->size;
        array_push($return_val["content"]["files"], $single_file);
      }
    }
    $return_val["content"]["count"] = count($return_val["content"]["files"]);
    die(json_encode($return_val));
  } catch (Exception $e) {
    die("{\"status\":0,\"content\":\"Failed to start session\"}");
  }
?>

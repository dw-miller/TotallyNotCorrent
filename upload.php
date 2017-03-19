<?php
  include("assets/includes/google-api/vendor/autoload.php");
  include("assets/includes/config.php");
  try {
    session_start();
    $client->setAccessToken($_SESSION["token"]);
    $oauth = new Google_Service_Oauth2($client);
    $drive = new Google_Service_Drive($client);
    $dump = $oauth->userinfo->get();
    if (isset($_GET["id"])) {
      try {
        $list_options = array();
        $list_options["fields"] = "id,md5Checksum,name,permissions/id,size,trashed,webContentLink,webViewLink";
        $found_file = $drive->files->get($_GET["id"], $list_options);
        $is_public = false;
        foreach ($found_file->permissions as $permission) {
          if ($permission->id == "anyoneWithLink") {
            $is_public = true;
            break;
          }
        }
        if (!($is_public)) {
          die("{\"status\":0,\"content\":\"File is not public\"}");
        } else {
          $old_id = $found_file->id;
          $file_key = substr(str_replace("+", "_", str_replace("/", "_", str_replace("=", "-", base64_encode(md5($old_id, true))))), 0, -2);
          $key_check = $db->query("SELECT * FROM files WHERE prime_key='$file_key'");
          if (mysqli_num_rows($key_check) > 0) {
            die("{\"status\":0,\"content\":\"File already exists. Contact an admin if you believe this is an error\"}");
          }
          $size = strval($found_file->size);
          $owner = $dump->id;
          $hash = $found_file->md5Checksum;
          $listed = "0";
          $name = $db->real_escape_string($found_file->name);
          try {
            $db->query("INSERT INTO files (prime_key, owner, hash, name, size, listed) VALUES ('$file_key', '$owner', '$hash', '$name', $size, $listed)");
            try {
              $copied = new Google_Service_Drive_DriveFile();
              $copied->setName($file_prefix.$name);
              $new_file = $drive->files->copy($found_file->id, $copied);
              $permission = new Google_Service_Drive_Permission();
              $permission->setRole("reader");
              $permission->setType("anyone");
              $drive->permissions->create($new_file->id, $permission);
              $new_id = $new_file->id;
              try {
                $db->query("INSERT INTO mirrors (owner, parent, id) VALUES ('$owner', '$file_key', '$new_id')");
                die("{\"status\":1,\"content\":\"$file_key\"}");
              } catch (Exception $i) {
                die("{\"status\":0,\"content\":\"Failed to mirror file to database\"}");
              }
            } catch (Exception $h) {
              die("{\"status\":0,\"content\":\"Failed to copy file\"}");
            }
          } catch (Exception $g) {
            die("{\"status\":0,\"content\":\"Failed to add file to database\"}");
          }
        }
      } catch (Exception $f) {
        die("{\"status\":0,\"content\":\"Invalid file ID\"}");
      }
    } else {
      die("{\"status\":0,\"content\":\"Invalid file ID\"}");
    }
  } catch (Exception $e) {
    die("{\"status\":0,\"content\":\"Failed to start session\"}");
  }
?>

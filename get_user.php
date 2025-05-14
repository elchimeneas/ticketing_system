<?php
include 'config/database.php';
include 'models/User.php';

header('Content-Type: application/json');

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = [];

if ($user_id > 0) {
  $database = new Database();
  $db = $database->getConnection();
  $user_model = new User($db);
  $user = $user_model->readOne($user_id);
  if ($user) {
    $response = $user;
  }
}

echo json_encode($response);

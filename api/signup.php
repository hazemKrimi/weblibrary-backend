<?php
    include_once "../config/core.php";
    cors();

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    include_once "../config/Database.php";
    include_once "../models/User.php";

    $database = new Database();
    $db = $database->connect();
    $user = new User($db);
    $data = json_decode(file_get_contents("php://input"));
    $user->username = $data->username;
    $user->email = $data->email;
    $user->password = $data->password;
    $user->type = !empty($data->type) ? $data->type : "reader";
    if (empty($user->username) || empty($user->email) || empty($user->password) || !$user->create()) {
        http_response_code(400);
        echo json_encode(["message" => "Sign Up Failed"]);
    } else {
        http_response_code(200);
        echo json_encode(["message" => "Sign Up Successful"]);
    }
?>
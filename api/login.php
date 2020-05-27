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
    $user->email = $data->email;

    include_once '../libs/php-jwt-master/src/BeforeValidException.php';
    include_once '../libs/php-jwt-master/src/ExpiredException.php';
    include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt-master/src/JWT.php';
    use \Firebase\JWT\JWT;

    if ($user->email_exists() && password_verify($data->password, $user->password)) {
        $token = array(
            "iss" => $iss,
            "aud" => $aud,
            "iat" => $iat,
            "nbf" => $nbf,
            "data" => array(
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email,
                "type" => $user->type
            )
        );
        http_response_code(200);
        $jwt = JWT::encode($token, $key);
        echo json_encode(
            array(
                "message" => "Login Successful",
                "jwt" => $jwt
            )
        );
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Login Failed"]);
    }
?>
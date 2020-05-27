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

    include_once '../libs/php-jwt-master/src/BeforeValidException.php';
    include_once '../libs/php-jwt-master/src/ExpiredException.php';
    include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt-master/src/JWT.php';
    use \Firebase\JWT\JWT;

    $jwt = isset($data->jwt) ? $data->jwt : "";
    if ($jwt) {
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $user->id = $decoded->data->id;
            $user->username = $data->username;
            $user->email = $data->email;
            $user->password = $data->password;
            if ($user->update()) {
                $token = array(
                    "iss" => $iss,
                    "aud" => $aud,
                    "iat" => $iat,
                    "nbf" => $nbf,
                    "data" => array(
                        "id" => $user->id,
                        "username" => !empty($user->username) ? $user->username : $decoded->data->username,
                        "email" => !empty($user->email) ? $user->email : $decoded->data->email,
                        "type" => $decoded->data->type
                    )
                );
                $jwt = JWT::encode($token, $key);
                http_response_code(200);
                echo json_encode(
                    array(
                        "message" => "User Update Successful",
                        "jwt" => $jwt
                    )
                );
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "User Update Failed"));
            }
        } catch(Exception $e) {
            http_response_code(401);
            echo json_encode(array(
                "message" => "Access Denied",
                "error" => $e->getMessage()
            ));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Access Denied"));
    }
?>
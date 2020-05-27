<?php
    include_once '../config/core.php';
    cors();

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    include_once "../config/Database.php";
    include_once "../models/Book.php";
    
    $database = new Database();
    $db = $database->connect();
    $book = new Book($db);
    define('MB', 1048576);
    
    include_once '../libs/php-jwt-master/src/BeforeValidException.php';
    include_once '../libs/php-jwt-master/src/ExpiredException.php';
    include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt-master/src/JWT.php';
    use \Firebase\JWT\JWT;

    $jwt = isset($_POST["jwt"]) ? $_POST["jwt"] : "";
    $book_name_extension = explode(".", $_FILES["book"]["name"]);

    if($jwt) {
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            if ($decoded->data->type != "author") {
                http_response_code(401);
                echo json_encode(array("message" => "Access Denied"));
            } else if (strtolower(end($book_name_extension)) != "pdf") {
                http_response_code(400);
                echo json_encode(array("message" => "Book Format Should Be PDF"));
            } else if ($_FILES['book']['size'] > 10 * MB) {
                http_response_code(400);
                echo json_encode(array("message" => "Max File Size Exceeded"));
            } else {
                $book->author = $decoded->data->id;
                $book->name = $_POST["name"];
                $book->path = str_replace(" ", "_", "/books/{$book->name}.pdf");
                if ($book->create()) {
                    move_uploaded_file($_FILES["book"]["tmp_name"], "..".$book->path);
                    http_response_code(200);
                    echo json_encode(array("message" => "Book Creation Successful"));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Book Creation Failed"));
                }
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
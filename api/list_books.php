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
    
    include_once '../libs/php-jwt-master/src/BeforeValidException.php';
    include_once '../libs/php-jwt-master/src/ExpiredException.php';
    include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt-master/src/JWT.php';
    use \Firebase\JWT\JWT;

    $data = json_decode(file_get_contents("php://input"));
    $jwt= isset($data->jwt) ? $data->jwt : "";

    if($jwt) {
        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            $res = $book->list();
            $num = $res->rowCount();
            if ($num > 0) {
                $books = array();
                while($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    array_push($books, array(
                        "id" => $id,
                        "authorId" => $author,
                        "authorName" => $username,
                        "name" => $name,
                        "path" => $path
                    ));
                }
                http_response_code(200);
                echo json_encode($books);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "No Books Found"));
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
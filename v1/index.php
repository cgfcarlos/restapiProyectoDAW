<?php
// CABECERAS E INCLUDES
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: application/json; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

include_once '../include/Config.php';
require '../libs/vendor/slim/slim/Slim/Slim.php';
require_once '../include/DBController.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// FUNCIONES DE LA API

$app->get('/products', function() use ($app) {
    
    $response = array();
    $db = new DBController();

    $query = "SELECT * FROM productos";

    $db->query($query);
    $products = $db->resultset();

    $response["products"] = $products;
    $response['error'] = false;
    $response['message'] = "Productos cargados: " . count($products);

    echoResponse(200, $response);

});

$app->post('/products', 'authenticate', function() use ($app) {

    verifyRequiredParams(array('titulo', 'precio', 'categoria', 'imgproducto'));

    $response = array();
    $db = new DBController();

    $body = json_decode($app->request()->getBody(), true);

    if(isset($body["titulo"]) && isset($body["precio"]) && isset($body["categoria"]) && isset($body["imgproducto"])){
        $query = "INSERT INTO productos (titulo, precio, categoria, imgproducto) VALUES ('".$body['titulo']."', ".$body['precio'].", ".$body['categoria'].",'".$body['imgproducto']."')";

        $db->query($query);
        $db->execute();

        $lastInsertId = $db->lastInsertId();

        if(isset($lastInsertId)) {
            $response["product"] = $body;
            $response["error"] = false;
            $response["message"] = "Producto añadido correctamente";

            echoResponse(201, $response);
        } else {
            $response["input"] = $input;
            $response["error"] = true;
            $response["message"] = "Ha ocurrido un error al añadir el producto";

            echoResponse(400, $response);
        }
    } else {
        $response["error"] = true;
        $response["message"] = "Ha ocurrido un error al añadir el producto";

        echoResponse(400, $response);
    }
});

$app->put('/products/:id', 'authenticate', function($id) use ($app) {

    verifyRequiredParams(array('titulo', 'precio', 'categoria', 'imgproducto'));

    $response = array();
    $db = new DBController();

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        parse_str($app->request()->getBody());
    }

    $query = "UPDATE productos SET titulo = '".$titulo."', precio = ".$precio.", categoria = '".$categoria."', imgproducto = '".$imgproducto."' WHERE idproducto LIKE ". intval($id);

    $db->query($query);
    $db->execute();

    $updatedRows = $db->rowCount();

    if(isset($updatedRows) && $updatedRows>0) {
        $response["error"] = false;
        $response["message"] = "Se ha modificado el producto correctamente";
        $response["rows"] = $updatedRows;

        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "No ha sido posible modificar el producto";

        echoResponse(400, $response);
    }
});

$app->delete('/products/:id', 'authenticate', function($id) {
    $response = array();

    $db = new DBController();

    $query = "DELETE FROM productos WHERE idproducto LIKE ".$id;

    $db->query($query);
    $db->execute();

    $deletedRows = $db->rowCount();

    if(isset($deletedRows) && $deletedRows > 0) {
        $response["error"] = false;
        $response["message"] = "Se ha eliminado un producto";
        $response["rows"] = $deletedRows;

        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "No ha sido posible eliminar el producto";
        
        echoResponse(400, $response);
    }
});

$app->get('/products/:id', function($id) {
    $response = array();

    $db = new DBController();

    $query = "SELECT * FROM productos WHERE idproducto LIKE ". intval($id);

    $db->query($query);

    $product = $db->single();


    if($product !== false){

        $response["product"] = $product; 
        $response["error"] = false;
        $response["message"] = "Producto cargado ". $id;
        echoResponse(200, $response);

    } else {

        $response["error"] = true;
        $response["message"] = "No se ha encontrado el producto";

        echoResponse(400, $response);
    }
    
});

$app->get('/categories', function() {
    $response = array();

    $db = new DBController();

    $query = "SELECT * FROM categorias";

    $db->query($query);
    $categories = $db->resultset();

    $response['categories'] = $categories;
    $response['error'] = false;
    $response['message'] = 'Categorias cargadas correctamente: '. count($categories);
    echoResponse(200, $response);
});

$app->get('/categories/:id', function($id) {
    $response = array();

    $db = new DBController();

    $query = "SELECT *  FROM categorias WHERE idcategoria LIKE ". intval($id);

    $db->query($query);
    $category = $db->single();

    if($category !== false) {
        $response['category'] = $category;
        $response["error"] = false;
        $response["message"] = "Categoría cargada correctamente";

        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "No se ha encontrado la categoría";

        echoResponse(400, $response);
    }
});

$app->get('/orders', function() {
    $response = array();

    $db = new DBController();

    if(!isset($body["userId"])){
        $query = "SELECT * FROM pedidos";

        $db->query($query);
        $orders = $db->resultset();

        $response["orders"] = $orders;
        $response["error"] = false;
        $response["message"] = "Pedidos cargados correctamente: ".count($orders);

        echoResponse(200, $response);
    } else {
        $query = "SELECT * FROM pedidos WHERE usuarioid = ".$body["userId"];

        $db->query($query);
        $orders = $db->resultset();

        $response["orders"] = $orders;
        $response["error"] = false;
        $response["message"] = "Pedidos cargados correctamente: ".count($orders);

        echoResponse(200, $response);
    }
});

$app->get('/orders/:id', function($id) {
    $response = array();

    $db = new DBController();

    $query = "SELECT * FROM pedidos WHERE idpedidos LIKE ". intval($id);

    $db->query($query);
    $order = $db->single();

    if($order !== false) {
        $response["order"] = $order;
        $response["error"] = false;
        $response["message"] = "Pedido cargado correctamente";

        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "No se ha podido cargar el pedido";

        echoResponse(400, $response);
    }
});

$app->post('/orders', 'authenticate', function() use ($app) {

    verifyRequiredParams(array('fechapedido','usuarioid','direccion1','ciudad','nombredestinatario'));

    $response = array();
	$body = json_decode($app->request()->getBody(), true);

    $db = new DBController();

    if(isset($body["fechapedido"]) && isset($body["usuarioid"]) && isset($body["direccion1"]) && isset($body["ciudad"]) && isset($body["nombredestinatario"])) {

        $query = "INSERT INTO pedidos (fechapedido, usuarioid, direccion1, direccion2, ciudad, nombredestinatario) 
        VALUES ('".$body["fechapedido"]."',".intval($body["usuarioid"]).",'".$body["direccion1"]."','".$body["direccion2"]."','".$body["ciudad"]."','".$body["nombredestinatario"]."')";

        $db->query($query);
        $db->execute();

        $idPedido = $db->lastInsertId();

        // $db->beginTransaction();
        // foreach($body['items'] as $items) {
        //     $query = "INSERT INTO lineapedido (idventa, idproducto, unidades)
        //     VALUES (".$idPedido.",".$items["id"].",".$items["cantidad"].")";
        //     $db->query($query);
        //     $db->execute();
        // }
        // $db->endTransaction();

        if(isset($idPedido)) {
            $response["order"] = $body;
            $response["error"] = false;
            $response["message"] = "Pedido realizado correctamente";
            
            echoResponse(200, $response);
        } else {
            $response["error"] = true;
            $response["message"] = "No se ha podido realizar el pedido";

            echoResponse(400, $response);
        }
    } else {
        $response["error"] = true;
        $response["message"] = "No se ha podido realizar el pedido";

        echoResponse(400, $response);
    }
});

$app->get('/users', function() use ($app) {
    $response = array();
    $db = new DBController();

    $query = "SELECT * FROM users";

    $db->query($query);
    $users = $db->resultset();

    $response["users"] = $users;
    $response['error'] = false;
    $response['message'] = "Usuarios cargados: " . count($response);

    echoResponse(200, $response);
});

$app->get('/users/:id', function($id) {
    $response = array();

    $db = new DBController();

    $query = "SELECT * FROM users WHERE id LIKE ". intval($id). " OR oauth_uid = '".$id."'";

    $db->query($query);
    $user = $db->single();
    $rows = $db->rowCount();

    if($rows !== 0) {
        $response["user"] = $user;
        $response["error"] = false;
        $response["message"] = "Usuario cargado correctamente";

        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "No se ha encontrado el usuario";

        echoResponse(400, $response);
    }
});

$app->post('/login', function() use ($app) {
    $response = array();
    $db = new DBController();
	
	$body = json_decode($app->request->getBody(), true);

    verifyRequiredParams(array('provider', 'id', 'name', 'email', 'image', 'token', 'idToken'));

    $queryCheck = "SELECT * FROM users WHERE oauth_uid = '".$body['id']."'";

    $db->query($queryCheck);
    $user = $db->single();
    $rows = $db->rowCount();

    if($rows === 0){
        $query = "INSERT INTO users (oauth_provider, oauth_uid, first_name, email, picture, token, id_token)
        VALUES ('".$body['provider']."', '".$body['id']."', '".$body['name']."', '".$body['email']."', '".$body['image']."', '".$body["token"]."', '".$body['idToken']."')";

        $db->query($query);
        $db->execute();
        $id = $db->lastInsertId();

        $query = "SELECT * FROM users WHERE oauth_uid LIKE '".$body['id']."'";
        $db->query($query);
        $user = $db->single();

        if(isset($id)){
            $response["error"] = false;
            $response["message"] = "Usuario registrado en la BD";
            $response["user"] = $body;

            echoResponse(200, $response);
        } else {
            $response["error"] = true;
            $response["message"] = "Ha ocurrido un error al registrar el usuario";
            $response["input"] = $body;

            echoResponse(400, $response);
        }
    } else {
        $query = "UPDATE users SET token = '".$body['token']."', id_token = '".$body['idToken']."' WHERE oauth_uid = '".$body['id']."'";
        
        $db->query($query);
        $db->execute();
        $rows = $db->rowCount();

        $response["error"] = false;
        $response["user"] = $body;
        $response["message"] = "Usuario obtenido de la BD";
        $response["updated"] = $rows;

        echoResponse(200, $response);
    }
});

$app->post('/logout', function() use ($app) {
    $response = array();

    $db = new DBController();
	
	$body = json_decode($app->request()->getBody(), true);

    //verifyRequiredParams(array('id'));

    $queryCheck = "SELECT * FROM users WHERE oauth_uid = '".$body['id']."'";

    $db->query($queryCheck);
    $user = $db->single();
    $rows = $db->rowCount();

    if($rows === 1) {
        $query = "UPDATE users SET token = NULL, id_token = NULL WHERE oauth_uid = '".$body['id']."'";
        $db->query($query);
        $db->execute();
        $rows = $db->rowCount();

        if($rows === 1) {
            $response["error"] = false;
            $response["message"] = "Usuario ha cerrado sesion";

            echoResponse(200,$response);
        } else {
            $response["error"] = true;
            $response["message"] = "Ha ocurrido un error al cerrar sesion";
            echoResponse(400,$response);
        }
    } else {
        $response["error"] = true;
        $response["message"] = "No se ha encontrado el usuario";
        echoResponse(400,$response);
    }

});

$app->get('/shoppingCarts', function() use ($app) {
    $response = array();
    $db = new DBController();

    $query = "SELECT * FROM shoppingcart";

    $db->query($query);
    $shoppingCarts =  $db->resultset();

    if($db->rowCount() > 0){
        $response["error"] = false;
        $response["shoppingCarts"] = $shoppingCarts;
        $response["message"] = "Carritos de compra mostrados correctamente, cantidad: ".$db->rowCount();
        echoResponse(200,$response);
    } else {
        $response["error"] = true;
        $response["message"] = "Error al intentar mostrar los carritos de compra";
        echoResponse(400,$response);
    }
});

$app->get('/shoppingCarts/:id', function() use ($app) {
    $response = array();
    $db = new DBController();

    $query = "SELECT * FROM shoppingcart WHERE shoppingcartid = ".intval($id)."";

    $db->query($query);
    $shoppingCart = $db->single();

    if($db->rowCount() === 1) {
        $response["error"] = false;
        $response["shoppingCart"] = $shoppingCart;
        $response["message"] = "Carrito cargado correctamente";
        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Error a la hora de retornar carrito";
        echoResponse(400, $response);
    }
});

$app->run();

/* FUNCIONES DE USO COMÚN */

/* FUNCION PARA COMPROBAR LOS PARAMETROS OBLIGATORIOS */

function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
    
    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        
        $app->stop();
    }
}

/* FUNCION DE ENVIO DE RESPUESTA EN FORMATO JSON */

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();

    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}

/*FUNCION DE AUTENTICACION*/

function authenticate(\Slim\Route $route) {
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    if(isset($headers['Authorization'])) {
        $token = $headers['Authorization'];

        if(!($token == API_KEY)) {
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);

            $app->stop();
        } else {

        }
    } else {
        $response["error"] = true;
        $response["message"] = "Falta token de autorización";
        echoResponse(400, $response);

        $app->stop();
    }
}
?>
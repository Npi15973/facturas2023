<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');
$db = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'db' => 'api_facturacion' //Cambiar al nombre de tu base de datos
];


  //Abrir conexion a la base de datos
  function connect($db)
  {
      try {
          $conn = new PDO("mysql:host={$db['host']};dbname={$db['db']}", $db['username'], $db['password']);

          // set the PDO error mode to exception
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          return $conn;
      } catch (PDOException $exception) {
          exit($exception->getMessage());
      }
  }


 //Obtener parametros para updates
 function getParams($input)
 {
    $filterParams = [];
    foreach($input as $param => $value)
    {
            $filterParams[] = "$param=:$param";
    }
    return implode(", ", $filterParams);
	}

  //Asociar todos los parametros a un sql
	function bindAllValues($statement, $params)
  {
		foreach($params as $param => $value)
    {
				$statement->bindValue(':'.$param, $value);
		}
		return $statement;
   }



$dbConn =  connect($db);

/*
  listar todos los posts o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['id']))
    {
      //Mostrar un post
      $sql = $dbConn->prepare("SELECT * FROM usuarios where id=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
	  }
    else {
      //Mostrar lista de post
      $sql = $dbConn->prepare("SELECT * FROM usuarios");
      $sql->execute();
      $sql->setFetchMode(PDO::FETCH_ASSOC);
      header("HTTP/1.1 200 OK");
      echo json_encode( $sql->fetchAll()  );
      exit();
	}
}

// Crear un nuevo post
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  include 'ctr_xml.php';
  include 'ctr_firmarxml.php';
  $JSONData = file_get_contents("php://input");
  $dataObject = json_decode($JSONData);
  $input = (array) $dataObject;
    $id          = $input['id'];
    $iduser = $id;
    $cantidad    = $input['cantidad'];
    $descripcion = $input['descripcion'];
    $preciou     = $input['preciou'];
    $descuento   = $input['descuento'];
    $celular_receptor   = $input['celular_receptor'];
    $direccion_receptor   = $input['direccion_receptor'];
    $direccion = $direccion_receptor;
    $correo                   =            $input['correo'];
    $email_receptor = $correo;
    $tipo_identificacion      = $input['tipo_identificacion'];
    $numero_cedula_receptor   = $input['numero_cedula_receptor'];
    $nombres_receptor         = $input['nombres_receptor'];
    $descuento   = $input['descuento'];
    $sql = $dbConn->prepare("SELECT * FROM usuarios
       where id=$id");
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $nombres        = $result['nombres'];
    $nombre_empresa = $result['nombre_empresa'];
    $direccion_emisor      = $result['direccion'];
    $numero_identidad_emisor = $result['numero_identidad'];
      $firma_electronica2 = $result['firma_electronica'];
      $codigo_sri = $result['codigo_sri'];
    $img_logo = $result['img_logo'];
    $porcentaje_iva_f      = $result['porcentaje_iva_f'];
    $estableciminento_f = str_pad($result['estableciminento_f'], 3, "0", STR_PAD_LEFT);
    $punto_emision_f = str_pad($result['punto_emision_f'], 3, "0", STR_PAD_LEFT);


     //FECHAS
     $fecha_actual = date("d-m-Y");
     $fecha =  str_replace("-","/",date("d-m-Y",strtotime($fecha_actual." - 0 hours")));
     $fecha_emision_factura2 = $fecha_actual = date("d-m-Y h:m:s");
     $fecha_emision =  date("d-m-Y h:m:s",strtotime($fecha_actual." +0 hours"));
     //IFORMACION DEL USUARIO
     $email_emisor = $result['email'];

     //INFORMACION DEL SECUENCIAL
     $sql_secuencial = $dbConn->prepare("SELECT * FROM comprobante_factura_final
        where id_emisor=$id ORDER BY fecha DESC");
     $sql_secuencial->execute();
     $result_secuenciial = $sql_secuencial->fetch(PDO::FETCH_ASSOC);
     if ($result_secuenciial) {
       $secuencial = $result_secuenciial['codigo_factura'];
       $secuencial = $secuencial +1;
       // code...
     }else {
       $secuencial =1;
     }

     $numeroConCeros = str_pad($secuencial, 9, "0", STR_PAD_LEFT);
     $secuencial = $numeroConCeros;
     //INOFORMACION PARA SACAR EL CODIGO
     $codigo	    =$secuencial.$id;

     //INFORMACION FINANCIERO
     $precio_88_iva = round(($preciou*$cantidad),2);
     $preciot = $precio_88_iva;
     $subtotal = $preciot;
     $total = $preciot;
     $iva12 = $total -$preciot;
     $precio_12_iva = $iva12;
     $precio_total = round(($cantidad*$preciou)*$porcentaje_iva_f/10,2);
     $precio_neto = $precio_total;



    $xmlf=new xml();
    $xmlf->xmlFactura($fecha,$correo,$secuencial,$codigo,$cantidad,$descripcion,$preciou,$descuento,$preciot,$subtotal,$iva12,$total,$numero_cedula_receptor,$nombres_receptor,$nombre_empresa,$direccion,$tipo_identificacion,$estableciminento_f,$punto_emision_f,$porcentaje_iva_f,$numero_identidad_emisor);

    $xmla=new autorizar();
    $xmla->autorizar_xml($fecha,$correo,$porcentaje_iva_f,$nombre_empresa,$img_logo,$direccion_emisor,$numero_identidad_emisor,$estableciminento_f,$punto_emision_f,$fecha_emision,$nombres_receptor,$numero_cedula_receptor,$precio_88_iva,$precio_12_iva,$precio_total,$direccion_receptor,$celular_receptor,$email_receptor,$email_emisor,$secuencial,$numeroConCeros,$iduser,$firma_electronica2,$codigo_sri);


    header("HTTP/1.1 200 OK");
    //echo json_encode($result);
    exit();
}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
	$id = $_GET['id'];
  $statement = $dbConn->prepare("DELETE FROM posts where id=:id");
  $statement->bindValue(':id', $id);
  $statement->execute();
	header("HTTP/1.1 200 OK");
	exit();
}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{
    $input = $_GET;
    $postId = $input['id'];
    $fields = getParams($input);

    $sql = "
          UPDATE posts
          SET $fields
          WHERE id='$postId'
           ";

    $statement = $dbConn->prepare($sql);
    bindAllValues($statement, $input);

    $statement->execute();
    header("HTTP/1.1 200 OK");
    exit();
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

?>

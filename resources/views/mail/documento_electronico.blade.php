<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Documento electronico</title>
</head>
<body>
    <h5>Estimado/a, {{$cliente->name}} ha recibído un documento electrónico de parte de : {{$emisor->nombre_comercial}}</h5>
    <p>Este es un correo enviado de forma automática, no responda al mismo!</p>
</body>
</html>
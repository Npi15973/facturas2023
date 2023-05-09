<?php
$secuencial='000045240';
$secuencial++;
//echo $secuencial.'<br>';
//echo '051120200117913454440011001001'.$secuencial.'123456781';
$fecha= '15/11/2022';
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<fieldset>
  <legend>Datos de la Venta</legend>
  <form action="../controladores/ctr_venta.php" method="post">
  <table id="tablePreview" class="table table-striped table-hover table-sm table-bordered">
  <thead>
    <tr>
      <th colspan="4"><img src="../img/logo.png" width="100">MI EMPRESA COMERCIAL S.A <br>
          FACTURA N° 001-001-<?php echo '<input type="text" name="secuencial" value="'.$secuencial.'" readonly>'?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="2"><center><b>MI EMPRESA COMERCIAL S.A</b><center></td>
      <td><b>RUC</b></td>
      <td>1723124234001</td>
    </tr>
    <tr>
      <td><b>Direccion<br>Matriz</b></b></td>
      <td>AV. Quito</td>
      <td><b>Telefono</b></td>
      <td>0222451258</td>
    </tr>
    <tr>
      <td><b>Direccion<br>Sucursal</b></td>
      <td>AV. Quito</td>
      <td><b>Correo</b></td>
      <td>empresa@dominio.com</td>
    </tr>
  </tbody>
  </table>

<table id="tablePreview" class="table table-striped table-hover table-sm table-bordered">
  <thead>
  <tr>
    <th>Cliente:</th>
    <th>Cliente Prueba</th>
    <th>Direccion</th>
    <th>Quito</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>Fecha Emision:</td>
    <td><input type="text" name="fecha" value="<?php echo $fecha?>" readonly></td>
    <td>Correo</td>
    <td><input type="text" name="correo"></td>
  </tr>
</tbody>
</table>
<table id="tablePreview" class="table table-striped table-hover table-sm table-bordered">
<thead>
  <tr>
    <th>Codigo Pr.</th>
    <th>Cantidad</th>
    <th>DESCRIPCION</th>
    <th>Precio Uni.</th>
    <th>Dscto.</th>
    <th>Precio Total</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td><input type="text" name="codigo"></td>
    <td><input type="text" name="cantidad"></td>
    <td><input type="text" name="descripcion"></td>
    <td><input type="text" name="preciou"></td>
    <td><input type="text" name="descuento"></td>
    <td><input type="text" name="preciot"></td>
  </tr>
  <tr>
    <td colspan="4" rowspan="5"></td>
    <td>SUBTOTAL</td>
    <td><input type="text" name="subtotal"></td>
  </tr>
  <tr>
    <td>IVA 0%</td>
    <td><input type="text" name="iva0"></td>
  </tr>
  <tr>
    <td>IVA 12</td>
    <td><input type="text" name="iva12"></td>
  </tr>
  <tr>
    <td>DESCUENTO</td>
    <td><input type="text" name="descuento"></td>
  </tr>
  <tr>
    <td>VALOR TOTAL</td>
    <td><input type="text" name="total"></td>
  </tr>
</tbody>
</table>
<input type="submit" value="Generar Venta" name="submit">
</form>
</fieldset>

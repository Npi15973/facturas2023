<?php
$emisor = DB::table('emisor')->where('is_active',1)->first();
//$documentoModificado = DB::table('tipo_documento_electronico')->where('codigo',$infoGuiaRemision->codDocModificado)->first();
$documento = DB::table('tipo_documento_electronico')->where('codigo',$infoTributaria->codDoc)->first();
?>
<html>
<head>
    <meta charset="utf-8"> 
    <title>Propiedad display</title> 
    <style>
        .a { display: none; }
        .b { display: inline; width: 100px; height: 50px;}
        .c { display: block; }
        .d { display: inline-block; width: 349px;}
        p  { color: purple; border: dotted;}

        
    </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;">  

<section>
    <div class="c" style="margin: 10px;">
        <br>
    </div>
</section>
<section>
    <br/>
    <div>
        <div class="d">
            <center>
            <img style="
            padding: 5px;
            width: 150px;
            height: 150px;" src="<?php echo e($emisor->logo); ?>" alt=""> 
            </center>
            <div style="border-style: solid; border-width: 1px; padding: 10px; border-radius: 25px;  margin-right: 10px;">
                <table>
                    <tr>
                        <td>
                            <?php echo e($infoTributaria->razonSocial); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Dir Matriz:</strong> <?php echo e($infoTributaria->dirMatriz); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Dir Establecimiento:</strong> <?php echo e($infoGuiaRemision->dirEstablecimiento); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Contribuyente especial: </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>OBLIGADO CONTABILIDAD:</strong> <?php echo e($infoGuiaRemision->obligadoContabilidad); ?>

                        </td>
                    </tr>
                </table>
            </div>
        </div> 
        <div class="d">
            <div style="border-style: solid; border-width: 1px; padding: 10px; border-radius: 25px;">
                <table>
                    <tr>
                        <td>
                            <strong>RUC: </strong> <?php echo e($infoTributaria->ruc); ?>

                        </td>
                    </tr>
                </table>
                <div style="background-color:#0B3861; color:white; font-size: 18px;" >
                    <center> <?php echo e($documento->name); ?> </center>
                </div>
                <table>
                    <tr>
                        <td>
                            N°: <?php echo e($infoTributaria->estab); ?>-<?php echo e($infoTributaria->ptoEmi); ?>-<?php echo e($infoTributaria->secuencial); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            NÚMERO DE AUTORIZACIÓN: 
                        </td>
                    </tr>
                    <tr>
                        <?php if($fechaAutorizacion!=null || $fechaAutorizacion!=""): ?>
                       
                                <?php if($estado=="autorizado"): ?>
                                <td><?php echo e($infoTributaria->claveAcceso); ?> </td>
                                <?php else: ?>
                                <td style="background-color: rgb(171, 185, 179);">ANULADO</td>
                                <?php endif; ?>
                        <?php else: ?>
                        <td style="background-color: yellowgreen;">PENDIENTE</td>
                        <?php endif; ?>
                        
                    </tr>
                    <tr>
                        <td>
                            FECHA  AUTORIZACIÓN: 
                        </td>
                    </tr>
                    <tr>
                        
                            <?php if($fechaAutorizacion!=null || $fechaAutorizacion!=""): ?>
                                
                                    <td><?php echo e($fechaAutorizacion); ?> </td>
                                
                            
                            <?php else: ?>
                            <td style="background-color: yellowgreen;">PENDIENTE</td>
                            <?php endif; ?>
                            
                        
                    </tr>
                    <tr>
                        <td>
                            AMBIENTE: <?php if($infoTributaria->ambiente=="1") echo "PRUEBAS"; else echo "PRODUCCIÓN"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            EMISIÓN: <?php if($infoTributaria->tipoEmision=="1") echo "NORNMAL"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            CLAVE DE ACCESO
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img style=" border: 1px solid #ddd;
                            border-radius: 4px;
                            padding: 5px;
                            width: 300px;" src="public/barcode/prueba.png" alt="">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo e($infoTributaria->claveAcceso); ?>

                        </td>
                    </tr>
                </table>
            </div>    
        </div> 
    </div>
</section>
    
<section>
    <div style="margin-top: -35px">
        <div class="c" style="border-style: solid; border-width: 1px; padding: 10px;">
            <div class="c">
                <table>
                    <tr>
                        <td>
                            Razón Social / Nombres y Apellidos / Transportista: <?php echo e($infoGuiaRemision->razonSocialTransportista); ?>

                        </td>                       
                    </tr>
                </table>
            </div>
            <div class="d" style="margin-top: 10px;">
                <table>
                    
                    <tr>
                        <td>
                            Identificación:
                        </td>
                        <td>
                            <?php echo e($infoGuiaRemision->rucTransportista); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Punto partida: 
                        </td>
                        <td>
                            <?php echo e($infoGuiaRemision->dirPartida); ?>

                        </td>
                        <td>
                            Placa: 
                        </td>
                        <td>
                            <?php echo e($infoGuiaRemision->placa); ?>

                        </td>
                    </tr>
                </table>
            </div>
           <div class="c">
                <table>
                   
                    <tr>
                        <td>
                            Fecha Inicio : <?php echo e($infoGuiaRemision->fechaIniTransporte); ?>

                        </td>
                        <td>
                            Fecha Fin: <?php echo e($infoGuiaRemision->fechaFinTransporte); ?>

                        </td>
                    </tr>
                </table>
           </div>
        </div> 
    </div>
   
</section>


<section>
    <?php
    $motivo_traslado ="";
    $destino ="";
    $identificacion="";
    $razonSocial="";
        foreach($destinatarios->destinatario as $key => $value){
            $motivo_traslado=$value->motivoTraslado;
            $destino= $value->dirDestinatario;
            $identificacion = $value->identificacionDestinatario;
            $razonSocial = $value->razonSocialDestinatario;
        }
            
        
    ?>
    <div style="margin-top: 35px">
        <div class="c" style="border-style: solid; border-width: 1px; padding: 10px;">
            <div class="c">
                <table>
                    <tr>
                        <td>
                            Motivo traslado : <?php echo e($motivo_traslado); ?>

                        </td>                       
                    </tr>
                </table>
            </div>
            <div class="d" style="margin-top: 10px;">
                <table>
                    
                    <tr>
                        <td>
                            Destino Cliente:
                        </td>
                        <td>
                            <?php echo e($destino); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Identificación: 
                        </td>
                        <td>
                            <?php echo e($identificacion); ?>

                        </td>
                        
                    </tr>
                    <tr>
                        <td>
                            Razón Social / Nombre y Apellidos: 
                        </td>
                        <td>
                            <?php echo e($razonSocial); ?>

                        </td>
                    </tr>
                </table>
            </div>
        </div> 
    </div>
   
</section>


<section>
    <div class="c" style="margin-top: 20px;">
        <table style="border: 1px solid #0B3861; width: 100%;">
            <thead style="color:white; border: 1px solid #0B3861; background-color:#0B3861">
                <tr>
                    <th>
                        Código Interno
                    </th>
                    <th>
                        Código Adicional
                    </th>
                    <th>
                        Descripción
                    </th>
                    <th>
                        Cantidad
                    </th>
                </tr>
            </thead>
            
            <tbody>
                <?php $__currentLoopData = $detalles->detalle; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
     
                <tr>
                    <td>
                        
                        <?php echo e($value->codigoInterno); ?>

                    </td>
                    <td>
                        
                    </td>
                    <td>
                        <?php echo e($value->codigoInterno); ?>

                    </td>
                    <td>
                        <?php echo e($value->cantidad); ?>

                    </td>
  
    
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </tbody>
            
        </table>
    </div>
    <div class="c">
        <div style="width: 500px; display: inline-block; margin-top: 30px;">
            <div style="padding-top: 10px;">
                <table style="border: 1px solid black;width: 100%;">
                    <tr style="background-color: #0B3861; color: white; ">
                        <th colspan="2">Informacion Adicional</th>
                    </tr>
                    <tr>
                        <td>
                            Email:
                        </td>
                        <td>
                            <?php echo e($infoAdicional->campoAdicional[0]); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Teléfono:
                        </td>
                        <td>
                            <?php echo e($infoAdicional->campoAdicional[1]); ?>

                        </td>
                    </tr>

                </table>
            </div>
           
           
            
        </div>
        
    </div>
</section>


 

   
</body>
</html>
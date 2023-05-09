<?php
$emisor = DB::table('emisor')->where('is_active',1)->first();
$documentoModificado = DB::table('tipo_documento_electronico')->where('codigo',$infoNotaDebito->codDocModificado)->first();
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
                            <strong>Dir Establecimiento:</strong> <?php echo e($infoNotaDebito->dirEstablecimiento); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Contribuyente especial: </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>OBLIGADO CONTABILIDAD:</strong> <?php echo e($infoNotaDebito->obligadoContabilidad); ?>

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
                            Razón Social / Nombres y Apellidos: <?php echo e($infoNotaDebito->razonSocialComprador); ?>

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
                            <?php echo e($infoNotaDebito->identificacionComprador); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Fecha Emisión: 
                        </td>
                        <td>
                            <?php echo e($fechaEmision); ?>

                        </td>
                    </tr>
                </table>
            </div>
           <div class="c">
                <table>
                    <tr>
                        <td>
                            Comprobante que se modifica  : <?php echo e($documentoModificado->name); ?> - <?php echo e($infoNotaDebito->numDocModificado); ?>

                        </td>
                        <td>
                            Fecha emisión comprobante :  <?php echo e($infoNotaDebito->fechaEmisionDocSustento); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Razó Modificacion : <?php echo e($infoNotaDebito->motivo); ?>

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
                        Descripción
                    </th>
                    <th>
                        Valor
                    </th>
                </tr>
            </thead>
            
            <tbody>
                <?php $__currentLoopData = $motivos->motivo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          
                <tr>
                    <td>
                        
                        <?php echo e($value->razon); ?>

                    </td>
                    <td>
                        <?php echo e($value->valor); ?>

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
        <div style="width: 280px; display: inline-block; margin-top: -16px;">
            
            <table style="border: 1px solid black;">
                
                <tr>
                    <td>
                        IMPORTE TOTAL
                    </td>
                    <td>
                   
                        <?php echo e(bcadd($valorTotal,'0',2)); ?>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</section>


 

   
</body>
</html>
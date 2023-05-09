@php
$emisor = DB::table('emisor')->where('is_active',1)->first();
@endphp
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
            height: 150px;" src="{{$emisor->logo}}" alt=""> 
            </center>
            <div style="border-style: solid; border-width: 1px; padding: 10px; border-radius: 25px;  margin-right: 10px;">
                <table>
                    <tr>
                        <td>
                            {{$infoTributaria->razonSocial}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Dir Matriz:</strong> {{$infoTributaria->dirMatriz}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Dir Establecimiento:</strong> {{$infoLiquidacionCompra->dirEstablecimiento}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Contribuyente especial: </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>OBLIGADO CONTABILIDAD:</strong> {{$infoLiquidacionCompra->obligadoContabilidad}}
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
                            <strong>RUC: </strong> {{$infoTributaria->ruc}}
                        </td>
                    </tr>
                </table>
                <div style="background-color:#0B3861; color:white; font-size: 18px;" >
                    <center> LIQUIDACIÓN DE COMPRA DE BIENES Y PRESTACIÓNDE SERVICIOS</center>
                </div>
                <table>
                    <tr>
                        <td>
                            N°: {{$infoTributaria->estab}}-{{$infoTributaria->ptoEmi}}-{{$infoTributaria->secuencial}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            NÚMERO DE AUTORIZACIÓN: 
                        </td>
                    </tr>
                    <tr>
                        @if($fechaAutorizacion!=null || $fechaAutorizacion!="")
                        <td>{{$infoTributaria->claveAcceso}} </td>
                        @else
                        <td style="background-color: yellowgreen;">PENDIENTE</td>
                        @endif
                        
                    </tr>
                    <tr>
                        <td>
                            FECHA  AUTORIZACIÓN: 
                        </td>
                    </tr>
                    <tr>
                        
                            @if($fechaAutorizacion!=null || $fechaAutorizacion!="")
                            <td>{{$fechaAutorizacion}} </td>
                            @else
                            <td style="background-color: yellowgreen;">PENDIENTE</td>
                            @endif
                            
                        
                    </tr>
                    <tr>
                        <td>
                            AMBIENTE: @php if($infoTributaria->ambiente=="1") echo "PRUEBAS"; else echo "PRODUCCIÓN"; @endphp
                        </td>
                    </tr>
                    <tr>
                        <td>
                            EMISIÓN: @php if($infoTributaria->tipoEmision=="1") echo "NORNMAL"; @endphp
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
                            {{$infoTributaria->claveAcceso}}
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
                            Razón Social / Nombres y Apellidos: {{$infoLiquidacionCompra->razonSocialProveedor}}
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
                            {{$infoLiquidacionCompra->identificacionProveedor}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Fecha Emisión: 
                        </td>
                        <td>
                            {{$fechaEmision}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Dirección: 
                        </td>
                        <td>
                            {{$infoLiquidacionCompra->direccionProveedor}}
                        </td>
                    </tr>
                </table>
            </div>
           <div class="d">
                <table>
                    <tr>
                        <td>
                            
                        </td>
                        <td>

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
                        CódigoPrincipal
                    </th>
                    <th>
                        Código Auxiliar
                    </th>
                    <th>
                        Cantidad
                    </th>
                    <th>
                        Descripción
                    </th>
                    <th>
                        Precio Unitario
                    </th>
                    <th>
                        Descuento
                    </th>
                    <th>
                        Total SinImpuestos
                    </th>
                </tr>
            </thead>
            
            <tbody>
                @foreach ($detalles->detalle as $key => $value)
          
                <tr>
                    <td>
                        
                        {{$value->codigoPrincipal}}
                    </td>
                    <td>
                        {{$value->codigoAuxiliar}}
                    </td>
                    <td>
                        {{$value->cantidad}}
                    </td>
                    <td>
                        {{$value->descripcion}}
                    </td>
                    <td>
                        {{$value->precioUnitario}}
                    </td>
                    <td>
                        {{$value->descuento}}
                    </td>
                    <td>
                        {{$value->precioTotalSinImpuesto}}
                    </td>
    
                </tr>
                @endforeach

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
                            {{$infoAdicional->email}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Teléfono:
                        </td>
                        <td>
                            {{$infoAdicional->phone_number}}
                        </td>
                    </tr>
                    
                </table>
            </div>
           
           <div style="padding-top: 10px;">
              
               @php
               $pagos = $infoLiquidacionCompra->pagos;
               $pago = $pagos[0]->pago;
               $descripcionPago ="";
               foreach ($formaPagos as $item)
                {
                    if($pago->formaPago==$item->codigo){
                        $descripcionPago = $item->descripcion;
                    }
                }
               
               @endphp
               
            <table style="border: 1px solid black;width: 100%;">
                <tr style="background-color: #0B3861; color: white; ">
                    <th>Forma de pago</th>
                    <th>Valor</th>
                    <th>Plazo</th>
                    <th>Tiempo</th>
                </tr>
                <tr>
                    <td>
                        {{$descripcionPago}}
                    </td>
                    <td>
                        {{$pago->total}}
                    </td>
                    <td>
                        {{$plazo}}
                    </td>
                    <td>
                        días
                    </td>
                </tr>
            </table>
           </div>
            
        </div>
        <div style="width: 280px; display: inline-block; margin-top: -16px;">
            @php
                $totalImpuesto = $totalConImpuestos;
                $subtotal12=0;
                $subtital0=0;
                $impuesto="";
                $impuestoGenerado = 0;
                foreach ($totalImpuesto->totalImpuesto as $key => $item){

                   
                    if($item->codigo=="2" && $item->codigoPorcentaje=="2"){
                        $subtotal12 = $subtotal12 + $item->baseImponible;
                    }
                    if($item->codigo=="2" && $item->codigoPorcentaje=="0"){
                        $subtital0 = $subtital0 + $item->baseImponible;
                    }
                    $impuestoGenerado  = $impuestoGenerado + $item->valor;
                
                }
                $subTotalSinImpuesto = $subtotal12+$subtital0;
            @endphp
            <table style="border: 1px solid black;">
                <tr>
                    <td>
                        SUBTOTAL 12%
                    </td>
                    <td>
                        {{bcadd($subtotal12,'0',2)}}
                       
                    </td>
                </tr>
                <tr>
                    <td>
                        SUBTOTAL 0%
                    </td>
                    <td>
                        {{bcadd($subtital0,'0',2)}}
                       
                    </td>
                </tr>
                <tr>
                    <td>
                        SUBTOTAL no objeto de IVA
                    </td>
                    <td>
                        00.00
                    </td>
                </tr>
                <tr>
                    <td>
                        SUBTOTAL exento de IVA
                    </td>
                    <td>
                        00.00
                    </td>
                </tr>
                <tr>
                    <td>
                        SUBTOTAL SIN IMPUESTOS
                    </td>
                    <td>
                        
                        {{bcadd($infoLiquidacionCompra->totalSinImpuestos,'0',2)}}
                    </td>
                </tr>
                <tr>
                    <td>
                        TOTAL Descuento
                    </td>
                    <td>
                      
                        {{bcadd($infoLiquidacionCompra->totalDescuento,'0',2)}}
                    </td>
                </tr>
                <tr>
                    <td>
                        ICE
                    </td>
                    <td>
                        0.00
                    </td>
                </tr>
                <tr>
                    <td>
                        IVA 12%
                    </td>
                    <td>
             
                        {{bcadd($impuestoGenerado,'0',2)}}
                    </td>
                </tr>
                <tr>
                    <td>
                        IMPORTE TOTAL
                    </td>
                    <td>
                   
                        {{bcadd($infoLiquidacionCompra->importeTotal,'0',2)}}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</section>


 

   
</body>
</html>
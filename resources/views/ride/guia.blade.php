@php
$emisor = DB::table('emisor')->where('is_active',1)->first();
//$documentoModificado = DB::table('tipo_documento_electronico')->where('codigo',$infoGuiaRemision->codDocModificado)->first();
$documento = DB::table('tipo_documento_electronico')->where('codigo',$infoTributaria->codDoc)->first();
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
                            <strong>Dir Establecimiento:</strong> {{$infoGuiaRemision->dirEstablecimiento}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Contribuyente especial: </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>OBLIGADO CONTABILIDAD:</strong> {{$infoGuiaRemision->obligadoContabilidad}}
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
                    <center> {{$documento->name}} </center>
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
                       
                                @if($estado=="autorizado")
                                <td>{{$infoTributaria->claveAcceso}} </td>
                                @else
                                <td style="background-color: rgb(171, 185, 179);">ANULADO</td>
                                @endif
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
                            Razón Social / Nombres y Apellidos / Transportista: {{$infoGuiaRemision->razonSocialTransportista}}
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
                            {{$infoGuiaRemision->rucTransportista}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Punto partida: 
                        </td>
                        <td>
                            {{$infoGuiaRemision->dirPartida}}
                        </td>
                        <td>
                            Placa: 
                        </td>
                        <td>
                            {{$infoGuiaRemision->placa}}
                        </td>
                    </tr>
                </table>
            </div>
           <div class="c">
                <table>
                   
                    <tr>
                        <td>
                            Fecha Inicio : {{$infoGuiaRemision->fechaIniTransporte}}
                        </td>
                        <td>
                            Fecha Fin: {{$infoGuiaRemision->fechaFinTransporte}}
                        </td>
                    </tr>
                </table>
           </div>
        </div> 
    </div>
   
</section>


<section>
    @php
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
            
        
    @endphp
    <div style="margin-top: 35px">
        <div class="c" style="border-style: solid; border-width: 1px; padding: 10px;">
            <div class="c">
                <table>
                    <tr>
                        <td>
                            Motivo traslado : {{$motivo_traslado}}
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
                            {{$destino}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Identificación: 
                        </td>
                        <td>
                            {{$identificacion}}
                        </td>
                        
                    </tr>
                    <tr>
                        <td>
                            Razón Social / Nombre y Apellidos: 
                        </td>
                        <td>
                            {{$razonSocial}}
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
                @foreach ($detalles->detalle as $key => $value)
     
                <tr>
                    <td>
                        
                        {{$value->codigoInterno}}
                    </td>
                    <td>
                        
                    </td>
                    <td>
                        {{$value->codigoInterno}}
                    </td>
                    <td>
                        {{$value->cantidad}}
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
                            {{$infoAdicional->campoAdicional[0]}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Teléfono:
                        </td>
                        <td>
                            {{$infoAdicional->campoAdicional[1]}}
                        </td>
                    </tr>

                </table>
            </div>
           
           
            
        </div>
        
    </div>
</section>


 

   
</body>
</html>
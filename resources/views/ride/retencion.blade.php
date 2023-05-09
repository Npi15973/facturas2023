@php
$emisor = DB::table('emisor')->where('is_active',1)->first();
//$documentoModificado = DB::table('tipo_documento_electronico')->where('codigo',$infoCompRetencion->codDocModificado)->first();
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
                            <strong>Dir Establecimiento:</strong> {{$infoCompRetencion->dirEstablecimiento}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Contribuyente especial: </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>OBLIGADO CONTABILIDAD:</strong> {{$infoCompRetencion->obligadoContabilidad}}
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
                            Razón Social / Nombres y Apellidos: {{$infoCompRetencion->razonSocialSujetoRetenido}}
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
                            {{$infoCompRetencion->identificacionSujetoRetenido}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Fecha Emisión: 
                        </td>
                        <td>
                            {{$infoCompRetencion->fechaEmision}}
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
                        Número Factura 
                    </th>
                    <th>
                        Ejercicio Fiscal 
                    </th>
                    <th>
                        Base Imponible para la
                        Retención 
                    </th>
                    <th>
                        Impuesto 
                    </th>
                    <th>
                        Cod Retención 
                    </th>
                    <th>
                        Porcentaje Retención
                    </th>
                    <th>
                        Total Sin Impuestos
                    </th>
                </tr>
            </thead>
            
            <tbody>
                @foreach ($impuestos->impuesto as $key => $value)
                @php
                    $impuesto1 = DB::table('tipo_impuesto')->where('codigo',$value->codigo)->first();
                @endphp
                <tr>
                    <td>
                        
                        {{$value->numDocSustento}}
                    </td>
                    <td>
                        {{$infoCompRetencion->periodoFiscal}}
                    </td>
                    <td>
                        {{$value->baseImponible}}
                    </td>
                    <td>
                        
                        {{$impuesto1->name}}
                    </td>
                    <td>
                        {{$value->codigoRetencion}}
                    </td>
                    <td>
                        {{$value->porcentajeRetener}}
                    </td>
                    <td>
                        {{$value->valorRetenido}}
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
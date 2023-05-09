<?php

namespace App\Services;

use App\Customer;
use App\Emisor;
use App\FormaDePago;
use App\Guia;
use App\Librerias\FacturacionElectronica;
use App\NotaCredito;
use App\NotaDebito;
use App\Purchase;
use App\Retencion;
use App\Sale;
use App\Supplier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PDF;
use Illuminate\Support\Facades\File;
/**
 * Service Lecturas
 */
class ProcesarDocumentoService
{

    public $facturacionElectronica;
    private $facturacionElectronicaService;

    public function __construct(FacturacionElectronica $facturacionElectronica, FacturacionElectronicaService $facturacionElectronicaService){

      $this->facturacionElectronica=$facturacionElectronica;
      $this->facturacionElectronicaService= $facturacionElectronicaService;
    }

    public function procesarComprobante($data){
        $id = $data["documentId"];
         $tipo = $data["documentType"];
   
         if(!$this->validarEmisor()){
          
           return "El emisor no se encuentra bien configurado";
         }
         $emisor = $this->obtenerEmison(); 
         $claveFirma= $emisor->password_firma;
         $firma= $emisor->firma;
         if($tipo == "01"){ // factura
           //return "factura";
           $venta = Sale::where('id',$id)->first();
           $nombre_documento = $venta->clave_acceso;
           
         }else if($tipo == "03"){ // liquidación de compra
           $liquidacion = Purchase::where('id',$id)->where('liquidacion',1)->first();
           $nombre_documento = $liquidacion->clave_acceso;
         }else if($tipo == "04"){ // nota crédito
           $nota_credito = NotaCredito::where('id',$id)->first();
           $nombre_documento = $nota_credito->clave_acceso;
          ;
         }else if($tipo == "05"){ // nota débito
           $nota_credito = NotaDebito::where('id',$id)->first();
           $nombre_documento = $nota_credito->clave_acceso;
           
         }else if($tipo == "06"){ // guía
           $guia = Guia::where('id',$id)->first();
           $nombre_documento = $guia->clave_acceso;
          
         }else if($tipo == "07"){ // retención
           $retencion = Retencion::where('id',$id)->first();
           $nombre_documento = $retencion->clave_acceso;
          
         }
      
         if($this->facturacionElectronica->firmarXml($claveFirma,$firma,$nombre_documento.'.xml')){
           $mensaje = $this->validarComprobante($tipo,$nombre_documento);
           return $mensaje;
     
         }else{
            return "Ha ocurrido un error al enviar el comprobante";
         }
         
       }
    public function validarEmisor(){
        try {
          $emisor = Emisor::where('is_active',1)->first();
          if($emisor){
            if(is_null($emisor->firma)){
              return response()->json(false);
            }else{
              return response()->json(true);
            }
          }else{
            return response()->json(false);
          }
          
        } catch (\Throwable $th) {
          return response()->json(false);
        }
        
      }
      public function obtenerEmison(){
        return Emisor::where('is_active',1)->first();
      }
      public function validarComprobante($documento, $claveAcceso){
        $message="";
        $emisor = Emisor::where('is_active',1)->first();
        try {
          $facturaXml = File::get(public_path('facturacion/facturacionphp/comprobantes/si_firmados/'.$claveAcceso.'.xml'));
          $result = $this->facturacionElectronicaService->enviarComprobanteSri($facturaXml,$emisor->ambiente);
          $someArray = json_decode($result, true);
          $resultado =  $someArray["RespuestaRecepcionComprobante"];
          if(isset($resultado["estado"])){
              if(trim($resultado["estado"])==="DEVUELTA"){
                 $comprobantes =  $resultado["comprobantes"];
                 foreach ($comprobantes as $key => $value) {
                    $mensajes =  $value["mensajes"];
                    foreach ($mensajes as $key => $mensaje) {
                       if($mensaje["tipo"]=="ERROR"){
                          if(trim($mensaje["mensaje"])=="CLAVE ACCESO REGISTRADA"){
                            $message =  $this->autorizarComprobante($documento,$claveAcceso);
                            break;
                          }else{
                            $message = $result;
                            break;
                          }    
                       }else{
                        $message =  "Ocurrio un error";
                        break;
                       }
                    }
                 }
                
              }else if (trim($resultado["estado"])==="RECIBIDA"){
                $message= $this->autorizarComprobante($documento,$claveAcceso);
              }else{
                $message = "Erro en la autorización: ". strval($resultado);
              }
            }else{
              $message="Error al conectarse al sri";
            }
          return $message;
        } catch (\Throwable $th) {
        
           return response()->json($th);
        }
       
      }
      public function autorizarComprobante($documento ,$claveAcceso ){
        $emisor = Emisor::where('is_active',1)->first();
        $estado ="";
    
        $result = $this->facturacionElectronicaService->autorizarComprobanteSri($claveAcceso,$emisor->ambiente); 
        $someArray = json_decode($result, true);
        $resultado =  $someArray["RespuestaAutorizacionComprobante"];
         $autorizaciones =  $resultado["autorizaciones"];
         if(is_array($resultado["autorizaciones"])){
          foreach ($resultado["autorizaciones"] as $key => $value) { 
            $estado =$value["estado"];
             if($estado=="AUTORIZADO"){ 
              $xml = $value["comprobante"];
              if($documento=="01"){// factura
                $sale = Sale::where('clave_acceso',$value["numeroAutorizacion"])->first();
                $idDocumento = $sale->id;
                $sale->fecha_autorizacion= date('Y-m-d');
                $sale->estado_fact_sri= "autorizado";
                $sale->save();
                
              }else if($documento=="03"){// liquidación compra
                $liquidacion = Purchase::where('clave_acceso',$value["numeroAutorizacion"])->where('liquidacion',1)->first();
                $idDocumento = $liquidacion->id;
                $liquidacion->fecha_autorizacion= date('Y-m-d');
                $liquidacion->estado_sri= "autorizado";
                $liquidacion->save();
  
                
              }else if($documento == "04"){ // nota crédito
                $nota_credito = NotaCredito::where('clave_acceso',$value["numeroAutorizacion"])->first();
                $idDocumento = $nota_credito->id;
                $nota_credito->fecha_autorizacion= date('Y-m-d');
                $nota_credito->estado_sri= "autorizado";
                $nota_credito->save();
              }else if($documento == "05"){ // nota débito
                $nota_debito = NotaDebito::where('clave_acceso',$value["numeroAutorizacion"])->first();
                $idDocumento = $nota_debito->id;
                $nota_debito->fecha_autorizacion= date('Y-m-d');
                $nota_debito->estado_sri= "autorizado";
                $nota_debito->save();
              }else if($documento == "06"){ // guía
                $guia = Guia::where('clave_acceso',$value["numeroAutorizacion"])->first();
                $idDocumento = $guia->id;
                $guia->fecha_autorizacion= date('Y-m-d');
                $guia->estado_sri= "autorizado";
                $guia->save();
              }else if($documento == "07"){ // retención
                $retencion = Retencion::where('clave_acceso',$value["numeroAutorizacion"])->first();
                $idDocumento = $retencion->id;
                $retencion->fecha_autorizacion= date('Y-m-d');
                $retencion->estado_sri= "autorizado";
                $retencion->save();
              }
              File::put(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$value["numeroAutorizacion"].'.xml') , $xml);
              //File::delete(public_path('facturacion/facturacionphp/comprobantes/si_firmados/'.$value["numeroAutorizacion"].'.xml'));
              //File::delete(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$value["numeroAutorizacion"].'.xml'));
               
               
              
                $this->GenerarRidePDF($idDocumento,$documento);
                $this->EnviarMailRidePDF($idDocumento,$documento);
             }else{
               $estado="NO AUTORIZADO";
             }
          }
  
         }else{
           $estado = $resultado;
         }
        
      
        return response()->json($estado);
      }

        /** 
     * GENERAR RIDE PDF
     */
    public function GenerarRidePDF($id,$documento){
        $dataXml="";
        $pdf="";
        $filename="";
        if($documento=="01"){ // factura
          $venta = Sale::where('id',$id)->select('clave_acceso','plazo','customer_id','fecha_autorizacion','fecha_emision')->first();
          if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
            //$dataXml = Storage::get('facturas/autorizados/'.$venta->clave_acceso.'.xml');
          }else{
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
           // $dataXml = Storage::get('facturas/'.$venta->clave_acceso.'.xml');
          }
          $dataComprobante = simplexml_load_string($dataXml);
    
          $data= array();
          $facturaInfo = $dataComprobante->infoFactura;
          $data['infoFactura'] = $dataComprobante->infoFactura;
          $data['infoTributaria'] = $dataComprobante->infoTributaria;
          $data['detalles'] = $dataComprobante->detalles;
          $data['totalConImpuestos'] = $facturaInfo->totalConImpuestos;
          $data["infoAdicional"]= Customer::where('id',$venta->customer_id) ->first();
          $data["formaPagos"]= FormaDePago::all();
          $data["fechaAutorizacion"]= $venta->fecha_autorizacion;
          $data["fechaEmision"]= $venta->fecha_emision;
          $data["plazo"]= $venta->plazo;
          //return $data;
          $pdf = PDF::loadView('ride.factura',$data);
          $filename = $venta->clave_acceso.".pdf";
          Storage::put('facturas/ride/'.$filename, $pdf->output());
        }else if($documento=="03"){ // liquidación de compras
          $liquidacion = Purchase::where('id',$id)->select('clave_acceso','supplier_id','fecha_autorizacion','fecha_emision','plazo')->first();
          if($liquidacion->fecha_autorizacion != null || $liquidacion->fecha_autorizacion != ""){
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$liquidacion->clave_acceso.'.xml'));
          }else{
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$liquidacion->clave_acceso.'.xml'));
          }
          $dataComprobante = simplexml_load_string($dataXml);
    
          $data= array();
          $liquidacionInfo = $dataComprobante->infoLiquidacionCompra;
          $data['infoLiquidacionCompra'] = $dataComprobante->infoLiquidacionCompra;
          $data['infoTributaria'] = $dataComprobante->infoTributaria;
          $data['detalles'] = $dataComprobante->detalles;
          $data['totalConImpuestos'] = $liquidacionInfo->totalConImpuestos;
          $data["infoAdicional"]= $dataComprobante->infoAdicional;//Supplier::where('id',$liquidacion->supplier_id) ->first();
          $data["formaPagos"]= FormaDePago::all();
          $data["fechaAutorizacion"]= $liquidacion->fecha_autorizacion;
          $data["fechaEmision"]= $liquidacion->fecha_emision;
          $data["plazo"]= $liquidacion->plazo;
          $pdf = PDF::loadView('ride.liquidacion',$data);
          $filename = $liquidacion->clave_acceso.".pdf";
          Storage::put('liquidacion/ride/'.$filename, $pdf->output());
        }else if($documento == "04"){ // nota crédito
  
          $notaCredito = NotaCredito::where('id',$id)->select('estado_sri','clave_acceso','customer_id','fecha_autorizacion','fecha_emision')->first();
          if($notaCredito->fecha_autorizacion != null || $notaCredito->fecha_autorizacion != ""){
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$notaCredito->clave_acceso.'.xml'));
          }else{
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$notaCredito->clave_acceso.'.xml'));
          }
          $dataComprobante = simplexml_load_string($dataXml);
    
          $data= array();
          $notaCreditoInfo = $dataComprobante->infoNotaCredito;
          $data["estado"] = $notaCredito->estado_sri;
          $data['infoNotaCredito'] = $dataComprobante->infoNotaCredito;
          $data['infoTributaria'] = $dataComprobante->infoTributaria;
          $data['detalles'] = $dataComprobante->detalles;
          $data['totalConImpuestos'] = $notaCreditoInfo->totalConImpuestos;
          $data["infoAdicional"]= $dataComprobante->infoAdicional;//Supplier::where('id',$liquidacion->supplier_id) ->first();
          $data["formaPagos"]= FormaDePago::all();
          $data["fechaAutorizacion"]= $notaCredito->fecha_autorizacion;
          $data["fechaEmision"]= $notaCredito->fecha_emision;
  
          $data["plazo"]= $notaCredito->plazo;
         // return $data;
          $pdf = PDF::loadView('ride.notaCredito',$data);
          $filename = $notaCredito->clave_acceso.".pdf";
          Storage::put('nota_credito/ride/'.$filename, $pdf->output());
        }else if($documento == "05"){ // nota débito
  
  
          $notaDebito = NotaDebito::where('id',$id)->select('estado_sri','clave_acceso','customer_id','fecha_autorizacion','fecha_emision')->first();
          if($notaDebito->fecha_autorizacion != null || $notaDebito->fecha_autorizacion != ""){
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$notaDebito->clave_acceso.'.xml'));
          }else{
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$notaDebito->clave_acceso.'.xml'));
          }
          $dataComprobante = simplexml_load_string($dataXml);
    
          $data= array();
          $infoNotaDebito = $dataComprobante->infoNotaDebito;
          $data["estado"] = $notaDebito->estado_sri;
          $data['infoNotaDebito'] = $dataComprobante->infoNotaDebito;
          $data['infoTributaria'] = $dataComprobante->infoTributaria;
          $data['motivos'] = $dataComprobante->motivos;
          $data['valorTotal'] = $infoNotaDebito->valorTotal;
          $data["infoAdicional"]= $dataComprobante->infoAdicional;//Supplier::where('id',$liquidacion->supplier_id) ->first();
          $data["formaPagos"]= FormaDePago::all();
          $data["fechaAutorizacion"]= $notaDebito->fecha_autorizacion;
          $data["fechaEmision"]= $notaDebito->fecha_emision;
  
          //return $data;
          $pdf = PDF::loadView('ride.notaDebito',$data);
          $filename = $notaDebito->clave_acceso.".pdf";
          Storage::put('nota_debito/ride/'.$filename, $pdf->output());
  
        }else if($documento == "06"){ // guía
  
          $guia = Guia::where('id',$id)->select('estado_sri','clave_acceso','customer_id','fecha_autorizacion','fecha_emision')->first();
          if($guia->fecha_autorizacion != null || $guia->fecha_autorizacion != ""){
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$guia->clave_acceso.'.xml'));
          }else{
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$guia->clave_acceso.'.xml'));
          }
          $dataComprobante = simplexml_load_string($dataXml);
    
          $data= array();
          $infoGuiaRemision = $dataComprobante->infoGuiaRemision;
          $destinatarios = $dataComprobante->destinatarios;
          $data["estado"] = $guia->estado_sri;
          $data['infoGuiaRemision'] = $dataComprobante->infoGuiaRemision;
          $data['infoTributaria'] = $dataComprobante->infoTributaria;
          $data['destinatarios'] = $dataComprobante->destinatarios;
          $data['valorTotal'] = $infoGuiaRemision->valorTotal;
          $data["infoAdicional"]= $dataComprobante->infoAdicional;//Supplier::where('id',$liquidacion->supplier_id) ->first();
          $data["formaPagos"]= FormaDePago::all();
          $data["fechaAutorizacion"]= $guia->fecha_autorizacion;
          $data["fechaEmision"]= $guia->fecha_emision;
          $data["detalles"] = $destinatarios->destinatario->detalles;
          //return $data;
          $pdf = PDF::loadView('ride.guia',$data);
          $filename = $guia->clave_acceso.".pdf";
          Storage::put('guia/ride/'.$filename, $pdf->output());
        }else if($documento == "07"){ // retención
  
          $retencion = Retencion::where('id',$id)->select('estado_sri','clave_acceso','sujeto_retenido','fecha_autorizacion','fecha_emision')->first();
          if($retencion->fecha_autorizacion != null || $retencion->fecha_autorizacion != ""){
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$retencion->clave_acceso.'.xml'));
          }else{
            $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$retencion->clave_acceso.'.xml'));
          }
          $dataComprobante = simplexml_load_string($dataXml);
    
          $data= array();
          $infoCompRetencion = $dataComprobante->infoCompRetencion;
       
          $data["estado"] = $retencion->estado_sri;
          $data['infoCompRetencion'] = $dataComprobante->infoCompRetencion;
          $data['infoTributaria'] = $dataComprobante->infoTributaria;
          $data['impuestos'] = $dataComprobante->impuestos;
   
          $data["infoAdicional"]= $dataComprobante->infoAdicional;//Supplier::where('id',$liquidacion->supplier_id) ->first();
          $data["formaPagos"]= FormaDePago::all();
          $data["fechaAutorizacion"]= $retencion->fecha_autorizacion;
          $data["fechaEmision"]= $retencion->fecha_emision;
  
          //return $data;
          $pdf = PDF::loadView('ride.retencion',$data);
          $filename = $retencion->clave_acceso.".pdf";
          Storage::put('retencion/ride/'.$filename, $pdf->output());
  
        }
        
        
        
       
      }
  
      public function EnviarMailRidePDF($id,$documento){
        
          try {
            if($documento=="01"){
              $factura = Sale::where('id',$id)->first();
              
              $dataPdf = Storage::get('facturas/ride/'.$factura->clave_acceso.'.pdf');
              $filename=$factura->clave_acceso.'.pdf';
              $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$factura->clave_acceso.'.xml'));
           
              $filenameXml=$factura->clave_acceso.'.xml';
              $mail_data = array();
              if($factura){
                
                $cliente = Customer::where('id',$factura->customer_id)->first();
                //return $cliente;
                $mail_data['email'] = $cliente->email;
                $mail_data["cliente"] = $cliente;
                $mail_data["emisor"] = Emisor::where('is_active',1)->first();
                
                        Mail::send( 'mail.documento_electronico', $mail_data, function( $message ) use ($mail_data,$dataPdf,$filename,$dataXml,$filenameXml)
                        {
                            $message->to($mail_data["email"])
                                      ->subject( 'Ha recibido un comprobante electrónico!' )
                                      ->attachData($dataPdf,$filename)
                                      ->attachData($dataXml,$filenameXml);
                        });
                     return   response()->json(true);
                }
                return   response()->json("no encontrado");
            }
            else if($documento=="03"){
              $liquidacion = Purchase::where('id',$id)->where('liquidacion',1)->first();
              $dataPdf = Storage::get('liquidacion/ride/'.$liquidacion->clave_acceso.'.pdf');
              $filename=$liquidacion->clave_acceso.'.pdf';
              $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$liquidacion->clave_acceso.'.xml'));

              $filenameXml=$liquidacion->clave_acceso.'.xml';
              $mail_data = array();
              if($liquidacion){
                $supplier = Supplier::where('id',$liquidacion->supplier_id)->first();
                $mail_data['email'] = $supplier->email;
                $mail_data["cliente"] = $supplier;
                $mail_data["emisor"] = Emisor::where('is_active',1)->first();
                        Mail::send( 'mail.documento_electronico', $mail_data, function( $message ) use ($mail_data,$dataPdf,$filename,$dataXml,$filenameXml)
                        {
                            $message->to($mail_data["email"])
                                      ->subject( 'Ha recibido un comprobante electrónico!' )
                                      ->attachData($dataPdf,$filename)
                                      ->attachData($dataXml,$filenameXml);
                        });
                        response()->json(true);
                }
            }
            else if($documento=="04"){ // nota crédito
              $nota_credito = NotaCredito::where('id',$id)->first();
              $dataPdf = Storage::get('nota_credito/ride/'.$nota_credito->clave_acceso.'.pdf');
              $filename=$nota_credito->clave_acceso.'.pdf';
              $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$nota_credito->clave_acceso.'.xml'));

            //  $dataXml = Storage::get('nota_credito/autorizados/'.$nota_credito->clave_acceso.'.xml');
              $filenameXml=$nota_credito->clave_acceso.'.xml';
              $mail_data = array();
              if($nota_credito){
                $cliente = Customer::where('id',$nota_credito->customer_id)->first();
                $mail_data['email'] = $cliente->email;
                $mail_data["cliente"] = $cliente;
                $mail_data["emisor"] = Emisor::where('is_active',1)->first();
                        Mail::send( 'mail.documento_electronico', $mail_data, function( $message ) use ($mail_data,$dataPdf,$filename,$dataXml,$filenameXml)
                        {
                            $message->to($mail_data["email"])
                                      ->subject( 'Ha recibido un comprobante electrónico!' )
                                      ->attachData($dataPdf,$filename)
                                      ->attachData($dataXml,$filenameXml);
                        });
                        return   response()->json(true);
                }
  
            }
            else if($documento=="05"){ // nota débito
              $nota_debito = NotaCredito::where('id',$id)->first();
              $dataPdf = Storage::get('nota_debito/ride/'.$nota_debito->clave_acceso.'.pdf');
              $filename=$nota_debito->clave_acceso.'.pdf';
              $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$nota_debito->clave_acceso.'.xml'));

              //$dataXml = Storage::get('nota_debito/autorizados/'.$nota_debito->clave_acceso.'.xml');
              $filenameXml=$nota_debito->clave_acceso.'.xml';
              $mail_data = array();
              if($nota_debito){
                $cliente = Customer::where('id',$nota_debito->customer_id)->first();
                $mail_data['email'] = $cliente->email;
                $mail_data["cliente"] = $cliente;
                $mail_data["emisor"] = Emisor::where('is_active',1)->first();
                        Mail::send( 'mail.documento_electronico', $mail_data, function( $message ) use ($mail_data,$dataPdf,$filename,$dataXml,$filenameXml)
                        {
                            $message->to($mail_data["email"])
                                      ->subject( 'Ha recibido un comprobante electrónico!' )
                                      ->attachData($dataPdf,$filename)
                                      ->attachData($dataXml,$filenameXml);
                        });
                        return response()->json(true);
                }
            }
            else if($documento=="06"){ //guía remisión
              $guia = Guia::where('id',$id)->first();
              $dataPdf = Storage::get('guia/ride/'.$guia->clave_acceso.'.pdf');
              $filename=$guia->clave_acceso.'.pdf';
              $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$guia->clave_acceso.'.xml'));

             // $dataXml = Storage::get('guia/autorizados/'.$guia->clave_acceso.'.xml');
              $filenameXml=$guia->clave_acceso.'.xml';
              $mail_data = array();
              if($guia){
                $cliente = Customer::where('id',$guia->customer_id)->first();
                $mail_data['email'] = $cliente->email;
                $mail_data["cliente"] = $cliente;
                $mail_data["emisor"] = Emisor::where('is_active',1)->first();
                        Mail::send( 'mail.documento_electronico', $mail_data, function( $message ) use ($mail_data,$dataPdf,$filename,$dataXml,$filenameXml)
                        {
                            $message->to($mail_data["email"])
                                      ->subject( 'Ha recibido un comprobante electrónico!' )
                                      ->attachData($dataPdf,$filename)
                                      ->attachData($dataXml,$filenameXml);
                        });
                        return  response()->json(true);
                }
            }
            else if($documento=="07"){ //retención
              $retencion = Guia::where('id',$id)->first();
              $dataPdf = Storage::get('retencion/ride/'.$retencion->clave_acceso.'.pdf');
              $filename=$retencion->clave_acceso.'.pdf';
              $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$retencion->clave_acceso.'.xml'));

              //$dataXml = Storage::get('retencion/autorizados/'.$retencion->clave_acceso.'.xml');
              $filenameXml=$retencion->clave_acceso.'.xml';
              $mail_data = array();
              if($retencion){
                $cliente = Customer::where('id',$retencion->sujeto_retenido)->first();
                $mail_data['email'] = $cliente->email;
                $mail_data["cliente"] = $cliente;
                $mail_data["emisor"] = Emisor::where('is_active',1)->first();
                        Mail::send( 'mail.documento_electronico', $mail_data, function( $message ) use ($mail_data,$dataPdf,$filename,$dataXml,$filenameXml)
                        {
                            $message->to($mail_data["email"])
                                      ->subject( 'Ha recibido un comprobante electrónico!' )
                                      ->attachData($dataPdf,$filename)
                                      ->attachData($dataXml,$filenameXml);
                        });
                        return response()->json(true);    
                }
                
            }
          } catch (\Throwable $th) {
            return response()->json($th);
          }    
    }
}

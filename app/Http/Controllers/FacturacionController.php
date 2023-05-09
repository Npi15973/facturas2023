<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\File;
use App\Librerias\FacturacionElectronica;
use Illuminate\Support\Facades\Storage;
use App\Services\FacturacionElectronicaService;
use App\Customer;
use App\Sale;
use App\Purchase;
use App\FormaDePago;
use App\Emisor;
use App\Supplier;
use SoapClient;
use PDF;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Account;
use Illuminate\Support\Facades\Mail;
use Image;
use DB;
use App\User;
use Illuminate\Support\Facades\Validator;
use App\NotaCredito;
use App\NotaDebito;
use App\Guia;
use App\Retencion;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class FacturacionController extends Controller
{

use ApiResponser;

    public $facturacionElectronica;
    private $facturacionElectronicaService;

    public function __construct(FacturacionElectronica $facturacionElectronica, FacturacionElectronicaService $facturacionElectronicaService){

      $this->facturacionElectronica=$facturacionElectronica;
      $this->facturacionElectronicaService= $facturacionElectronicaService;
    }



    public function index2(){

      $data=array();
      
      return view('facturacion.documentos',$data);
    }


    public function procesarComprobante(Request $request){
     $id = $request->documentId;
      $tipo = $request->documentType;

      if(!$this->validarEmisor()){
       
        return redirect()->back()->withErrors("El emisor no se encuentra bien configurado");
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
     // return $firma;
     // return $this->facturacionElectronica->firmarXml($claveFirma,$firma,$nombre_documento.'.xml');
      //return redirect('sales')->with('message', $this->facturacionElectronica->firmarXml($claveFirma,$firma,$nombre_documento.'.xml'));
      if($this->facturacionElectronica->firmarXml($claveFirma,$firma,$nombre_documento.'.xml')){
        $mensaje = $this->validarComprobante($tipo,$nombre_documento);
     //return "hoila";
     Session::flash('message', $mensaje);
    return Redirect::back();
      }else{
        Session::flash('message', "Ha ocurrido un error");
        return Redirect::back();
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
      return $emisor = Emisor::where('is_active',1)->first();
      //return $pass= $emisor->password_firma;
      //return response()->json($pass);
    }


     public function validarFechaCertificado(Request $request){
      $fechaInicio= $request->fechaInicio;
      $fechaFin= $request->fechaFin;
      $result=$this->facturacionElectronica->validarFechaCertificado($fechaInicio,$fechaFin);
      return $result;
    }

    /**
     *
     */
    public function index(){
      return view('facturacion.index');
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

    /**
     *
     */
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

    public function  ObtenerRutaPdf($id){
      $venta = Sale::where('id',$id)->first();
      $claveAcceso = $venta->clave_acceso;
      $cliente="1721854675";
      $ruta= "public/comprobantes_electronicos/".$cliente."/".$claveAcceso.".pdf";
      return $ruta;
    }
    
    /**
     *
     */
   

    public function ProcesoFacturacion(){

    } 

    public function GenerarRidePDFVisual($id,$documento){
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
          //$dataXml = Storage::get('facturas/'.$venta->clave_acceso.'.xml');
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
        $pdf = PDF::loadView('ride.factura',$data);

        $filename = $venta->clave_acceso.".pdf";
   
      }else if($documento=="03"){ // liquidación de compras
        $liquidacion = Purchase::where('id',$id)->select('clave_acceso','supplier_id','fecha_autorizacion','fecha_emision','plazo')->first();
        if($liquidacion->fecha_autorizacion != null || $liquidacion->fecha_autorizacion != ""){
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$liquidacion->clave_acceso.'.xml'));
          //$dataXml = Storage::get('liquidacion/autorizados/'.$liquidacion->clave_acceso.'.xml');
        }else{
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$liquidacion->clave_acceso.'.xml'));
          //$dataXml = Storage::get('liquidacion/'.$liquidacion->clave_acceso.'.xml');
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
        
      }else if($documento == "04"){ // nota crédito

        $notaCredito = NotaCredito::where('id',$id)->select('estado_sri','clave_acceso','customer_id','fecha_autorizacion','fecha_emision')->first();
        if($notaCredito->fecha_autorizacion != null || $notaCredito->fecha_autorizacion != ""){
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$notaCredito->clave_acceso.'.xml'));
          //$dataXml = Storage::get('nota_credito/autorizados/'.$notaCredito->clave_acceso.'.xml');
        }else{
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$notaCredito->clave_acceso.'.xml'));
         // $dataXml = Storage::get('nota_credito/'.$notaCredito->clave_acceso.'.xml');
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
        
      }else if($documento == "05"){ // nota débito


        $notaDebito = NotaDebito::where('id',$id)->select('estado_sri','clave_acceso','customer_id','fecha_autorizacion','fecha_emision')->first();
        if($notaDebito->fecha_autorizacion != null || $notaDebito->fecha_autorizacion != ""){
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$notaDebito->clave_acceso.'.xml'));
          //$dataXml = Storage::get('nota_debito/autorizados/'.$notaDebito->clave_acceso.'.xml');
        }else{
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$notaDebito->clave_acceso.'.xml'));
          //$dataXml = Storage::get('nota_debito/'.$notaDebito->clave_acceso.'.xml');
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
       

      }else if($documento == "06"){ // guía

        $guia = Guia::where('id',$id)->select('estado_sri','clave_acceso','customer_id','fecha_autorizacion','fecha_emision')->first();
        if($guia->fecha_autorizacion != null || $guia->fecha_autorizacion != ""){
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$guia->clave_acceso.'.xml'));
          //$dataXml = Storage::get('guia/autorizados/'.$guia->clave_acceso.'.xml');
        }else{
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$guia->clave_acceso.'.xml'));
          //$dataXml = Storage::get('guia/'.$guia->clave_acceso.'.xml');
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
 
      }else if($documento == "07"){ // retención

        $retencion = Retencion::where('id',$id)->select('estado_sri','clave_acceso','sujeto_retenido','fecha_autorizacion','fecha_emision')->first();
        if($retencion->fecha_autorizacion != null || $retencion->fecha_autorizacion != ""){
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$retencion->clave_acceso.'.xml'));
          //$dataXml = Storage::get('retencion/autorizados/'.$retencion->clave_acceso.'.xml');
        }else{
          $dataXml = File::get(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$retencion->clave_acceso.'.xml'));
          //$dataXml = Storage::get('retencion/'.$retencion->clave_acceso.'.xml');
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
        

      }

      $pdf_base64= base64_encode($pdf->stream($filename));
      return response()->json($pdf_base64);
    }

    /**
     * descargar xml
     */
    public function descargarXml($id,$documento){

      if($documento=="01"){ // factura
        $venta = Sale::where('id',$id)->select('clave_acceso','fecha_autorizacion')->first();
        if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
          
        }else{
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
          
        }
        return "error";
      }else if($documento=="03"){
        $venta = Purchase::where('id',$id)->select('clave_acceso','fecha_autorizacion')->first();
        if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
         
        }else{
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
          
        }
        return "error";
      }
      else if($documento=="04"){ // nota credito
        $venta = NotaCredito::where('id',$id)->select('clave_acceso','fecha_autorizacion')->first();
        if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
          
        }else{
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
          
        }
        return "error";
      }
      else if($documento=="05"){ // nota débito
        $venta = NotaDebito::where('id',$id)->select('clave_acceso','fecha_autorizacion')->first();
        if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
          
        }else{
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
          
        }
        return "error";
      }
      else if($documento=="06"){ // guía
        $venta = Guia::where('id',$id)->select('clave_acceso','fecha_autorizacion')->first();
        if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
          
        }else{
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
          
        }
        return "error";
      }
      else if($documento=="07"){ // guía
        $venta = Retencion::where('id',$id)->select('clave_acceso','fecha_autorizacion')->first();
        if($venta->fecha_autorizacion != null || $venta->fecha_autorizacion != ""){
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/autorizados/'.$venta->clave_acceso.'.xml'));
          
        }else{
          return response()->download(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$venta->clave_acceso.'.xml'));
          
        }
        return "error";
      }
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


public function Emisor(){
  $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('expenses-index')){
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_account_list = Account::where('is_active', true)->get();
            $general_setting = DB::table('general_settings')->latest()->first();
            
            $emisor = Emisor::where('is_active',1)->get();;
            return view('facturacion.emisor', compact('all_permission','emisor'));
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo');
  //return view('');
}
public function CrearEmisor(Request $request){
    $data= $request->except('logo','firma');
    
    $data["is_active"] = 1;
    $img = $request->logo;
    $firma = $request->firma;
    //return $data;
    if($img){
      $file1=$img;
      $image=Image::make($file1->getRealPath());
      $image->resize(100, 100);
      $imagen_base64 = $image->encode('data-url');
      $data["logo"] =$imagen_base64;
    }
    if($firma){
      $filename = $request->file('firma')->getClientOriginalName();
      $path = $request->firma->move(public_path('certificados'), $filename);
      
      $data["firma"] = $filename;
    }
    $data["serie"] = "001001";
    //return $data;
    $emisor = Emisor::create($data);
    if($emisor){
      $message="Emisor creado correctamente";
      return redirect()->back()->with('message', $message);
    }else{
      $message="Error al crear el emisor";
      return redirect()->back()->with('error', $message);
    }
    
} 

public function eliminarEmisor($id){
  $emisor=Emisor::where('id',$id)->first();
  $emisor->delete();
  return response()->json(true);
}
public function update(Request $request){
    $data= $request->except('logo','firma','id','_token');
    $id=$request->id;
    $data["is_active"] = 1;
    $img = $request->logo;
    $firma = $request->firma;
    //return $data;
    if($img){
      $file1=$img;
      $image=Image::make($file1->getRealPath());
      $image->resize(100, 100);
      $imagen_base64 = $image->encode('data-url');
      $data["logo"] =$imagen_base64;
    }
    if($firma){
      $filename = $request->file('firma')->getClientOriginalName();
      $path = $request->firma->move(public_path('certificados'), $filename);
      
      $data["firma"] = $filename;
    }
    $data["serie"] = "001001";
    //return $data;
    $emisor = Emisor::where('id',$id)->update($data);
    if($emisor){
      $message="Emisor actualizado correctamente";
      return redirect()->back()->with('message', $message);
    }else{
      $message="Error al actualizado el emisor";
      return redirect()->back()->with('error', $message);
    }
}
public function editar($id){
  $emisor= Emisor::where('id',$id)->first();
  $data["emisor"] = $emisor;
  return view('facturacion.edit_emisor',$data);
}

public function liquidacion(){
  $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-index')){
            $general_setting = DB::table('general_settings')->first();
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->get();
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_account_list = Account::where('is_active', true)->get();
            
            return view('purchase.index', compact('lims_purchase_list', 'lims_account_list', 'all_permission',
             'lims_pos_setting_data'));
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo');
}
public function crearLiquidacionCompra(){

}

    
  
public function firmarXml(){
    
           
             
  $clave = 'Colmena1587';
  $almacen_cert=  public_path('certificados/firma_electronica-jg.p12');

          if (openssl_pkcs12_read($almacen_cert, $info_cert, $clave)) {
            //$func = new fac_ele();
            $vtipoambiente = 2;
            //$wsdls = $func->wsdl($vtipoambiente);
            //$recepcion = $wsdls['recepcion'];
          //  $autorizacionws = $wsdls['autorizacion'];
            //RUTAS PARA LOS ARCHIVOS XML
            $ruta_no_firmados = public_path('facturacion/facturacionphp/comprobantes/no_firmados/');
            $ruta_si_firmados = public_path('facturacion/facturacionphp/comprobantes/si_firmados/');
            $ruta_autorizados = public_path('acturacion/facturacionphp/comprobantes/autorizados/');
            $pathPdf = public_path('facturacion/facturacionphp/comprobantes/pdf/');
            $tipo = 'FV';
            date_default_timezone_set("America/Lima");
            $fecha_actual = date('d-m-Y H:m:s', time());
            //$codigo_factura = $iduser.md5(date('d-m-Y H:m:s')).$iduser;

            $acceso_no_firmados = simplexml_load_file($ruta_no_firmados);
            $claveAcceso_no_firmado['claveAccesoComprobante'] = substr($acceso_no_firmados->infoTributaria[0]->claveAcceso, 0, 49);
            $clave_acc_guardar = implode($claveAcceso_no_firmado);

            $nuevo_xml = ''.$clave_acc_guardar.'.xml';
            $controlError = false;
            $m = '';
            $show = '';
            //VERIFICAMOS SI EXISTE EL XML NO FIRMADO CREADO
              if (file_exists($ruta_no_firmados)) {
                $argumentos = $ruta_no_firmados . ' ' . $ruta_si_firmados . ' ' . $nuevo_xml . ' ' . $almacen_cert . ' ' . $clave;
                //FIRMA EL XML
                $comando = ('java -jar facturacion/firmaComprobanteElectronico/dist/firmaComprobanteElectronico.jar ' . $argumentos);
                $resp = shell_exec($comando);
                $claveAcces = simplexml_load_file($ruta_si_firmados . $nuevo_xml);
                $claveAcceso['claveAccesoComprobante'] = substr($claveAcces->infoTributaria[0]->claveAcceso, 0, 49);

              
              }


        }
  }

}

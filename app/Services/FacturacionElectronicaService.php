<?php

namespace App\Services;


use App\Traits\ConsumesExternalService;
use SoapClient;

/**
 * Service Lecturas
 */
class FacturacionElectronicaService
{

  use ConsumesExternalService;

/**
 * url base del servicio a consumir
 */
  public $baseUrl;
  private $url_sri_valida_pruebas ="https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl";
  private $url_sri_autoriza_pruebas ="https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl";

  private $url_sri_valida_produccion ="https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl";
  private $url_sri_autoriza_produccion ="https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl";

/**
 * key autentication service
 */
  public $secret;

  public function __construct()
  {
      $this->baseUrl=config('services.facturacion.base_url');
      $this->secret=config('services.facturacion.secret');
  }

  /**
   * obtiene datos de emisor desde servicio
   */
  public function getDataEmisor($ruc){
    return $this->performRequest('GET',"/operaciones/empresa/{$ruc}");
  }


  /** 
   * registrar datos de emisor en el servicio 
  */
  public function registrarEmisor($data){
    return $this->performRequest('POST',"/operaciones/empresa",$data);
  }

  /**
   * 
   */
  
   public function enviarComprobanteSri($xml, $ambiente){
     try {
      $parametros = new \stdClass();
      $parametros->xml = $xml;
      $client="";
      if($ambiente=="1"){// pruebas
        $client = new SoapClient($this->url_sri_valida_pruebas);
      }
      if($ambiente=="2"){
        $client = new SoapClient($this->url_sri_valida_produccion);
      }
      
      $result = $client->validarComprobante($parametros);
      return json_encode($result);
     } catch (\Throwable $th) {
       return json_encode($th);
     }
      
   }
   /** 
    * 
   */
   public function autorizarComprobanteSri($claveAcceso, $ambiente){
      try {
      $cliente="";
      $parametros = array(); //parametros de la llamada
      $parametros['claveAccesoComprobante'] = $claveAcceso;
      if($ambiente=="1"){
        $client = new SoapClient($this->url_sri_autoriza_pruebas);
      }
      if($ambiente=="2"){
        $client = new SoapClient($this->url_sri_autoriza_produccion);
      }
      
      $result = $client->autorizacionComprobante($parametros);
      return json_encode($result);
      } catch (\Throwable $th) {
        return json_encode($th);
      } 
  }
}

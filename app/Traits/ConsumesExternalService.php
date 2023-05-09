<?php
namespace App\Traits;

use GuzzleHttp\Client;
/**
 *
 */
trait ConsumesExternalService
{
  /**
   * send a request to any service
   * @return string
   */
  public function performRequest($method,$requestUrl,$formParams=[],$headers=[])
  {
    $client= new Client([
      'base_uri'=>$this->baseUrl,
    ]);

    if(isset($this->secret)){
      $headers['Authorization']=$this->secret;
    }
    $response= $client->request($method,$requestUrl,['form_params'=>$formParams,'headers'=>$headers]);

    return $response->getBody()->getContents();
  }

/**
 * envia archivos al servicio
 */
  public function performRequestFiles($method,$requestUrl,$file,$id){
    $client= new Client([
      'base_uri'=>$this->baseUrl,
    ]);

    if(isset($this->secret)){
      $headers['Authorization']=$this->secret;
    }

    $response = $client->request($method, $requestUrl, [
        'multipart' => [
            [
                'name'     => 'id',
                'contents' => $id,
            ],
            [
                'name'     => 'file',
                'contents' => fopen($file, 'r')
            ],

        ],
        'headers'=>$headers
        ]);

        return $response->getBody()->getContents();
  }

  public function  performRequestSri(){
    $client= new Client([
      'base_uri'=>'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
    ]);
  }

}

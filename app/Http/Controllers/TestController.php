<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{

    public function index()
    {
        //$ruta_no_firmados  = Storage::get('facturas/1501202301172185467500110010010000001001234567812.xml'); 
        $clave = 'Colmena1587';
        $cert=  public_path('certificados/firma_electronica-jg.p12');
        $almacen_cert = file_get_contents($cert);
                if (openssl_pkcs12_read($almacen_cert, $info_cert, $clave)) {


                $ruta_no_firmados = public_path('facturacion/facturacionphp/comprobantes/no_firmados/1501202301172185467500110010010000001001234567812.xml');
                  $ruta_si_firmados = public_path('facturacion/facturacionphp/comprobantes/si_firmados/');
                  $ruta_autorizados = public_path('acturacion/facturacionphp/comprobantes/autorizados/');
                  $pathPdf = public_path('facturacion/facturacionphp/comprobantes/pdf/');
                  $tipo = 'FV';
                  date_default_timezone_set("America/Lima");
                  $fecha_actual = date('d-m-Y H:m:s', time());
      
                  $acceso_no_firmados = simplexml_load_file($ruta_no_firmados);
                  $claveAcceso_no_firmado['claveAccesoComprobante'] = substr($acceso_no_firmados->infoTributaria[0]->claveAcceso, 0, 49);
                  $clave_acc_guardar = implode($claveAcceso_no_firmado);
      
                  $nuevo_xml = ''.$clave_acc_guardar.'.xml';

                  //VERIFICAMOS SI EXISTE EL XML NO FIRMADO CREAD
                    if (file_exists($ruta_no_firmados)) {
                       // return "in sign";
                      $argumentos = $ruta_no_firmados . ' ' . $ruta_si_firmados . ' ' . $nuevo_xml . ' ' . $cert . ' ' . $clave;
                      //FIRMA EL XML
                      $pathJar = public_path('facturacion/firmaComprobanteElectronico/dist/firmaComprobanteElectronico.jar');
                       $comando = ('java -jar '.$pathJar.' ' . $argumentos);
                       $resp = shell_exec($comando);
                     
                      if($resp=="FIRMADO"){
                        return true;
                      }  
                      
                    }

                    return false;
              }
        return false;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function testFirma(){
        return "Hola";
    }
}

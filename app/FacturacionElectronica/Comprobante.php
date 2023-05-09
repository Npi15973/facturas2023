<?php

namespace App\FacturacionElectronica;


use App\FacturacionElectronica\Factura\Factura;
use App\FacturacionElectronica\InfoTributaria;
use App\FacturacionElectronica\Factura\InfoFactura;
use App\FacturacionElectronica\Pagos;
use App\FacturacionElectronica\TotalImpuesto;
use App\FacturacionElectronica\Factura\Detalle;
use App\FacturacionElectronica\Impuesto;
use App\FacturacionElectronica\InfoAdicional;
use App\FacturacionElectronica\liquidacion\Liquidacion;
use App\FacturacionElectronica\liquidacion\InfoLiquidacionCompra;
use App\FacturacionElectronica\nota_credito\NotaCredito;
use App\FacturacionElectronica\nota_credito\InfoNotaCredito;
use App\Librerias\FacturacionElectronica;

class Comprobante 
{
    private $facturacion;

    function __construct(FacturacionElectronica $facturacion){
        $this->facturacion=$facturacion;
    }

    /**
     * gener modelo data factura 
     */
    public function generarFactura($data){

        $factura = new Factura();
        $factura->id="comprobante";
        $factura->version="1.0.0";
        $factura->infoTributaria=$this->generarInfoTributaria($data);
        $factura->infoFactura=$this->generarInfoFactura($data);
        $factura->detalle=$this->generarDetalle($data);
        return $factura;
    }


    /**
     * generar modelo data liquidación compra
     */

     public function generarLiquidacionCompra($data){
        $liquidacion = new Liquidacion();
        $liquidacion->id="comprobante";
        $liquidacion->version="1.0.0";
        $liquidacion->infoTributaria=$this->generarInfoTributaria($data);
        $liquidacion->infoLiquidacionCompra = $this->generarinfoLiquidacionCompra($data);
        $liquidacion->detalle=$this->generarDetalleLiquidacion($data);
        $liquidacion->infoAdicional=$this->generarInfoAdicionalLiquidacion($data);
        return $liquidacion;
     }

     /**
      * generar data nota credito
      */
      public function generarNotaCreadito($data){
        $notaCredito = new NotaCredito();
        $notaCredito->id="comprobante";
        $notaCredito->version="1.0.0";
        $notaCredito->infoTributaria=$this->generarInfoTributaria($data);
        $notaCredito->infoNotaCredito = $this->generarInfoNotaCredito($data);
        $notaCredito->detalle=$this->generarDetalle($data);
        $notaCredito->infoAdicional=$this->generarInfoAdicionalNota($data);
        return $notaCredito;
      }

      /**
       * generar nota débito
       */
      public function generarNotaDebito($data){
        
      }
    
    
    public function generarInfoNotaCredito($data){
        $infoNotaCredito = new InfoNotaCredito();
        $infoNotaCredito->fechaEmision= $data["fechaEmision"];
        $infoNotaCredito->dirEstablecimiento=$data["dirEstablecimiento"];
        $infoNotaCredito->tipoIdentificacionComprador=$data["tipoIdentificacionComprador"];
        $infoNotaCredito->razonSocialComprador=$data["cliente"]->name;
        $infoNotaCredito->identificacionComprador=$data["cliente"]->tax_no;
        $infoNotaCredito->obligadoContabilidad= $data['emisor']->obligado_contabilidad;
        $infoNotaCredito->codDocModificado=$data["codDocModificado"];
        $infoNotaCredito->numDocModificado=$data["numDocModificado"];
        $infoNotaCredito->fechaEmisionDocSustento = $data["fechaEmisionDocSustento"];
        $infoNotaCredito->totalSinImpuestos= $data["totalSinImpuestos"];
        $infoNotaCredito->valorModificacion=$data["valorModificacion"];
        $infoNotaCredito->moneda="DOLAR";
        $infoNotaCredito->totalConImpuestos= $this->generarTotalImpuesto($data);
        $infoNotaCredito->motivo=$data["motivo"];
        return $infoNotaCredito;
    }
    private function generarinfoLiquidacionCompra($data){
        $infoLiquidacion= new InfoLiquidacionCompra();
        $infoLiquidacion->fechaEmision=$data['fechaEmision'];
        $infoLiquidacion->dirEstablecimiento=$data["dirEstablecimiento"];
        $infoLiquidacion->obligadoContabilidad=$data['emisor']->obligado_contabilidad;
        $infoLiquidacion->tipoIdentificacionProveedor=$data['tipo_documento'];
        $infoLiquidacion->razonSocialProveedor = $data['provedor']->name;
        $documento ="";
        if($data['tipo_documento']=="05"){
            $caracteres = strlen($data['provedor']->vat_number);
            if($caracteres==10){
                $documento = $data['provedor']->vat_number;
            }else if($caracteres==13){
                $documento= substr($data['provedor']->vat_number, 0, -3); 
            }
        }
        else if($data['tipo_documento']=="04"){
            $caracteres = strlen($data['provedor']->vat_number);
            if($caracteres==10){
                $documento = $data['provedor']->vat_number."001";
            }else if($caracteres==13){
                $documento= $data['provedor']->vat_number; 
            }
        }
        else{
        $documento= $data['provedor']->vat_number;        
        }
        $infoLiquidacion->identificacionProveedor=$documento;
        $infoLiquidacion->direccionProveedor=$data['provedor']->address;
        $totalSinImpuestos=$data['grand_total']- $data['total_tax'];
        $infoLiquidacion->totalSinImpuestos= $totalSinImpuestos;
        $infoLiquidacion->totalDescuento=$data['total_discount'];
        $infoLiquidacion->totalConImpuestos=$this->generarTotalImpuesto($data);
        $infoLiquidacion->importeTotal=$data->grand_total;
        $infoLiquidacion->moneda="DOLAR";
        $infoLiquidacion->pagos=$this->generarPago($data);
        return $infoLiquidacion;
    }

    private function generarInfoAdicionalLiquidacion($data){
        $infoAdicional= array();
        $infoAdicional["telefono"] = $data['provedor']->phone_number;
        $infoAdicional["email"] = $data['provedor']->email;
        return $infoAdicional;

    }   

    public function generarInfoAdicionalNota($data){
        $infoAdicional= array();
        $infoAdicional["telefono"] = $data['cliente']->phone_number;
        $infoAdicional["email"] = $data['cliente']->email;
        return $infoAdicional;
    }

    private function generarInfoFactura($data){
        $infoFactura = new InfoFactura();
        $infoFactura->fechaEmision=$data['fechaEmision'];
        $infoFactura->dirEstablecimiento=$data["dirEstablecimiento"];
        $infoFactura->obligadoContabilidad=$data['emisor']->obligado_contabilidad;
        $infoFactura->contribuyenteEspecial=$data['emisor']->contribuyente;
        $infoFactura->tipoIdentificacionComprador=$data['tipo_documento'];
        $infoFactura->razonSocialComprador=$data['cliente']->name;
        $documento ="";
        if($data['tipo_documento']=="05"){
            $caracteres = strlen($data['cliente']->tax_no);
            if($caracteres==10){
                $documento = $data['cliente']->tax_no;
            }else if($caracteres==13){
                $documento= substr($data['cliente']->tax_no, 0, -3); 
            }
        }
        else if($data['tipo_documento']=="04"){
            $caracteres = strlen($data['cliente']->tax_no);
            if($caracteres==10){
                $documento = $data['cliente']->tax_no."001";
            }else if($caracteres==13){
                $documento= $data['cliente']->tax_no; 
            }
        }
        else{
        $documento= $data['cliente']->tax_no;        
        }
        
        $infoFactura->identificacionComprador=$documento;
        $infoFactura->direccionComprador=$data['cliente']->addres;
        $totalSinImpuestos=$data['grand_total']- $data['total_tax'];
        $infoFactura->totalSinImpuestos=$totalSinImpuestos;
        $infoFactura->totalDescuento=$data['total_discount'];
        $infoFactura->totalImpuesto=$this->generarTotalImpuesto($data);
        $infoFactura->propina= "0";
        $infoFactura->importeTotal= $data->grand_total;
        $infoFactura->moneda= "DOLAR";
        $infoFactura->pagos=$this->generarPago($data);
        return $infoFactura;
    }

    private function generarTotalImpuestoNota($data){

    }
    
    private function generarTotalImpuesto($data){
        $lsDetalle = $data->detalle;
        $totalImpuestoArray=array();
        $totalImpuestoIva12=0;
        $totalImpuestoIva0=0;
        $contador =0;
        foreach ($lsDetalle as $key => $value) {
            
            if($value->tax_rate=="12"){
                $totalImpuestoIva12 = $totalImpuestoIva12+$value->total;
            }else if($value->tax_rate=="0"){
                $totalImpuestoIva0 = $totalImpuestoIva0+$value->total;
            }    
        }

                if($totalImpuestoIva12>0){
                    $totalImpuesto = new TotalImpuesto();
                    $totalImpuesto->codigo="2";
                    $totalImpuesto->codigoPorcentaje="2";
                    $totalSinImpuestos=$totalImpuestoIva12/1.12;
                    $totalIva = $totalImpuestoIva12 - $totalSinImpuestos;
                    $totalImpuesto->baseImponible=number_format($totalSinImpuestos,2);
                    $totalImpuesto->valor=number_format($totalIva,2);
                    $totalImpuestoArray[$contador]= $totalImpuesto;
                    $contador++;
                }
                if($totalImpuestoIva0>0){
                    $totalImpuesto = new TotalImpuesto();
                    $totalImpuesto->codigo="2";
                    $totalImpuesto->codigoPorcentaje="0";
                    $totalSinImpuestos=number_format($totalImpuestoIva0,2);
                    $totalImpuesto->baseImponible=number_format($totalSinImpuestos,2);
                    $totalImpuesto->valor="0";
                    $totalImpuestoArray[$contador]= $totalImpuesto;
                }
                
        return $totalImpuestoArray;
    }

    private function generarInfoTributaria($data){
        $infoTributaria = new InfoTributaria();
        $infoTributaria->ambiente=$data['emisor']->ambiente;
        $infoTributaria->tipoEmision=$data['emisor']->tipo_emision;
        $infoTributaria->razonSocial = $data['emisor']->razon_social;
        $infoTributaria->nombreComercial = $data['emisor']->nombre_comercial;
        $infoTributaria->ruc = $data['emisor']->ruc;
        $infoTributaria->claveAcceso= $this->GenerarClaveAcceso($data);
        $infoTributaria->codDoc = $data['codDoc'];
        $infoTributaria->estab =$data['estab'];
        $infoTributaria->ptoEmi = $data['ptoEmi'];
        $infoTributaria->secuencial = $data['secuencial'];
        $infoTributaria->dirMatriz = $data['dirMatriz'];
        return $infoTributaria;
    }
    private function GenerarClaveAcceso($data){

        $clave = $this->facturacion->GenerarClaveDeAccesos($data->fecha_emision,$data['codDoc'],$data['emisor']->ruc,$data['emisor']->ambiente,$data['emisor']->serie,$data->secuencial,$data->codigo_numerico,$data['emisor']->tipo_emision);
        return $clave;
    }
    private function generarPago($data){
        $pago = new Pagos();
        $pago->formaPago=$data->forma_pago;
        $pago->total=$data->grand_total;
        $pago->plazo=$data->plazo;
        $pago->unidadTiempo="dias";
        return $pago;
    }
    private function generarDetalle($data){
        $lsDetalle = $data->detalle;
        $detalleArray=array();
        $contador=0;
        foreach ($lsDetalle as $key => $value) {
            $detalle= new Detalle();
            $detalle->codigoPrincipal=$value->product_id;
            $detalle->codigoAuxiliar=$value->code;
            $detalle->descripcion=$value->name;
            $detalle->cantidad=$value->qty;
            $detalle->precioUnitario=$value->net_unit_price;
            $detalle->descuento=$value->discount;
            $precioTotalSinImpuesto= $value->qty * $value->net_unit_price;
            $detalle->precioTotalSinImpuesto=number_format($precioTotalSinImpuesto,2);
            $detalle->impuesto=$this->generarImpuestoLinea($value);
            $detalleArray[$contador]= $detalle;
            $contador++;
        }
        return $detalleArray;
    }
    
    private function generarDetalleLiquidacion($data){
        $lsDetalle = $data->detalle;
        $detalleArray=array();
        $contador=0;
        foreach ($lsDetalle as $key => $value) {
            $detalle= new Detalle();
            $detalle->codigoPrincipal=$value->product_id;
            $detalle->codigoAuxiliar=$value->code;
            $detalle->descripcion=$value->name;
            $detalle->cantidad=$value->qty;
            $detalle->precioUnitario=$value->net_unit_cost;
            $detalle->descuento=$value->discount;
            $precioTotalSinImpuesto= $value->qty * $value->net_unit_cost;
            $detalle->precioTotalSinImpuesto=number_format($precioTotalSinImpuesto,2);
            $detalle->impuesto=$this->generarImpuestoLineaLiquidacion($value);
            $detalleArray[$contador]= $detalle;
            $contador++;
        }
        return $detalleArray;
    }

    private function generarImpuestoLinea($data){
        $impuesto = new Impuesto();
        $impuesto->codigo="2";
        $porcentaje="";
        if($data->tax_rate=="12"){
            $porcentaje="2";
        }
        if($data->tax_rate=="0"){
            $porcentaje="0";
        }
        $impuesto->codigoPorcentaje=$porcentaje;
        $impuesto->tarifa=$data->tax_rate;
        $impuesto->baseImponible=$data->net_unit_price * $data->qty;
        $impuesto->valor=number_format($data->tax,);
        return $impuesto;
    }
    private function generarImpuestoLineaLiquidacion($data){
        $impuesto = new Impuesto();
        $impuesto->codigo="2";
        $porcentaje="";
        if($data->tax_rate=="12"){
            $porcentaje="2";
        }
        if($data->tax_rate=="0"){
            $porcentaje="0";
        }
        $impuesto->codigoPorcentaje=$porcentaje;
        $impuesto->tarifa=$data->tax_rate;
        $impuesto->baseImponible=$data->net_unit_cost * $data->qty;
        $impuesto->valor=number_format($data->tax,2);
        return $impuesto;
    }


    
}

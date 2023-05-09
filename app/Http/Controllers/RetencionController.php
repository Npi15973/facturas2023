<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\Retencion;
use App\DetalleRetencion;
use App\CodigosRetencion;
use App\InformacionAdicional;
use App\Warehouse;
use App\FormaDePago;
use App\Biller;
use App\TipoDocumento;
use App\Emisor;
use App\User;
Use App\Product_Warehouse;
Use App\Product;
Use App\Tax;
use App\Customer;
use App\Supplier;
use DB;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\UserNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\GeneralSetting;
use App\FacturacionElectronica\Comprobante;
use App\Librerias\FacturacionElectronica;
use Illuminate\Support\Facades\File;

class RetencionController extends Controller
{
    private $comprobante;
    private $facturacion;
    public function __construct(Comprobante $comprobante, FacturacionElectronica $facturacion){
        $this->comprobante=$comprobante;
        $this->facturacion=$facturacion;
    }


    public function index(){
        
        $emisor = Emisor::where('is_active',1)->first();
        $lims_retenciones = Retencion::where('ambiente',$emisor->ambiente)->orderBy('id', 'desc')->get();
        
        
        return view('retencion.index',compact('lims_retenciones' ,'emisor'));
    }


    public function create(){
        $this->guardarRetenciones(null);
        
        $lims_customer_list = Customer::where('is_active', true)->where('name' ,'not like','%CONSUMIDOR%')->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_retenciones = Retencion::orderBy('id', 'desc');
        $emisor = Emisor::where('is_active',1)->first();
        $role = Role::find(Auth::user()->role_id);
        $tipo_documento= TipoDocumento::all();
        $codigos_retenciones= CodigosRetencion::all();
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        return view('retencion.create',compact('codigos_retenciones','lims_supplier_list','lims_retenciones' ,'lims_warehouse_list',
                'lims_biller_list','emisor','lims_customer_list','tipo_documento'));
    }

    public function agregarRetencionTabla(Request $request){
      
        $retencion= array();
        $retencion["codigo_retencion"] = $request->codigo_retencion;
        $retencion["tipo_impuesto"] = $request->tipo_retencion;
        $retencion["porcentaje"] = $request->porcentaje;
        $retencion["base_imponible"] = $request->base_imponible;
        $retencion["total"] = $request->total;
        $retencion["numero_documento"] = $request->numero_documento;
        $retencion["tipo_documento"] = $request->tipo_documento;
        $retencion["fecha_documento"] = $request->fecha_documento;
        $retenciones = $this->obtenerRetenciones();
        array_push($retenciones, $retencion);
        $this->guardarRetenciones($retenciones);
        $retenciones = $this->obtenerRetenciones();
        $data["retenciones"]= $retenciones;
        return view('retencion.table',$data);
    }


    public function store(Request $request){
            
            $dataResponse = array();
            $emisor = Emisor::where('is_active',1)->first();
            $dataInsert=array();
            $dataInsert["fecha_emision"]=$request->fecha_emision;
            $dataInsert["periodo_fiscal"]=$request->periodo_fiscal;
            $dataInsert["user_id"]=Auth::user()->id;
            $dataInsert["warehouse_id"]=$request->warehouse_id;
            $dataInsert["sujeto_retenido"]=$request->supplier_id;
            $dataInsert["tipo_sujeto"]="provedor";
            //$dataInsert["tipo_identificacion"]=$request->tipo_identificacion;
            $dataInsert["ambiente"] = $emisor->ambiente;
            $warehouse = Warehouse::where('id',$request->warehouse_id)->first();
            $secuencial = 0;
            $lastNote= Retencion::where('ambiente',$emisor->ambiente)->latest()->first();
            if($lastNote){
                $secuencial = $lastNote->secuencial_auxiliar + 1;
            }else{
                $puntoEmision = Biller::where('warehouse_id',$request->warehouse_id)->first();
                $secuencial= $puntoEmision->secuencial_nota_credito;
            }
            $dataInsert["secuencial_auxiliar"]=$secuencial;
            $secuencial_inicial = $secuencial;
            $num_sec_ide =9;
            $num_sec = strlen($secuencial_inicial);
            $falta = $num_sec_ide - $num_sec;
            $alterno="0";
            for ($i=1; $i < $falta ; $i++) { 
                $alterno=$alterno."0";
            }
            $secuencial_final = $alterno.$secuencial_inicial;
            $dataInsert["numero_documento"]=$warehouse->codigo."-001-".$secuencial_final;
            $dataInsert["estado_sri"]="creado";
            $retencion = Retencion::create($dataInsert);
            if($retencion){
                $retenciones= $this->obtenerRetenciones();
                
                    foreach ($retenciones as $key => $value) {
                        $dataDetalleRetencion = array();
                        $dataDetalleRetencion["codigo_retencion"] = $value["codigo_retencion"];
                        $dataDetalleRetencion["retencion_id"] = $retencion->id;
                        $dataDetalleRetencion["tipo_impuesto"] = $value["tipo_impuesto"];
                        $dataDetalleRetencion["porcentaje"] = $value["porcentaje"];
                        $dataDetalleRetencion["base_imponible"] = $value["base_imponible"];
                        $dataDetalleRetencion["total"] = $value["total"];
                        $dataDetalleRetencion["numero_documento"] = $value["numero_documento"];
                        $dataDetalleRetencion["tipo_documento"] = $value["tipo_documento"];
                        $dataDetalleRetencion["fecha_documento"] = $value["fecha_documento"];
                        DetalleRetencion::create($dataDetalleRetencion);
                    }
                $this->guardarRetenciones(null);
            }else{
                $dataResponse["status"]=false;
                $dataResponse["message"]="Error al crear Retención";
                return $dataResponse;
            }
            $this->generarRetencionXml($retencion->id);
            $dataResponse["status"]=true;
            $dataResponse["message"]="Retención creado con éxito";
            return $dataResponse;

    }

    public function generarRetencionXml($id , $esEdit=false){
        $retencion = Retencion::where("id",$id)->first();
        $warehouse = Warehouse::where("id",$retencion->warehouse_id)->first();
        $sujeto_retenido = Supplier::where('id',$retencion->sujeto_retenido)->first();
        $emisor= Emisor::where('is_active',1)->first();
        $dataXml = array();
        $sec =explode('-',$retencion->numero_documento);
        $clave_acceso=$this->facturacion->GenerarClaveDeAccesos($retencion->fecha_emision,"07",$emisor->ruc,$retencion->ambiente,$emisor->serie,$sec[2],$warehouse->codigo_numerico,$emisor->tipo_emision);
        $infoTributaria = array();  
        $infoTributaria["ambiente"] = $retencion->ambiente;
        $infoTributaria["tipoEmision"] = $emisor->tipo_emision;
        $infoTributaria["razonSocial"] = $emisor->razon_social;
        $infoTributaria["nombreComercial"] = $emisor->nombre_comercial;
        $infoTributaria["claveAcceso"] = $clave_acceso;
        $infoTributaria["ruc"] = $emisor->ruc;
        $infoTributaria["codDoc"] = "07";
        $infoTributaria["estab"] = $warehouse->codigo;
        $infoTributaria["ptoEmi"] = "001";
        $infoTributaria["secuencial"] = $sec[2];
        $infoTributaria["dirMatriz"] = $emisor->direccion_matriz;
        $dataXml["infoTributaria"]= $infoTributaria;

        $infoCompRetencion= array();
        $infoCompRetencion["fechaEmision"]=$retencion->fecha_emision;
        $infoCompRetencion["dirEstablecimiento"]=$warehouse->address;
        $infoCompRetencion["obligadoContabilidad"]=$emisor->obligado_contabilidad;
        $infoCompRetencion["tipoIdentificacionSujetoRetenido"]=$sujeto_retenido->tipo_documento;
        $infoCompRetencion["razonSocialSujetoRetenido"]=$sujeto_retenido->name;
        $infoCompRetencion["identificacionSujetoRetenido"]=$sujeto_retenido->vat_number;
        $infoCompRetencion["periodoFiscal"]=$retencion->periodo_fiscal;
        $dataXml["infoCompRetencion"]= $infoCompRetencion;

        $detalle_retencion = DetalleRetencion::where('retencion_id',$retencion->id)->get();

        $impuestos= array();
        foreach ($detalle_retencion as $key => $value) {
            $impuesto = array();
            $impuesto["codigo"]=$value->tipo_impuesto;
            $impuesto["codigoRetencion"]=$value->codigo_retencion;
            $impuesto["baseImponible"]=number_format($value->base_imponible,2);
            $impuesto["porcentajeRetener"]=$value->porcentaje;
            $impuesto["valorRetenido"]=number_format($value->total,2);
            $impuesto["codDocSustento"]=$value->tipo_documento;
            $impuesto["numDocSustento"]=$value->numero_documento;
            $impuesto["fechaEmisionDocSustento"]=$value->fecha_documento;
            $impuestos[$key]= $impuesto;
        }
        $dataXml["impuestos"]= $impuestos;


        $infoAdicional=array();
        $infoAdicional["telefono"] = $sujeto_retenido->phone_number;
        $infoAdicional["email"]=$sujeto_retenido->email;
        $dataXml["infoAdicional"]= $infoAdicional;

        $xml = $this->facturacion->generarRetencionXml($dataXml);
        if($esEdit){
            Storage::delete('retencion/'.$clave_acceso.'.xml');
         }
        Storage::put('retencion/'.$clave_acceso.'.xml', $xml);
        $not = Retencion::find($id);
        $not->clave_acceso= $clave_acceso;
        $not->save();
        if($not){
            File::put(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$clave_acceso.'.xml') , $xml);
         }
    }


    
    public function quitarRetencion(Request $request)
    {
        $indice = $request->indice;
        $retenciones = $this->obtenerRetenciones();
        array_splice($retenciones, $indice, 1);
        $this->guardarRetenciones($retenciones);
        $data["retenciones"]=$this->obtenerRetenciones();
        return view("retencion.table",$data);
    }

    public function eliminarDocumento($id){
        $nota = Retencion::find($id);
        $nota->delete();
        $detalle = DB::table('detalle_retencion')
                    ->where('retencion_id',$id)
                    ->delete();

        return response()->json(true);
    }

    public function anular($id){
        $nota = Retencion::find($id);
        $nota->estado_sri="anulado";
        $nota->save();
        return response()->json($nota);;
    }
    /**metodos privados */

    private function obtenerRetenciones()
    {
        $retenciones = session("retenciones");
        if (!$retenciones) {
            $retenciones = [];
        }
        return $retenciones;
    }

    private function buscarIndiceRetencion($id, $retenciones)
    {
        foreach ($retenciones as $indice => $retencion) {
            if ($retencion["id"] === $id) {
                return $indice;
            }
        }
        return -1;
    }
    
    private function guardarRetenciones($retenciones)
    {
        session(["retenciones" => $retenciones,
        ]);
    }

    private function getClaveAcceso($documento){
        $infoTrib =    $documento->infoTributaria;
         $claveAcceso = $infoTrib->claveAcceso;
        return $claveAcceso;
    }

    
}

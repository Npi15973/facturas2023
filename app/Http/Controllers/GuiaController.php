<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\Retencion;
use App\Guia;
use App\DetalleGuia;
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

class GuiaController extends Controller
{
    private $comprobante;
    private $facturacion;
    public function __construct(Comprobante $comprobante, FacturacionElectronica $facturacion){
        $this->comprobante=$comprobante;
        $this->facturacion=$facturacion;
    }

    public function index(){
        $emisor = Emisor::where('is_active',1)->first();
        $lims_guias = Guia::where('ambiente',$emisor->ambiente)->orderBy('id', 'desc')->get();
        //return $lims_guias;
        return view('guia.index',compact('lims_guias'));
    }


    public function create(){
        $this->guardarProductos(null);
    
        $lims_customer_list = Customer::where('is_active', true)->where('name' ,'not like','%CONSUMIDOR%')->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_retenciones = Retencion::orderBy('id', 'desc');
        $emisor = Emisor::where('is_active',1)->first();
        $role = Role::find(Auth::user()->role_id);
        $tipo_documento= TipoDocumento::all();
        $lims_product_data = Product::where('type', ['standard'])->where('is_active', true)->get();
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        return view('guia.create',compact('lims_product_data','lims_supplier_list','lims_retenciones' ,'lims_warehouse_list',
                'lims_biller_list','emisor','lims_customer_list','tipo_documento'));
    }


    public function store(Request $request){

        try {
            
            
            $emisor = Emisor::where('is_active',1)->first();
            $dataResponse = array();
            $dataInsert=array();
            $dataInsert["customer_id"]=$request->customer_id;
            $dataInsert["warehouse_id"]=$request->warehouse_id;
            $dataInsert["user_id"]=Auth::user()->id;
            $dataInsert["ambiente"] = $emisor->ambiente;
            $dataInsert["tipo_documento"]=$request->tipo_identificacion;
            $dataInsert["identificacion_transportista"]="04";
            $dataInsert["fecha_emision"]=$request->fecha_emision;
            $dataInsert["motivo_traslado"]=$request->motivo_traslado;
            $dataInsert["fecha_inicio"]=$request->fecha_inicio;
            $dataInsert["fecha_fin"]=$request->fecha_fin;
            $dataInsert["transportista"]=$request->transportista;
            $dataInsert["direccion_partida"]=$request->direccion_partida;
            $dataInsert["ruc_transportista"]=$request->ruc_transportista;
            $dataInsert["placa"]=$request->placa;

            
            $warehouse = Warehouse::where('id',$request->warehouse_id)->first();
            $secuencial = 0;
            $lastNote= Guia::where('ambiente',$emisor->ambiente)->latest()->first();
            if($lastNote){
                $secuencial = $lastNote->auxiliar_secuencial + 1;
            }else{
                $puntoEmision = Biller::where('warehouse_id',$request->warehouse_id)->first();
                $secuencial= $puntoEmision->secuencial_nota_credito;
            }
            $dataInsert["auxiliar_secuencial"]=$secuencial;

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
            $guia = guia::create($dataInsert);
            if($guia){
                $productos_guia= $this->obtenerProductos();
                
                    foreach ($productos_guia as $key => $value) {
                        $dataProductoGuia = array();
                        $dataProductoGuia["product_id"] = $value["id"];
                        $dataProductoGuia["guia_id"] = $guia->id;
                        $dataProductoGuia["cantidad"] = $value["qty"];
                        DetalleGuia::create($dataProductoGuia);
                    }
                
                $this->guardarProductos(null);
            }else{
                $dataResponse["status"]=false;
                $dataResponse["message"]="Error al crear Nota de crédito";
                return $dataResponse;
            }
            $this->generarGuiaXml($guia->id);
            $dataResponse["status"]=true;
            $dataResponse["message"]="Nota de crédito creado con éxito";
            return $dataResponse;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function generarGuiaXml($id ,$esEdit=false){
        
        $guia = Guia::where('id',$id)->first();
        $warehouse= Warehouse::where('id',$guia->warehouse_id)->first();
        $emisor= Emisor::where('is_active',1)->first();
        $cliente = Customer::where('id',$guia->customer_id)->first();
        $dataXml=array();
        $infoTributaria=array();
        $sec =explode('-',$guia->numero_documento);
        $clave_acceso=$this->facturacion->GenerarClaveDeAccesos($guia->fecha_emision,"06",$emisor->ruc,$guia->ambiente,$emisor->serie,$sec[2],$warehouse->codigo_numerico,$emisor->tipo_emision); 
        $infoTributaria["ambiente"] = $guia->ambiente;
        $infoTributaria["tipoEmision"] = $emisor->tipo_emision;
        $infoTributaria["razonSocial"] = $emisor->razon_social;
        $infoTributaria["nombreComercial"] = $emisor->nombre_comercial;
        $infoTributaria["claveAcceso"] = $clave_acceso;
        $infoTributaria["ruc"] = $emisor->ruc;
        $infoTributaria["codDoc"] = "06";
        $infoTributaria["estab"] = $warehouse->codigo;
        $infoTributaria["ptoEmi"] = "001";
        $infoTributaria["secuencial"] = $sec[2];
        $infoTributaria["dirMatriz"] = $emisor->direccion_matriz;
        $dataXml["infoTributaria"]= $infoTributaria;

        $infoGuiaRemision= array();
        $infoGuiaRemision["dirEstablecimiento"]=$warehouse->address;
        $infoGuiaRemision["dirPartida"]=$guia->direccion_partida;
        $infoGuiaRemision["razonSocialTransportista"]=$guia->transportista;
        $infoGuiaRemision["tipoIdentificacionTransportista"]=$guia->identificacion_transportista;
        $infoGuiaRemision["rucTransportista"]=$guia->ruc_transportista;
        $infoGuiaRemision["obligadoContabilidad"]=$emisor->obligado_contabilidad;
        $infoGuiaRemision["fechaIniTransporte"]=$guia->fecha_inicio;
        $infoGuiaRemision["fechaFinTransporte"]=$guia->fecha_fin;
        $infoGuiaRemision["placa"]=$guia->placa;
        $dataXml["infoGuiaRemision"]= $infoGuiaRemision;


        $destinatarios= array();
        $destinatario=array();
        $destinatario["identificacionDestinatario"]=$cliente->tax_no;
        $destinatario["razonSocialDestinatario"]= $cliente->name;
        $destinatario["dirDestinatario"]=$cliente->address;
        $destinatario["motivoTraslado"] = $guia->motivo_traslado;
        $detalles=array();
        $detalleGuia = DetalleGuia::where('guia_id',$guia->id)->get();
        foreach ($detalleGuia as $key => $value) {

            $producto = Product::where('id',$value->product_id)->first();
            $detalle = array();
            $detalle["codigoInterno"]=$producto->code;
            $detalle["descripcion"]= $producto->name;
            $detalle["cantidad"] = $value->cantidad;
            $detalles[$key]= $detalle;
        }
        $destinatario["detalles"] = $detalles;
        $destinatarios[0] = $destinatario;
        $dataXml["destinatarios"]= $destinatarios;

        $infoAdicional=array();
        $infoAdicional["telefono"] = $cliente->phone_number;
        $infoAdicional["email"]=$cliente->email;
        $dataXml["infoAdicional"]= $infoAdicional;

        $xml = $this->facturacion->generarGuiaXml($dataXml);
        if($esEdit){
            Storage::delete('guia/'.$clave_acceso.'.xml');
         }
        Storage::put('guia/'.$clave_acceso.'.xml', $xml);
        $not = Guia::find($id);
        $not->clave_acceso= $clave_acceso;
        $not->save();
        if($not){
            File::put(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$clave_acceso.'.xml') , $xml);
         }

    }
    public function getProduct($id){
        return $producto = Product::where('id',$id)->first();
    }


    public function guardarProductoDetalle(Request $request){
        
        $prod = Product::where('id',$request->id)->first();
        $total = 0;
        $producto= array();
        $producto["id"] = $request->id;
        $producto["qty"] = $request->qty;
        $producto["name"] = $prod->name;
        $producto["codigo"] = $prod->code;
        $productos = $this->obtenerProductos();
        $posibleIndice = $this->buscarIndiceDeProducto($producto["id"], $productos);
        if ($posibleIndice === -1) {
            
            array_push($productos, $producto);
        }else{

            $productos[$posibleIndice]= $producto;
        }
        $this->guardarProductos($productos);
        $productos = $this->obtenerProductos();
        $data["productos"]= $productos;
        return view('guia.table',$data);

    }
    public function quitarProducto(Request $request)
    {
        $indice = $request->indice;
        $productos = $this->obtenerProductos();
        array_splice($productos, $indice, 1);
        $this->guardarProductos($productos);
        $data["productos"]= $this->obtenerProductos();
        return view("guia.table",$data);
    }

    public function eliminarDocumento($id){
        $guia = Guia::find($id);
        $guia->delete();
        $detalle = DB::table('detalles_guia')
                    ->where('guia_id',$id)
                    ->delete();

        return response()->json(true);
    }

    public function anular($id){
        $nota = Guia::find($id);
        $nota->estado_sri="anulado";
        $nota->save();
        return response()->json($nota);;
    }

    /**metodos privados */
    private function obtenerProductos()
    {
        $productos = session("productos");
        if (!$productos) {
            $productos = [];
        }
        return $productos;
    }
    private function guardarProductos($productos)
    {
        session(["productos" => $productos,
        ]);
    }
    
    private function buscarIndiceDeProducto($id, $productos)
    {
        foreach ($productos as $indice => $producto) {
            if ($producto["id"] === $id) {
                return $indice;
            }
        }
        return -1;
    }
    private function getClaveAcceso($documento){
        $infoTrib =    $documento->infoTributaria;
         $claveAcceso = $infoTrib->claveAcceso;
        return $claveAcceso;
    }

    
}

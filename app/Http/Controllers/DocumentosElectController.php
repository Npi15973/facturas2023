<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\NotaCredito;
use App\NotaDebito;
use App\ProductNotaCredito;
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

class DocumentosElectController extends Controller
{


    private $comprobante;
    private $facturacion;
    public function __construct(Comprobante $comprobante, FacturacionElectronica $facturacion){
        $this->comprobante=$comprobante;
        $this->facturacion=$facturacion;
    }


    public function notaCreditoIndex(){
       
        $emisor = Emisor::where('is_active',1)->first();
        $lims_nota_credito = DB::table('nota_credito as t1')
                                ->join('customers as t2' ,'t1.customer_id','=','t2.id')
                                ->where('t1.ambiente',$emisor->ambiente)
                                ->select('t1.*','t2.name')
                                ->orderBy('t1.id', 'desc')
                                ->get();
       

        
        $tipo_documento= TipoDocumento::all();
        
        return view('nota_credito.index',compact('lims_nota_credito' ));
        
        
            
    }
    public function notaCreditoCreate(){
        $this->guardarProductos(null);
       
        $lims_product_data = Product::where('type', ['standard'])->where('is_active', true)->get();
        $lims_customer_list = Customer::where('is_active', true)->where('name' ,'not like','%CONSUMIDOR FINAL%')->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $emisor = Emisor::where('is_active',1)->first();
        NotaCredito::orderBy('id', 'desc')->get();
        $role = Role::find(Auth::user()->role_id);
        $tipo_documento= TipoDocumento::all();
        
        return view('nota_credito.create',compact('lims_tax_list','lims_warehouse_list',
                'lims_biller_list','emisor','lims_customer_list','tipo_documento','lims_product_data'));
    }
    public function demo(){
        return "Hola mundo";
    }

    public function guardarProductoDetalle(Request $request){
        
        $prod = Product::where('id',$request->id)->first();
        $total = 0;
        $producto= array();
        $producto["id"] = $request->id;
        $producto["qty"] = $request->qty;
        $producto["descuento"] = $request->descuento;
        $producto["ice"] = $request->ice;
        $producto["irbpnr"] = $request->irbpnr;
        $producto["name"] = $prod->name;
        $producto["price"] = $prod->price;
        $producto["tax_id"] = $prod->tax_id;
        $producto["code"] = $prod->code;
        $producto["tax_method"] = $prod->tax_method;
        $total = ($request->qty * $prod->price) - $request->descuento;
        if($prod->tax_id==1){
            $producto["iva"] ="SI";
            if($prod->tax_method==1){
                $valorIva = $total * 0.12;
                $producto["valorIva"] =number_format($valorIva,2);
            }else{
                $valorSinIva = $total / 1.12;
                $valorIva = $total -$valorSinIva;
                $producto["valorIva"] =number_format($valorIva,2);
            }
        }else{
            $producto["iva"] ="NO";
            $producto["valorIva"] =0.00;
        }
        $producto["total"]= $total;
        
        
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
        return view('nota_credito.table',$data);

    }

    
    public function obtenerProductoDetalle(){
        $productos = session("productos");
        if (!$productos) {
            $productos = [];
        }
        return $productos;
    }

    private function obtenerProductos()
    {
        $productos = session("productos");
        if (!$productos) {
            $productos = [];
        }
        return $productos;
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


    private function guardarProductos($productos)
    {
        session(["productos" => $productos,
        ]);
    }

    public function quitarProducto(Request $request)
    {
        $indice = $request->indice;
        $productos = $this->obtenerProductos();
        array_splice($productos, $indice, 1);
        $this->guardarProductos($productos);
        $data["productos"]= $this->obtenerProductos();
        return view("nota_credito.table");
    }
    public function getProduct($id)
    {
        return $producto = Product::where('id',$id)->first();
        
    }

    public function InsertarNotaDebito(Request $request){
        try {
            $emisor = Emisor::where('is_active',1)->first();
            $dataResponse = array();
            $dataInsert=array();
            $dataInsert["customer_id"]=$request->customer_id;
            $dataInsert["ambiente"] = $emisor->ambiente;
            $dataInsert["warehouse_id"]=$request->warehouse_id;
            $dataInsert["user_id"]=Auth::user()->id;
            $dataInsert["tipo_documento"]=$request->tipo_documento;
            $dataInsert["fecha_emision"]=$request->fecha_emision;
            $dataInsert["numero_comprobante"]=$request->numero_comprobante;
            $dataInsert["motivo"]=$request->motivo;
            //$dataInsert["tipo_identificacion"]=$request->tipo_identificacion;
            $dataInsert["subtotal_iva12"]=$request->subtotal12;
            $dataInsert["subtotal_iva0"]=$request->subtotal0;
            $dataInsert["total_discount"]=$request->totaldescuento;
            $dataInsert["subtotal_noiva"]=$request->subtotalnoiva;
            $dataInsert["subtotal_extiva"]=$request->subtotalexentoiva;
            $dataInsert["total_price"]=$request->subtotalSinImpuestos;
            $dataInsert["total_ice"]=$request->valorice;
            $dataInsert["total_irbpnr"]=$request->valorIrbpnr;
            $dataInsert["grand_total"]=$request->valortotal;
            $dataInsert["fecha_emision_documento"]=$request->fecha_emision_documento;
            $warehouse = Warehouse::where('id',$request->warehouse_id)->first();
            $secuencial = 0;
            $lastNote= NotaCredito::where('ambiente',$emisor->ambiente)->latest()->first();
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
            $dataInsert["numero_nota"]=$warehouse->codigo."-001-".$secuencial_final;
            $dataInsert["estado_sri"]="creado";
            $nota_credito = NotaCredito::create($dataInsert);
            if($nota_credito){
                $productos_nota= $this->obtenerProductos();
                
                    foreach ($productos_nota as $key => $value) {
                        $dataProductoNota = array();
                        $dataProductoNota["product_id"] = $value["id"];
                        $dataProductoNota["nota_credito_id"] = $nota_credito->id;
                        $dataProductoNota["qty"] = $value["qty"];
                        $dataProductoNota["net_unit_price"] = $value["price"];
                        $dataProductoNota["discount"] = $value["descuento"];
                        if($value["iva"]=="SI"){
                            $dataProductoNota["tax_rate"] = 12;
                        }else{
                            $dataProductoNota["tax_rate"] = 0;
                        }
                        $dataProductoNota["tax"] = $value["valorIva"];
                        $dataProductoNota["total"] = $value["total"] + $value["valorIva"];
                        $dataProductoNota["ice"] = $value["ice"];
                        $dataProductoNota["irbpnr"] = $value["irbpnr"];
                        ProductNotaCredito::create($dataProductoNota);
                    }
                
                $this->guardarProductos(null);
            }else{
                $dataResponse["status"]=false;
                $dataResponse["message"]="Error al crear Nota de crédito";
                return $dataResponse;
            }
            $this->generarNotaCreditoXml($nota_credito->id);
            $dataResponse["status"]=true;
            $dataResponse["message"]="Nota de crédito creado con éxito";
            return $dataResponse;
        } catch (\Throwable $th) {
            return $th;
        }
    }


    public function generarNotaCreditoXml($id,$esEdit =false){
        
        $notaCredito = NotaCredito::where('id',$id)->first();
        $data_warehause= Warehouse::where('id',$notaCredito->warehouse_id)->first();
        $emisor= Emisor::where('is_active',1)->first();
        $cliente = Customer::where('id',$notaCredito->customer_id)->first();
        $sec =explode('-',$notaCredito->numero_nota);
        $notaCredito['emisor'] = $emisor;
        $notaCredito['dirEstablecimiento'] = $data_warehause->address;
        $notaCredito['dirMatriz']=$emisor->direccion_matriz;
        $notaCredito['codDoc']="04";
        $notaCredito['fechaEmision']=$notaCredito->fecha_emision; 
        $notaCredito['tipoIdentificacionComprador']=$cliente->tipo_identificacion;
        $notaCredito["codDocModificado"] = $notaCredito->tipo_documento;
        $notaCredito["numDocModificado"] = $notaCredito->numero_comprobante;
        $notaCredito["fechaEmisionDocSustento"] = $notaCredito->fecha_emision_documento;
        $notaCredito["totalSinImpuestos"] = $notaCredito->total_price;
        $notaCredito["valorModificacion"] = $notaCredito->grand_total;
        $notaCredito['estab']=$data_warehause->codigo;
        $notaCredito['ptoEmi']="001";
        $notaCredito['codigo_numerico']=$data_warehause->codigo_numerico;

        $notaCredito['secuencial']=$sec[2];
        $notaCredito['cliente']=Customer::where('id',$notaCredito->customer_id)->first();
        $notaCredito['detalle'] =DB::table('products')
        ->join('product_nota_credito', 'products.id', '=', 'product_nota_credito.product_id')
        ->where([
            ['products.is_active', true],
            ['product_nota_credito.nota_credito_id', $id]
        ])->select('product_nota_credito.*','products.name','products.code')->get();
        
        //return $notaCredito;
         $notaCredito = $this->comprobante->generarNotaCreadito($notaCredito);
        //return json_encode($notaCredito);
         $claveAcceso= $this->getClaveAcceso($notaCredito);
        $xml = $this->facturacion->generarNotaCreditoXml($notaCredito);
         if($esEdit){
            Storage::delete('nota_credito/'.$claveAcceso.'.xml');
         }
       // Storage::put('nota_credito/'.$claveAcceso.'.xml', $xml);
        $not = NotaCredito::find($id);
        $not->clave_acceso= $claveAcceso;
        $not->save();

        if($not){
            File::put(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$claveAcceso.'.xml') , $xml);
         }

    }

    public function eliminarDocumento($id){
        $nota = NotaCredito::find($id);
        $nota->delete();
        $detalle = DB::table('product_nota_credito')
                    ->where('nota_credito_id',$id)
                    ->delete();
        return response()->json(true);
    }

    public function anular($id){
        $nota = NotaCredito::find($id);
        $nota->estado_sri="anulado";
        $nota->save();
        return response()->json($nota);;
    }

    private function getClaveAcceso($documento){
        $infoTrib =    $documento->infoTributaria;
         $claveAcceso = $infoTrib->claveAcceso;
        return $claveAcceso;
    }
    

    
}

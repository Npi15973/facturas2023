<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\NotaDebito;
use App\MotivosNotaDebito;
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

class NotaDebitoController extends Controller
{
    
    private $comprobante;
    private $facturacion;
    public function __construct(Comprobante $comprobante, FacturacionElectronica $facturacion){
        $this->comprobante=$comprobante;
        $this->facturacion=$facturacion;
    }


    public function index(){

         
        $emisor = Emisor::where('is_active',1)->first();
        $lims_nota_debito = DB::table('nota_debito as t1')
                                ->join('customers as t2' ,'t1.customer_id','=','t2.id')
                                ->where('t1.ambiente',$emisor->ambiente)
                                ->select('t1.*','t2.name')
                                ->orderBy('t1.id', 'desc')
                                ->get();
  

        
        return view('nota_debito.index',compact('lims_nota_debito'));
        
    }


    public function create(){

        $this->guardarMotivos(null);
        $lims_product_data = Product::where('type', ['standard'])->where('is_active', true)->get();
        $lims_customer_list = Customer::where('is_active', true)->where('name' ,'not like','%CONSUMIDOR%')->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $emisor = Emisor::where('is_active',1)->first();
        $role = Role::find(Auth::user()->role_id);
        $tipo_documento= TipoDocumento::all();
        
        return view('nota_debito.create',compact('lims_tax_list','lims_warehouse_list',
                'lims_biller_list','emisor','lims_customer_list','tipo_documento','lims_product_data'));
    }


    public function AgregarMotivoTabla(Request $request){
        $motivo= array();
        $motivo["razon"] = $request->razon;
        $motivo["valor"] = $request->valor;
        $motivos = $this->obtenerMotivos();
        array_push($motivos, $motivo);
        $this->guardarMotivos($motivos);
        $motivos = $this->obtenerMotivos();
        $data["motivos"]= $motivos;
        return view('nota_debito.table',$data);
    }

    public function GuardarDocumento(Request $request){
            $dataResponse = array();
            $dataInsert=array();
            $emisor=Emisor::where('is_active',1)->first();
            $clinte = Customer::where('id',$request->customer_id)->first();
            $dataInsert["customer_id"]=$request->customer_id;
            $dataInsert["ambiente"]=$emisor->ambiente;
            $dataInsert["warehouse_id"]=$request->warehouse_id;
            $dataInsert["user_id"]=Auth::user()->id;
            $dataInsert["tipo_documento"]=$request->tipo_documento;
            $dataInsert["fecha_emision"]=$request->fecha_emision;
            $dataInsert["numero_comprobante"]=$request->numero_comprobante;
            $dataInsert["tipo_identificacion"]=$clinte->tipo_identificacion;
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
            $lastNote= NotaDebito::where('ambiente',$emisor->ambiente)->latest()->first();
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
            $nota_debito = NotaDebito::create($dataInsert);
            if($nota_debito){
                $motivos_nota= $this->obtenerMotivos();
                
                    foreach ($motivos_nota as $key => $value) {
                        $dataMotivoNota = array();
                        $dataMotivoNota["razon"] = $value["razon"];
                        $dataMotivoNota["valor"] = $value["valor"];
                        $dataMotivoNota["nota_debito_id"] = $nota_debito->id;
                        MotivosNotaDebito::create($dataMotivoNota);
                    }
                
                $this->guardarMotivos(null);
            }else{
                $dataResponse["status"]=false;
                $dataResponse["message"]="Error al crear Nota de crédito";
                return response()->json($dataResponse);
            }
            $this->generarNotaDebitoXml($nota_debito->id);
            $dataResponse["status"]=true;
            $dataResponse["message"]="Nota de crédito creado con éxito";
            return response()->json($dataResponse);
    }


    public function generarNotaDebitoXml($id, $esEdit=false){

        $nota_debito = NotaDebito::where('id',$id)->first();
        $emisor= Emisor::where('is_active',1)->first();
        $warehouse= Warehouse::where('id',$nota_debito->warehouse_id)->first();
        $cliente = Customer::where('id',$nota_debito->customer_id)->first();
        
        $dataXml = array();
        $sec =explode('-',$nota_debito->numero_nota);
        $clave_acceso=$this->facturacion->GenerarClaveDeAccesos($nota_debito->fecha_emision,"05",$emisor->ruc,$nota_debito->ambiente,$emisor->serie,$sec[2],$warehouse->codigo_numerico,$emisor->tipo_emision);
        $infoTributaria = array();  
        $infoTributaria["ambiente"] = $nota_debito->ambiente;
        $infoTributaria["tipoEmision"] = $emisor->tipo_emision;
        $infoTributaria["razonSocial"] = $emisor->razon_social;
        $infoTributaria["nombreComercial"] = $emisor->nombre_comercial;
        $infoTributaria["claveAcceso"] = $clave_acceso;
        $infoTributaria["ruc"] = $emisor->ruc;
        $infoTributaria["codDoc"] = "05";
        $infoTributaria["estab"] = $warehouse->codigo;
        $infoTributaria["ptoEmi"] = "001";
        $infoTributaria["secuencial"] = $sec[2];
        $infoTributaria["dirMatriz"] = $emisor->direccion_matriz;
        $dataXml["infoTributaria"]= $infoTributaria;
        
        $infoNotaDebito=array();
        $infoNotaDebito["fechaEmision"]= $nota_debito->fecha_emision;
        $infoNotaDebito["dirEstablecimiento"]= $warehouse->address;
        $infoNotaDebito["tipoIdentificacionComprador"]= $cliente->tipo_identificacion;
        $infoNotaDebito["razonSocialComprador"]= $cliente->name;
        $infoNotaDebito["identificacionComprador"]= $cliente->tax_no;
        $infoNotaDebito["obligadoContabilidad"]= $emisor->obligado_contabilidad;
        $infoNotaDebito["codDocModificado"]= $nota_debito->tipo_documento;
        $infoNotaDebito["numDocModificado"]= $nota_debito->numero_comprobante;
        $infoNotaDebito["fechaEmisionDocSustento"]= $nota_debito->fecha_emision_documento;
        $infoNotaDebito["totalSinImpuestos"]= number_format($nota_debito->total_price,2);
        
        $impuestos = array();
        //iva
        $impuestos[0]["codigo"]= "2";
        $impuestos[0]["codigoPorcentaje"]= "0";
        $impuestos[0]["tarifa"]= "0";
        $impuestos[0]["baseImponible"]= number_format($nota_debito->total_price,2);
        $impuestos[0]["valor"]= "0";
        $infoNotaDebito["impuestos"]= $impuestos;
        $infoNotaDebito["valorTotal"]=number_format($nota_debito->grand_total,2);    
        $pagos = array();
        $pagos[0]["formaPago"]="01";
        $pagos[0]["total"]=number_format($nota_debito->grand_total, 2);
        $pagos[0]["plazo"]="30";
        $pagos[0]["unidadTiempo"]="días";
        $infoNotaDebito["pagos"]= $pagos;
        $dataXml["infoNotaDebito"]= $infoNotaDebito;
        $detalle_nota = MotivosNotaDebito::where('nota_debito_id',$nota_debito->id)->get();

        $motivos= array();
        foreach ($detalle_nota as $key => $value) {
            $motivo = array();
            $motivo["razon"]= $value->razon;
            $motivo["valor"]= number_format($value->valor,2);
            $motivos[$key] = $motivo;
        }
        $dataXml["motivos"]= $motivos;
        $infoAdicional=array();
        $infoAdicional["telefono"] = $cliente->phone_number;
        $infoAdicional["email"]=$cliente->email;
        $dataXml["infoAdicional"]= $infoAdicional;
        //return $cliente;
        $xml = $this->facturacion->generarNotaDebitoXml($dataXml);
        if($esEdit){
            Storage::delete('nota_debito/'.$clave_acceso.'.xml');
         }
        Storage::put('nota_debito/'.$clave_acceso.'.xml', $xml);
        $not = NotaDebito::find($id);
        $not->clave_acceso= $clave_acceso;
        $not->save();
        if($not){
            File::put(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$clave_acceso.'.xml') , $xml);
         }
        //return $xml;

    }


    public function eliminarDocumento($id){
        $nota = NotaDebito::find($id);
        $nota->delete();
        $detalle = DB::table('motivos_nota_debito')
                    ->where('nota_debito_id',$id)
                    ->delete();

        return response()->json(true);
    }

    public function anular($id){
        $nota = NotaDebito::find($id);
        $nota->estado_sri="anulado";
        $nota->save();
        return response()->json($nota);;
    }
    /**
     * metodos privados
     */

    public function quitarMotivo(Request $request)
    {
        $indice = $request->indice;
        $motivos = $this->obtenerMotivos();
        array_splice($motivos, $indice, 1);
        $this->guardarMotivos($motivos);
        $data["motivos"]= $this->obtenerMotivos();
        return view("nota_debito.table", $data);
    }
    private function guardarMotivos($motivos)
    {
        session(["motivos" => $motivos,
        ]);
    }

    private function buscarIndiceMotivo($id, $motivos)
    {
        foreach ($motivos as $indice => $motivo) {
            if ($motivo["id"] === $id) {
                return $indice;
            }
        }
        return -1;
    }
    private function obtenerMotivos(){
        $motivos = session("motivos");
        if (!$motivos) {
            $motivos = [];
        }
        return $motivos;
    }

    

    private function getClaveAcceso($documento){
        $infoTrib =    $documento->infoTributaria;
         $claveAcceso = $infoTrib->claveAcceso;
        return $claveAcceso;
    }

    
}

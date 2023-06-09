<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Warehouse;
use App\Supplier;
use App\Product;
use App\Unit;
use App\Tax;
use App\Account;
use App\Purchase;
use App\ProductPurchase;
use App\Product_Warehouse;
use App\Payment;
use App\PaymentWithCheque;
use App\PaymentWithCreditCard;
use App\PosSetting;
use DB;
use App\GeneralSetting;
use Stripe\Stripe;
use Auth;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\FormaDePago;
use App\TipoDocumento;
use App\FacturacionElectronica\Comprobante;
use App\Librerias\FacturacionElectronica;
use App\Biller;
use App\Emisor;
use Illuminate\Support\Facades\File;

class PurchaseController extends Controller
{


    private $comprobante;
    private $liquidacion;
    public function __construct(Comprobante $comprobante, FacturacionElectronica $liquidacion){
        $this->comprobante=$comprobante;
        $this->liquidacion=$liquidacion;
    }
    
    public function index()
    {
        
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-index')){
            $general_setting = DB::table('general_settings')->first();
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->where('liquidacion',0)->where('user_id', Auth::id())->get();
            else
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->where('liquidacion',0)->get();
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            //return $lims_purchase_list;
            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_account_list = Account::where('is_active', true)->get();
            return view('purchase.index', compact('lims_purchase_list', 'lims_account_list', 'all_permission', 'lims_pos_setting_data'));
        
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo');
    }

    public function liquidacion(){
        $emisor = Emisor::where('is_active',1)->first();
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list = Product::where([
                                    ['is_active', true],
                                    ['type', 'standard']
                                ])->get();
            $formas_pago= FormaDePago::all();
            $tipo_documento= TipoDocumento::all();
            

            $general_setting = DB::table('general_settings')->first();
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->where('ambiente',$emisor->ambiente)->where('liquidacion',1)->where('user_id', Auth::id())->get();
            else
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->where('ambiente',$emisor->ambiente)->where('liquidacion',1)->get();
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            
            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_account_list = Account::where('is_active', true)->get();
            
            return view('liquidacion.create', compact('lims_supplier_list', 
            'lims_warehouse_list', 'lims_tax_list', 'lims_product_list',
            'formas_pago','tipo_documento','lims_purchase_list', 'lims_account_list', 'all_permission', 'lims_pos_setting_data'));
    }
}


    public function liquidacionData(Request $request){


        //return "Hola";
        $emisor = Emisor::where('is_active',1)->first();
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'grand_total',
            6 => 'paid_amount',
        );
        $general_setting = GeneralSetting::latest()->first();
        if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
            $totalData = Purchase::where('user_id', Auth::id())->where('liquidacion',1)->count();
        else
            $totalData = Purchase::where('liquidacion',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))){
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $purchases = Purchase::offset($start)
                            ->where('user_id', Auth::id())
                            ->where('liquidacion',1)
                            ->where('ambiente',$emisor->ambiente)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            else
                $purchases = Purchase::offset($start)
                            ->where('ambiente',$emisor->ambiente)
                            ->where('liquidacion',1)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();

        }
        else
        {
            $search = $request->input('search.value');
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own') {
                $purchases =  Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('user_id', Auth::id())
                            ->where('ambiente',$emisor->ambiente)
                            ->where('liquidacion',1)
                            ->orwhere([
                                ['reference_no', 'LIKE', "%{$search}%"],
                                ['user_id', Auth::id()]
                            ])
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('user_id', Auth::id())
                            ->where('ambiente',$emisor->ambiente)
                            ->where('liquidacion',1)
                            ->orwhere([
                                ['reference_no', 'LIKE', "%{$search}%"],
                                ['user_id', Auth::id()]
                            ])->count();
            }
            else {
                $purchases =  Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->orwhere('reference_no', 'LIKE', "%{$search}%")
                            ->where('ambiente',$emisor->ambiente)
                            ->offset($start)
                            ->where('liquidacion',1)
                            ->limit($limit)
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                                ->orwhere('reference_no', 'LIKE', "%{$search}%")
                                ->where('ambiente',$emisor->ambiente)
                                ->where('liquidacion',1)
                                ->count();
            }
        }
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key=>$purchase)
            {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date($general_setting->date_format, strtotime($purchase->created_at->toDateString()));
                $nestedData['reference_no'] = $purchase->reference_no;
                $nestedData['clave_acceso'] = $purchase->clave_acceso;
                //$nestedData['estado_sri'] = $purchase->estado_sri;
                $warehouse = Warehouse::find($purchase->warehouse_id);

                if($purchase->supplier_id){
                    $supplier = Supplier::find($purchase->supplier_id);
                }
                else{
                    $supplier = new Supplier();
                    $supplier->name = $supplier->company_name = $supplier->email = $supplier->phone_number = $supplier->address = $supplier->city = '';

                }
                $nestedData['supplier'] = $supplier->name;
                if($purchase->status == 1){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.trans('file.Recieved').'</div>';
                    $purchase_status = trans('file.Recieved');
                }
                elseif($purchase->status == 2){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.trans('file.Partial').'</div>';
                    $purchase_status = trans('file.Partial');
                }
                elseif($purchase->status == 3){
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $purchase_status = trans('file.Pending');
                }
                else{
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.trans('file.Ordered').'</div>';
                    $purchase_status = trans('file.Ordered');
                }

                if($purchase->payment_status == 1)
                    $nestedData['payment_status'] = '<div class="badge badge-danger">'.trans('file.Due').'</div>';
                else
                    $nestedData['payment_status'] = '<div class="badge badge-success">'.trans('file.Paid').'</div>';

                if($purchase->estado_sri=="creado"){
                    $nestedData['estado_sri'] = '<div class="badge badge-warning">Creado</div>';

                }
                if($purchase->estado_sri=="autorizado"){
                    $nestedData['estado_sri'] = '<div class="badge badge-success">Autorizado</div>';

                }

                $botonEnviarSri="";
                $botonEnviarMailCliente="";
                if($purchase->estado_sri=="creado"){
                    $botonEnviarSri = '
                    <form action="/procesarComprobante" method="POST">
                                            <input type="hidden" name="documentId" value="'.$purchase->id.'">
                                            <input type="hidden" name="documentType" value="03">
                                            <input type="hidden" name="_token" value="'.csrf_token() .'" />
                                            <button type="submit"  class="btn btn-link"><i class="fa fa-paper-plane"></i>Enviar SRI</button>
                                        </form>';
                }else{
                    $botonEnviarMailCliente='<li>
                    <button type="button" onclick="enviarRideCliente('.$purchase->id.')" class="btn btn-link"><i class="fa fa-paper-plane"></i>Renviar Mail</button>
                </li>';
                }
                $nestedData['grand_total'] = number_format($purchase->grand_total, 2);
                $nestedData['paid_amount'] = number_format($purchase->paid_amount, 2);
                $nestedData['due'] = number_format($purchase->grand_total - $purchase->paid_amount, 2);
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i>
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">'.$botonEnviarSri.''.$botonEnviarMailCliente.'<li>
                            <button type="button" onclick="GenerarPdf('.$purchase->id.',\'03\')" class="btn btn-link"><i class="fa fa-eye"></i>Generar Descargar PDF</button>
                            </li><li>
                            <button type="button" onclick="DescargarXml('.$purchase->id.',\'03\')" class="btn btn-link"><i class="fa fa-download"></i>Descargar Xml</button>
                            </li>';
                if(in_array("purchases-edit", $request['all_permission']))
                    
                $nestedData['options'] .=
                    '<li>
                        <button type="button" class="add-payment btn btn-link" data-id = "'.$purchase->id.'" data-toggle="modal" data-target="#add-payment"><i class="fa fa-plus"></i> '.trans('file.Add Payment').'</button>
                    </li>
                    <li>
                        <button type="button" class="get-payment btn btn-link" data-id = "'.$purchase->id.'"><i class="fa fa-money"></i> '.trans('file.View Payment').'</button>
                    </li>';
                if(in_array("purchases-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["purchases.destroy", $purchase->id], "method" => "DELETE"] ).'
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="fa fa-trash"></i> '.trans("file.delete").'</button>
                            </li>'.\Form::close().'
                        </ul>
                    </div>';

                // data for purchase details by one click
                $user = User::find($purchase->user_id);
                $warehouse = Warehouse::find($purchase->warehouse_id);

                $nestedData['purchase'] = array( '[ "'.date($general_setting->date_format, strtotime($purchase->created_at->toDateString())).'"', ' "'.$purchase->reference_no.'"', ' "'.$purchase_status.'"',  ' "'.$purchase->id.'"', ' "'.$warehouse->name.'"', ' "'.$warehouse->phone.'"', ' "'.$warehouse->address.'"', ' "'.$supplier->name.'"', ' "'.$supplier->company_name.'"', ' "'.$supplier->email.'"', ' "'.$supplier->phone_number.'"', ' "'.$supplier->address.'"', ' "'.$supplier->city.'"', ' "'.$purchase->total_tax.'"', ' "'.$purchase->total_discount.'"', ' "'.$purchase->total_cost.'"', ' "'.$purchase->order_tax.'"', ' "'.$purchase->order_tax_rate.'"', ' "'.$purchase->order_discount.'"', ' "'.$purchase->shipping_cost.'"', ' "'.$purchase->grand_total.'"', ' "'.$purchase->paid_amount.'"', ' "'.$purchase->purchase_note.'"', ' "'.$user->name.'"', ' "'.$user->email.'"]'
                );
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    } 
    public function purchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'grand_total',
            6 => 'paid_amount',
        );
        $general_setting = GeneralSetting::latest()->first();
        if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
            $totalData = Purchase::where('user_id', Auth::id())->where('liquidacion',0)->count();
        else
            $totalData = Purchase::where('liquidacion',0)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))){
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $purchases = Purchase::offset($start)
                            ->where('user_id', Auth::id())
                            ->where('liquidacion',0)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            else
                $purchases = Purchase::offset($start)
                            ->where('liquidacion',0)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();

        }
        else
        {
            $search = $request->input('search.value');
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own') {
                $purchases =  Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('user_id', Auth::id())
                            
                            ->where('liquidacion',0)
                            ->orwhere([
                                ['reference_no', 'LIKE', "%{$search}%"],
                                ['user_id', Auth::id()]
                            ])
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('user_id', Auth::id())
                            
                            ->orwhere([
                                ['reference_no', 'LIKE', "%{$search}%"],
                                ['user_id', Auth::id()]
                            ])->count();
            }
            else {
                $purchases =  Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->orwhere('reference_no', 'LIKE', "%{$search}%")
                            
                            ->offset($start)
                            ->where('liquidacion',0)
                            ->limit($limit)
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Purchase::whereDate('created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                                ->orwhere('reference_no', 'LIKE', "%{$search}%")
                                
                                ->where('liquidacion',0)
                                ->count();
            }
        }
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key=>$purchase)
            {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date($general_setting->date_format, strtotime($purchase->created_at->toDateString()));
                $nestedData['reference_no'] = $purchase->reference_no;
                $warehouse = Warehouse::find($purchase->warehouse_id);

                if($purchase->supplier_id){
                    $supplier = Supplier::find($purchase->supplier_id);
                }
                else{
                    $supplier = new Supplier();
                    $supplier->name = $supplier->company_name = $supplier->email = $supplier->phone_number = $supplier->address = $supplier->city = '';

                }
                $nestedData['supplier'] = $supplier->name;
                if($purchase->status == 1){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.trans('file.Recieved').'</div>';
                    $purchase_status = trans('file.Recieved');
                }
                elseif($purchase->status == 2){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.trans('file.Partial').'</div>';
                    $purchase_status = trans('file.Partial');
                }
                elseif($purchase->status == 3){
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $purchase_status = trans('file.Pending');
                }
                else{
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.trans('file.Ordered').'</div>';
                    $purchase_status = trans('file.Ordered');
                }

                if($purchase->payment_status == 1)
                    $nestedData['payment_status'] = '<div class="badge badge-danger">'.trans('file.Due').'</div>';
                else
                    $nestedData['payment_status'] = '<div class="badge badge-success">'.trans('file.Paid').'</div>';

                $nestedData['grand_total'] = number_format($purchase->grand_total, 2);
                $nestedData['paid_amount'] = number_format($purchase->paid_amount, 2);
                $nestedData['due'] = number_format($purchase->grand_total - $purchase->paid_amount, 2);
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i>
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> '.trans('file.View').'</button>
                                </li>';
                if(in_array("purchases-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('purchases.edit', $purchase).'" class="btn btn-link"><i class="fa fa-edit"></i> '.trans('file.edit').'</a>
                        </li>';
                $nestedData['options'] .=
                    '<li>
                        <button type="button" class="add-payment btn btn-link" data-id = "'.$purchase->id.'" data-toggle="modal" data-target="#add-payment"><i class="fa fa-plus"></i> '.trans('file.Add Payment').'</button>
                    </li>
                    <li>
                        <button type="button" class="get-payment btn btn-link" data-id = "'.$purchase->id.'"><i class="fa fa-money"></i> '.trans('file.View Payment').'</button>
                    </li>';
                if(in_array("purchases-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["purchases.destroy", $purchase->id], "method" => "DELETE"] ).'
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="fa fa-trash"></i> '.trans("file.delete").'</button>
                            </li>'.\Form::close().'
                        </ul>
                    </div>';

                // data for purchase details by one click
                $user = User::find($purchase->user_id);
                $warehouse = Warehouse::find($purchase->warehouse_id);

                $nestedData['purchase'] = array( '[ "'.date($general_setting->date_format, strtotime($purchase->created_at->toDateString())).'"', ' "'.$purchase->reference_no.'"', ' "'.$purchase_status.'"',  ' "'.$purchase->id.'"', ' "'.$warehouse->name.'"', ' "'.$warehouse->phone.'"', ' "'.$warehouse->address.'"', ' "'.$supplier->name.'"', ' "'.$supplier->company_name.'"', ' "'.$supplier->email.'"', ' "'.$supplier->phone_number.'"', ' "'.$supplier->address.'"', ' "'.$supplier->city.'"', ' "'.$purchase->total_tax.'"', ' "'.$purchase->total_discount.'"', ' "'.$purchase->total_cost.'"', ' "'.$purchase->order_tax.'"', ' "'.$purchase->order_tax_rate.'"', ' "'.$purchase->order_discount.'"', ' "'.$purchase->shipping_cost.'"', ' "'.$purchase->grand_total.'"', ' "'.$purchase->paid_amount.'"', ' "'.$purchase->purchase_note.'"', ' "'.$user->name.'"', ' "'.$user->email.'"]'
                );
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }


    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list = Product::where([
                                    ['is_active', true],
                                    ['type', 'standard']
                                ])->get();

            
            return view('purchase.create', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_product_list'));
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo');
    }

    public function limsProductSearch(Request $request)
    {
        $todayDate = date('Y-m-d');
        $product_code = explode(" ", $request['data']);
        $lims_product_data = Product::where('code', $product_code[0])->first();

        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->cost;

        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            $product[] = $lims_tax_data->rate;
            $product[] = $lims_tax_data->name;
        } else {
            $product[] = 0;
            $product[] = 'No Tax';
        }
        $product[] = $lims_product_data->tax_method;

        $units = Unit::where("base_unit", $lims_product_data->unit_id)
                    
                    ->orWhere('id', $lims_product_data->unit_id)
                    ->get();
        $unit_name = array();
        $unit_operator = array();
        $unit_operation_value = array();
        foreach ($units as $unit) {
            if ($lims_product_data->purchase_unit_id == $unit->id) {
                array_unshift($unit_name, $unit->unit_name);
                array_unshift($unit_operator, $unit->operator);
                array_unshift($unit_operation_value, $unit->operation_value);
            } else {
                $unit_name[]  = $unit->unit_name;
                $unit_operator[] = $unit->operator;
                $unit_operation_value[] = $unit->operation_value;
            }
        }

        $product[] = implode(",", $unit_name) . ',';
        $product[] = implode(",", $unit_operator) . ',';
        $product[] = implode(",", $unit_operation_value) . ',';
        $product[] = $lims_product_data->id;
        return $product;
    }

    public function store(Request $request)
    {
        $liquidacion = $request->liquidacion;
        $emisor= Emisor::where('is_active',1)->first();
        $data = $request->except('document');        
        $data['user_id'] = Auth::id();
        $warehouse = Warehouse::where('id',$data['warehouse_id'])->first();
        $data['reference_no'] = $warehouse->cod_ref.'-' . date("Ymd") . '-'. date("his");
        $data["estado_sri"] = "creado";
        
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/purchase', $documentName);
            $data['document'] = $documentName;
        }

        if($liquidacion==1){
            $lastPurchase = Purchase::where('liquidacion',1)->where('ambiente',$emisor->ambiente)->latest()->first();
            if($lastPurchase){
                $secuencial = $lastPurchase->secuencial+1;
                
            }else{
                $tpoEmi = Biller::where('is_active',1)->first();
                $secuencial = $tpoEmi->secuencial_liquidacion;
            }
            $data['secuencial'] = $secuencial;
            $data['liquidacion'] = 1;
            $data['ambiente'] = $emisor->ambiente;
        }else{
            $data['liquidacion'] = 0;
        }
        
        //return $data;
        $purc= Purchase::create($data);

        $lims_purchase_data = Purchase::latest()->first();
        $product_id = $data['product_id'];
        $qty = $data['qty'];
        $recieved = $data['recieved'];
        $purchase_unit = $data['purchase_unit'];
        $net_unit_cost = $data['net_unit_cost'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $product_purchase = [];
        $i=0;

        foreach ($product_id as $id) {
            $lims_purchase_unit_data  = Unit::where('unit_name', $purchase_unit[$i])->first();

            if ($lims_purchase_unit_data->operator == '*') {
                $quantity = $recieved[$i] * $lims_purchase_unit_data->operation_value;
            } else {
                $quantity = $recieved[$i] / $lims_purchase_unit_data->operation_value;
            }
            //add quantity to product table
            $lims_product_data = Product::find($id);
            $lims_product_data->qty = $lims_product_data->qty + $quantity;
            $lims_product_data->save();
            //add quantity to warehouse
            $lims_product_warehouse_data = Product_Warehouse::where([
                ['product_id', $id],
                ['warehouse_id', $data['warehouse_id'] ],
                ])->first();
            if ($lims_product_warehouse_data) {
                $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $quantity;
            } else {
                $lims_product_warehouse_data = new Product_Warehouse();
                $lims_product_warehouse_data->product_id = $id;
                $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                $lims_product_warehouse_data->qty = $quantity;
            }

            $lims_product_warehouse_data->save();

            $product_purchase['purchase_id'] = $lims_purchase_data->id ;
            $product_purchase['product_id'] = $id;
            $product_purchase['qty'] = $qty[$i];
            $product_purchase['recieved'] = $recieved[$i];
            $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
            $product_purchase['net_unit_cost'] = $net_unit_cost[$i];
            $product_purchase['discount'] = $discount[$i];
            $product_purchase['tax_rate'] = $tax_rate[$i];
            $product_purchase['tax'] = $tax[$i];
            $product_purchase['total'] = $total[$i];
            ProductPurchase::create($product_purchase);
            $i++;
        }   
        if($liquidacion ==1){
            $this->generarXml($purc->id);
            return redirect('liquidacion/create')->with('message', 'Liquidación de compras creada con éxito');
        }
        return redirect('purchases')->with('message', 'Compra creada con éxito');
    }

    public function generarXml($id,$esEdit =false){
        //$liquidacion = Purchase::find($id);
        $liquidacion = Purchase::where('id',$id)->first();
        $data_warehause= Warehouse::where('id',$liquidacion->warehouse_id)->first();
        $emisor= Emisor::first();
        $liquidacion['emisor'] = $emisor;
        $liquidacion['dirEstablecimiento'] = $data_warehause->address;
        $liquidacion['dirMatriz']=$emisor->direccion_matriz;
        $liquidacion['codDoc']="03";
        $liquidacion['fechaEmision']=$liquidacion->fecha_emision; 
        $liquidacion['estab']=$data_warehause->codigo;
        //$ptoEmision = Biller::where('warehouse_id',$data_warehause->id)->first();
        $liquidacion['ptoEmi']="001";
        $liquidacion['codigo_numerico']=$data_warehause->codigo_numerico;
        $secuencial_inicial = $liquidacion->secuencial;
        $num_sec_ide =9;
        $num_sec = strlen($secuencial_inicial);
        $falta = $num_sec_ide - $num_sec;
        $alterno="0";
        for ($i=1; $i <$falta ; $i++) { 
            $alterno=$alterno."0";
        }
        $secuencial_final = $alterno.$secuencial_inicial;
        $liquidacion['secuencial']=$secuencial_final;
        $liquidacion['provedor']=Supplier::where('id',$liquidacion->supplier_id)->first();
        $liquidacion['detalle'] =DB::table('products')
        ->join('product_purchases', 'products.id', '=', 'product_purchases.product_id')
        ->where([
            ['products.is_active', true],
            ['product_purchases.purchase_id', $id]
        ])->select('product_purchases.*','products.name','products.code')->get();
        
        //return $liquidacion;
         $liquidacion = $this->comprobante->generarLiquidacionCompra($liquidacion);
        //return json_encode($liquidacion);
         $claveAcceso= $this->getClaveAcceso($liquidacion);
         $xml = $this->liquidacion->generarLiquidacionCompraXml($liquidacion);
         if($esEdit){
            Storage::delete('liquidacion/'.$claveAcceso.'.xml');
         }
        Storage::put('liquidacion/'.$claveAcceso.'.xml', $xml);
        $liq = Purchase::find($id);
        $liq->clave_acceso= $claveAcceso;
        $liq->save();
        if($liq){
            File::put(public_path('facturacion/facturacionphp/comprobantes/no_firmados/'.$claveAcceso.'.xml') , $xml);
         }

    }
    private function getClaveAcceso($documento){
        $infoTrib =    $documento->infoTributaria;
         $claveAcceso = $infoTrib->claveAcceso;
        return $claveAcceso;
    }
    public function productPurchaseData($id)
    {
        $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
        foreach ($lims_product_purchase_data as $key => $product_purchase_data) {
            $product = Product::find($product_purchase_data->product_id);
            $unit = Unit::find($product_purchase_data->purchase_unit_id);

            $product_purchase[0][$key] = $product->name . '-' . $product->code;
            $product_purchase[1][$key] = $product_purchase_data->qty;
            $product_purchase[2][$key] = $unit->unit_code;
            $product_purchase[3][$key] = $product_purchase_data->tax;
            $product_purchase[4][$key] = $product_purchase_data->tax_rate;
            $product_purchase[5][$key] = $product_purchase_data->discount;
            $product_purchase[6][$key] = $product_purchase_data->total;
        }
        return $product_purchase;
    }

    public function purchaseByCsv()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            
            return view('purchase.import', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list'));
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo');
    }

    public function importPurchase(Request $request)
    {
        //get the file
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        //checking if this is a CSV file
        if($ext != 'csv')
            return redirect()->back()->with('message', 'Please upload a CSV file');

        $filePath=$upload->getRealPath();
        $file_handle = fopen($filePath, 'r');
        $i = 0;
        //validate the file
        while (!feof($file_handle) ) {
            $current_line = fgetcsv($file_handle);
            if($current_line && $i > 0){
                $product_data[] = Product::where('code', $current_line[0])->first();
                if(!$product_data[$i-1])
                    return redirect()->back()->with('message', 'Product does not exist!');
                $unit[] = Unit::where('unit_code', $current_line[2])->first();
                if(!$unit[$i-1])
                    return redirect()->back()->with('message', 'Purchase unit does not exist!');
                if(strtolower($current_line[5]) != "no tax"){
                    $tax[] = Tax::where('name', $current_line[5])->first();
                    if(!$tax[$i-1])
                        return redirect()->back()->with('message', 'Tax name does not exist!');
                }
                else
                    $tax[$i-1]['rate'] = 0;

                $qty[] = $current_line[1];
                $cost[] = $current_line[3];
                $discount[] = $current_line[4];
            }
            $i++;
        }

        $data = $request->except('file');
        $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = $data['reference_no'] . '.' . $ext;
            $document->move('public/documents/purchase', $documentName);
            $data['document'] = $documentName;
        }
        $item = 0;
        $grand_total = $data['shipping_cost'];
        $data['user_id'] = Auth::id();
        
        Purchase::create($data);
        $lims_purchase_data = Purchase::latest()->first();

        foreach ($product_data as $key => $product) {
            if($product['tax_method'] == 1){
                $net_unit_cost = $cost[$key] - $discount[$key];
                $product_tax = $net_unit_cost * ($tax[$key]['rate'] / 100) * $qty[$key];
                $total = ($net_unit_cost * $qty[$key]) + $product_tax;
            }
            elseif($product['tax_method'] == 2){
                $net_unit_cost = (100 / (100 + $tax[$key]['rate'])) * ($cost[$key] - $discount[$key]);
                $product_tax = ($cost[$key] - $discount[$key] - $net_unit_cost) * $qty[$key];
                $total = ($cost[$key] - $discount[$key]) * $qty[$key];
            }
            if($data['status'] == 1){
                if($unit[$key]['operator'] == '*')
                    $quantity = $qty[$key] * $unit[$key]['operation_value'];
                elseif($unit[$key]['operator'] == '/')
                    $quantity = $qty[$key] / $unit[$key]['operation_value'];
                $product['qty'] += $quantity;
                $product_warehouse = Product_Warehouse::where([
                    ['product_id', $product['id']],
                    ['warehouse_id', $data['warehouse_id']]
                ])->first();
                $product_warehouse->qty += $quantity;
                $product->save();
                $product_warehouse->save();
            }

            $product_purchase = new ProductPurchase();
            $product_purchase->purchase_id = $lims_purchase_data->id;
            $product_purchase->product_id = $product['id'];
            $product_purchase->qty = $qty[$key];
            if($data['status'] == 1)
                $product_purchase->recieved = $qty[$key];
            else
                $product_purchase->recieved = 0;
            $product_purchase->purchase_unit_id = $unit[$key]['id'];
            $product_purchase->net_unit_cost = number_format((float)$net_unit_cost, 2, '.', '');
            $product_purchase->discount = $discount[$key] * $qty[$key];
            $product_purchase->tax_rate = $tax[$key]['rate'];
            $product_purchase->tax = number_format((float)$product_tax, 2, '.', '');
            $product_purchase->total = number_format((float)$total, 2, '.', '');
            $product_purchase->save();
            $lims_purchase_data->total_qty += $qty[$key];
            $lims_purchase_data->total_discount += $discount[$key] * $qty[$key];
            $lims_purchase_data->total_tax += number_format((float)$product_tax, 2, '.', '');
            $lims_purchase_data->total_cost += number_format((float)$total, 2, '.', '');
        }
        $lims_purchase_data->item = $key + 1;
        $lims_purchase_data->order_tax = ($lims_purchase_data->total_cost - $lims_purchase_data->order_discount) * ($data['order_tax_rate'] / 100);
        $lims_purchase_data->grand_total = ($lims_purchase_data->total_cost + $lims_purchase_data->order_tax + $lims_purchase_data->shipping_cost) - $lims_purchase_data->order_discount;
        $lims_purchase_data->save();
        return redirect('purchases');
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-edit')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list = Product::where([
                                    ['is_active', true],
                                    ['type', 'standard']
                                ])->get();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            
            return view('purchase.edit', compact('lims_warehouse_list', 'lims_supplier_list', 'lims_product_list', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data'));
        }
        else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo');

    }

    public function update(Request $request, $id)
    {
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $documentName = $document->getClientOriginalName();
            $document->move('public/purchase/documents', $documentName);
            $data['document'] = $documentName;
        }
        $balance = $data['grand_total'] - $data['paid_amount'];
        if ($balance < 0 || $balance > 0) {
            $data['payment_status'] = 1;
        } else {
            $data['payment_status'] = 2;
        }
        $lims_purchase_data = Purchase::find($id);

        $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

        $product_id = $data['product_id'];
        $qty = $data['qty'];
        $recieved = $data['recieved'];
        $purchase_unit = $data['purchase_unit'];
        $net_unit_cost = $data['net_unit_cost'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $product_purchase = [];

        foreach ($lims_product_purchase_data as $product_purchase_data) {

            $old_recieved_value = $product_purchase_data->recieved;
            $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

            if ($lims_purchase_unit_data->operator == '*') {
                $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
            } else {
                $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
            }
            $lims_product_data = Product::find($product_purchase_data->product_id);
            $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $product_purchase_data->product_id],
                    ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();

            $lims_product_data->qty -= $old_recieved_value;
            if($lims_product_warehouse_data){
                $lims_product_warehouse_data->qty -= $old_recieved_value;
                $lims_product_warehouse_data->save();
            }
            $lims_product_data->save();
            $product_purchase_data->delete();
        }

        foreach ($product_id as $key => $pro_id) {

            $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
            if ($lims_purchase_unit_data->operator == '*') {
                $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
            } else {
                $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
            }

            $lims_product_data = Product::find($pro_id);
            $lims_product_warehouse_data = Product_Warehouse::where([
                ['product_id', $pro_id],
                ['warehouse_id', $data['warehouse_id']],
                ])->first();

            $lims_product_data->qty += $new_recieved_value;
            if($lims_product_warehouse_data){
                $lims_product_warehouse_data->qty += $new_recieved_value;
                $lims_product_warehouse_data->save();
            }
            else {
                $lims_product_warehouse_data = new Product_Warehouse();
                $lims_product_warehouse_data->product_id = $pro_id;
                $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                $lims_product_warehouse_data->qty = $new_recieved_value;
                $lims_product_warehouse_data->save();
            }

            $lims_product_data->save();

            $product_purchase['purchase_id'] = $id ;
            $product_purchase['product_id'] = $pro_id;
            $product_purchase['qty'] = $qty[$key];
            $product_purchase['recieved'] = $recieved[$key];
            $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
            $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
            $product_purchase['discount'] = $discount[$key];
            $product_purchase['tax_rate'] = $tax_rate[$key];
            $product_purchase['tax'] = $tax[$key];
            $product_purchase['total'] = $total[$key];
            ProductPurchase::create($product_purchase);
        }

        $lims_purchase_data->update($data);
        return redirect('purchases')->with('message', 'Compra actualizada con éxito');
    }

    public function addPayment(Request $request)
    {
        $data = $request->all();
        $lims_purchase_data = Purchase::find($data['purchase_id']);
        $lims_purchase_data->paid_amount += $data['amount'];
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        if($data['paid_by_id'] == 1)
            $paying_method = 'Cash';
        elseif ($data['paid_by_id'] == 2)
            $paying_method = 'Gift Card';
        elseif ($data['paid_by_id'] == 3)
            $paying_method = 'Credit Card';
        else
            $paying_method = 'Cheque';

        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->purchase_id = $lims_purchase_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->payment_reference = 'ppr-' . date("Ymd") . '-'. date("his");
        $lims_payment_data->amount = $data['amount'];
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'];
        
        $lims_payment_data->save();

        $lims_payment_data = Payment::latest()->first();
        $data['payment_id'] = $lims_payment_data->id;
        
        if($paying_method == 'Credit Card'){
            //$lims_pos_setting_data = PosSetting::latest()->first();
            //return $lims_pos_setting_data;
          //  Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            //$token = $data['stripeToken'];
            $amount = $data['amount'];

            // Charge the Customer
            /*$charge = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
            ]);*/

            $data['charge_id'] = "999999999";

            PaymentWithCreditCard::create($data);
        }
        elseif ($paying_method == 'Cheque') {
            PaymentWithCheque::create($data);
        }
        return redirect('purchases')->with('message', 'Pago creado con éxito');
    }

    public function getPayment($id)
    {
        $lims_payment_list = Payment::where('purchase_id', $id)->get();
        $date = [];
        $payment_reference = [];
        $paid_amount = [];
        $paying_method = [];
        $payment_id = [];
        $payment_note = [];
        $cheque_no = [];
        $change = [];
        $paying_amount = [];
        $account_name = [];
        $account_id = [];
        $general_setting = GeneralSetting::latest()->first();
        foreach ($lims_payment_list as $payment) {
            $date[] = date($general_setting->date_format, strtotime($payment->created_at->toDateString())) . ' '. $payment->created_at->toTimeString();
            $payment_reference[] = $payment->payment_reference;
            $paid_amount[] = $payment->amount;
            $change[] = $payment->change;
            $paying_method[] = $payment->paying_method;
            $paying_amount[] = $payment->amount + $payment->change;
            if($payment->paying_method == 'Cheque'){
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id',$payment->id)->first();
                $cheque_no[] = $lims_payment_cheque_data->cheque_no;
            }
            else{
                $cheque_no[] = null;
            }
            $payment_id[] = $payment->id;
            $payment_note[] = $payment->payment_note;
            $lims_account_data = Account::find($payment->account_id);
            $account_name[] = $lims_account_data->name;
            $account_id[] = $lims_account_data->id;
        }
        $payments[] = $date;
        $payments[] = $payment_reference;
        $payments[] = $paid_amount;
        $payments[] = $paying_method;
        $payments[] = $payment_id;
        $payments[] = $payment_note;
        $payments[] = $cheque_no;
        $payments[] = $change;
        $payments[] = $paying_amount;
        $payments[] = $account_name;
        $payments[] = $account_id;

        return $payments;
    }

    public function updatePayment(Request $request)
    {
        $data = $request->all();
        $lims_payment_data = Payment::find($data['payment_id']);
        $lims_purchase_data = Purchase::find($lims_payment_data->purchase_id);
        //updating purchase table
        $amount_dif = $lims_payment_data->amount - $data['edit_amount'];
        $lims_purchase_data->paid_amount = $lims_purchase_data->paid_amount - $amount_dif;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        //updating payment data
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->amount = $data['edit_amount'];
        $lims_payment_data->change = $data['edit_paying_amount'] - $data['edit_amount'];
        $lims_payment_data->payment_note = $data['edit_payment_note'];
        if($data['edit_paid_by_id'] == 1)
            $lims_payment_data->paying_method = 'Cash';
        elseif ($data['edit_paid_by_id'] == 2)
            $lims_payment_data->paying_method = 'Gift Card';
        elseif ($data['edit_paid_by_id'] == 3){
            $lims_pos_setting_data = PosSetting::latest()->first();
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['edit_amount'];
            if($lims_payment_data->paying_method == 'Credit Card'){
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $lims_payment_data->id)->first();

                \Stripe\Refund::create(array(
                  "charge" => $lims_payment_with_credit_card_data->charge_id,
                ));

                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $lims_payment_with_credit_card_data->charge_id = $charge->id;
                $lims_payment_with_credit_card_data->save();
            }
            else{
                // Charge the Customer
                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $data['charge_id'] = $charge->id;
                PaymentWithCreditCard::create($data);
            }
            $lims_payment_data->paying_method = 'Credit Card';
        }
        else{
            if($lims_payment_data->paying_method == 'Cheque'){
                $lims_payment_data->paying_method = 'Cheque';
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $data['payment_id'])->first();
                $lims_payment_cheque_data->cheque_no = $data['edit_cheque_no'];
                $lims_payment_cheque_data->save();
            }
            else{
                $lims_payment_data->paying_method = 'Cheque';
                $data['cheque_no'] = $data['edit_cheque_no'];
                PaymentWithCheque::create($data);
            }
        }
        $lims_payment_data->save();
        return redirect('purchases')->with('message', 'Payment updated successfully');
    }

    public function deletePayment(Request $request)
    {
        $lims_payment_data = Payment::find($request['id']);
        $lims_purchase_data = Purchase::where('id', $lims_payment_data->purchase_id)->first();
        $lims_purchase_data->paid_amount -= $lims_payment_data->amount;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        if($lims_payment_data->paying_method == 'Credit Card'){
            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $request['id'])->first();
            $lims_pos_setting_data = PosSetting::latest()->first();


            $lims_payment_with_credit_card_data->delete();
        }
        elseif ($lims_payment_data->paying_method == 'Cheque') {
            $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $request['id'])->first();
            $lims_payment_cheque_data->delete();
        }
        $lims_payment_data->delete();
        return redirect('purchases')->with('not_permitted', 'Payment deleted successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $purchase_id = $request['purchaseIdArray'];
        foreach ($purchase_id as $id) {
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                        ])->first();

                $lims_product_data->qty -= $recieved_qty;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty -= $recieved_qty;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card"){
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    $lims_pos_setting_data = PosSetting::latest()->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
        }
        return 'Purchase deleted successfully!';
    }

    public function destroy($id)
    {   $liquidacion = 0;
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-delete')){
            $lims_purchase_data = Purchase::find($id);
            $liquidacion=$lims_purchase_data->liquidacion;
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                        ])->first();

                $lims_product_data->qty -= $recieved_qty;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty -= $recieved_qty;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card"){
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    $lims_pos_setting_data = PosSetting::latest()->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
            if($liquidacion==1){
                return redirect('liquidacion/create')->with('not_permitted', 'Liquidación de Compra eliminada con éxito');;
            }
            return redirect('purchases')->with('not_permitted', 'Compra eliminada con éxito');;
        }

    }
    
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Biller;
use Illuminate\Validation\Rule;
use Image;
use App\Mail\UserNotification;
use Illuminate\Support\Facades\Mail;
use App\Services\FacturacionElectronicaService;
use App\Warehouse;
use Auth;
use DB;

class BillerController extends Controller
{
    //injection
    private $facturacionService;


    public function __construct(FacturacionElectronicaService $facturacionService){
        $this->facturacionService =  $facturacionService;
    } 

    public function index()
    {
        

        $lims_biller_all = DB::table('billers')
        ->join('warehouses', 'billers.warehouse_id', '=', 'warehouses.id')
        ->select('billers.*','warehouses.name as establecimiento')
        ->where('billers.is_active',1)
        ->get();
        
        return view('biller.index',compact('lims_biller_all'));
    }

    public function create()
    {
      
      $warehouse= Warehouse::where('is_active', true)->get();
        return view('biller.create',compact('warehouse'));
    }

    public function store(Request $request)
    {
    	$this->validate($request, [
            'name' => [
                'max:255',
                    Rule::unique('billers')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $lims_biller_data = $request->all();
        $lims_biller_data['is_active'] = true;
        
        $establecimiento = Warehouse::where('id',$lims_biller_data['warehouse_id'])->first();
        $lims_biller_data["name"] = $lims_biller_data["name"] ."-".$establecimiento->codigo."-".$lims_biller_data["codigo"];
        Biller::create($lims_biller_data);
        $message = 'Emisor creado correctamente';
        return redirect('biller')->with('message', $message);
    }

    public function edit($id)
    {
        $warehouse= Warehouse::where('is_active', true)->get();
        $lims_biller_data = Biller::where('id',$id)->first();

        return view('biller.edit',compact('lims_biller_data','warehouse'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_name' => [
                'max:255',
                    Rule::unique('billers')->ignore($id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $input = $request->except('image','password','firma');
 
        $lims_biller_data = Biller::findOrFail($id);
        $lims_biller_data->update($input);
        return redirect('biller')->with('message','Punto de emision actualizado correctamente!');
    }

    public function importBiller(Request $request)
    {
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Please upload a CSV file');
        $filename =  $upload->getClientOriginalName();
        $filePath=$upload->getRealPath();
        //open and read
        $file=fopen($filePath, 'r');
        $header= fgetcsv($file);
        $escapedHeader=[];
        //validate
        foreach ($header as $key => $value) {
            $lheader=strtolower($value);
            $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        //looping through othe columns
        while($columns=fgetcsv($file))
        {
            if($columns[0]=="")
                continue;
            foreach ($columns as $key => $value) {
                $value=preg_replace('/\D/','',$value);
            }
           $data= array_combine($escapedHeader, $columns);

           $biller = Biller::firstOrNew(['company_name'=>$data['companyname']]);
           $biller->name = $data['name'];
           $biller->image = $data['image'];
           $biller->vat_number = $data['vatnumber'];
           $biller->email = $data['email'];
           $biller->phone_number = $data['phonenumber'];
           $biller->address = $data['address'];
           $biller->city = $data['city'];
           $biller->state = $data['state'];
           $biller->postal_code = $data['postalcode'];
           $biller->country = $data['country'];
           $biller->is_active = true;
          
           $biller->save();
           $message = 'Biller Imported successfully';
           if($data['email']){
                try{
                    Mail::send( 'mail.biller_create', $data, function( $message ) use ($data)
                    {
                        $message->to( $data['email'] )->subject( 'New Biller' );
                    });
                }
                catch(\Exception $e){
                    $message = 'Biller Imported successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
                }
            }
        }
        return redirect('biller')->with('message', $message);

    }

    public function deleteBySelection(Request $request)
    {
        $biller_id = $request['billerIdArray'];
        foreach ($biller_id as $id) {
            $lims_biller_data = Biller::find($id);
            $lims_biller_data->is_active = false;
            $lims_biller_data->save();
        }
        return 'Biller deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_biller_data = Biller::find($id);
        $lims_biller_data->is_active = false;
        $lims_biller_data->save();

        return redirect('biller')->with('not_permitted','Data deleted successfully');
    }

    public function getDataEmisorservice($ruc){
        $dataResult = $this->facturacionService->getDataEmisor($ruc);
        return $dataResult;
    }
    
}

@extends('layout.main')
 @section('content')


@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif


<div class="modal" id="AddMotivo">
    <div class="modal-dialog">
      <div class="modal-content">
  
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Detalle Comprobante Retención</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
  
        <!-- Modal body -->
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tipo Impuesto</th>
                        <th>Cod. Reten</th>
                        <th>%</th>
                        <th>Base Imp.</th>
                        <th>Total</th>
                        <th>Documento No.</th>
                        <th>Tipo Doc</th>
                        <th>Fecha Doc</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        
                        <td>
                            <select class="form-control btn-sm" id="tipo_retencion">
                                <option value="">Seleccione</option>
                                <option value="1">RENTA</option>
                                <option value="2">IVA</option>
                                <option value="6">ISD</option>
                            </select>
                        </td>
                        <td> 
                            <select class="form-control btn-sm" id="codigo_retencion" onchange="calcularPorcentaje(this.options[this.selectedIndex].getAttribute('isred'));" >
                                <option value="">Selccione</option> 
                                @foreach($codigos_retenciones as $key => $value)
                                    <option isred="{{$value->porcentaje}}" value="{{$value->codigo}}">{{$value->descripcion}}</option>
                                @endforeach   
                            </select>    
                        </td>
                        <td><input type="text"  class="form-control btn-sm" id="porcentaje" > </td>
                        <td> <input type="text" size="5" class="form-control btn-sm" onkeyup="calcularTotal();"  id="base_imponible"> </td>
                        <td><input type="text"  class="form-control btn-sm" id="total" > </td>
                        <td> <input type="text" size="5" class="form-control btn-sm"  id="numero_documento"> </td>
                        <td>
                            <select class="form-control btn-sm" id="tipo_documento">
                                <option value="">Seleccione</option>
                                <option value="01">FACTURA</option> 
                                <option value="03">LIQUIDACIÓN DE COMPRAS</option>  
                                <option value="05">NOTA DE DÉBITO</option>  
                            </select>    
                        </td>
                        <td> <input type="date" size="5" class="form-control btn-sm"  id="fecha_documento"> </td>

                    </tr>
                </tbody>
            </table>
          </div>
        </div>
  
        <!-- Modal footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="AgregarData();">Agregar</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal">Cancelar</button>
        </div>
  
      </div>
    </div>
  </div>
    <section>
        {!! Form::open(['route' => 'sales.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
            <div class="row">
                <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <table class="table">
                                                        <tr>
                                                            <th>Emisor: </th>
                                                            <td>{{$emisor->razon_social}}</td>
                                                            <th>Ruc: </th>
                                                            <td>{{$emisor->ruc}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Ambiente: </th>
                                                            @if($emisor->ambiente=="1")
                                                                 <td><span class="badge badge-secondary">Pruebas</span></td>
                                                            @else
                                                                <td><span class="badge badge-primary">Producción</span></td>
                                                            @endif
                                                            <th>Fecha Emisión: </th>
                                                            <td><input type="date" class="form-control" name="fecha_emision" id="fecha_emision"></td>    
                                                        </tr> 
                                                    </table>
                                                </div>
                                                
                                            </div>
                                        </div>
                                      </div>
                                </div>
                            </div>
                </div>
                    
             </div>
             <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Periodo Fiscal  (mm/yyyy):*</strong></label>
                                        <input type="text"  class="form-control" name="periodo_fiscal" id="periodo_fiscal" placeholder="mm/yyyy">   
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>{{trans('file.Warehouse')}} *</strong></label>
                                        <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione un establecimiento...">
                                            @foreach($lims_warehouse_list as $warehouse)
                                            <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!--<div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Sujeto Retenido Cliente*</strong></label>
                                        <select required name="customer_id" id="customer_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione un cliente...">
                                            @foreach($lims_customer_list as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name . ' (' . $customer->tax_no . ')'}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>-->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Sujeto Retenido {{trans('file.Supplier')}}</strong></label>
                                        <select name="supplier_id" id="supplier_id"  class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione provedor...">
                                            @foreach($lims_supplier_list as $supplier)
                                            <option value="{{$supplier->id}}">{{$supplier->name .' ('. $supplier->company_name .')'}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!--<div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Tipo de Identificación *</strong></label>
                                        <select required name="tipo_identificacion" id="tipo_identificacion" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione tipo de documento">
                                            @foreach($tipo_documento as $documento)                                            
                                            <option value="{{$documento->codigo}}">{{$documento->descripcion}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>-->
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="button" onclick="AbrirModalMotivos();" class="btn btn-default btn-sm">Agregar Detalle Retención</button>
                                </div>
                            </div>
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <h5>{{trans('file.Order Table')}} *</h5>
                                    <div class="table-responsive mt-3" id="Contenedor">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="row">   
                                <div class="col-lg-12">
                                    <button type="button" onclick="GuardarDocumento();" class="btn btn-default btn-sm">GUARDAR DOCUMENTO</button>
                                </div>
                            </div>
                            
                       
                    </div>
                {!! Form::close() !!}
                </section>
      




@section('scripts')
<script>
    
    
    $("ul#facturacion").siblings('a').attr('aria-expanded','true');
    $("ul#facturacion").addClass("show");
    $("ul#facturacion #retencion-create-menu").addClass("active"); 

    $('select[name="customer_id"]').on('change', function() {
           
            //alert($("#supplier_id").val());
            $("#supplier_id").val("")
            $('#supplier_id')
            .empty()
            .append('<option selected="selected" value="">text</option>');
      
    });


    function AbrirModalMotivos(){
        $("#AddMotivo").modal('show');
    }
    function AgregarData(){

        var codigo_retencion=$("#codigo_retencion").val();
        var tipo_retencion=$("#tipo_retencion").val();
        var porcentaje =$("#porcentaje").val();
        var base_imponible =$("#base_imponible").val();
        var total = $("#total").val();
        var numero_documento = $("#numero_documento").val();
        var tipo_documento=$("#tipo_documento").val();
        var fecha_documento = $("#fecha_documento").val();
        var  parametros={
            '_token':$('input[name=_token]').val(),
            'codigo_retencion':codigo_retencion,
            'tipo_retencion':tipo_retencion,
            'porcentaje':porcentaje,
            'base_imponible':base_imponible,
            'total':total,
            'numero_documento':numero_documento,
            'tipo_documento':tipo_documento,
            'fecha_documento':fecha_documento
          }



          $.ajax({
            url: "agregar",
            type: 'POST',
            data: parametros,
        }).done(function (respuesta) {

            $("#Contenedor").html("");
            $("#Contenedor").append(respuesta);
            $("#AddMotivo").modal('hide');
         
        });
    }

    function EliminarLinea(indice){
        var  parametros={
            '_token':$('input[name=_token]').val(),
            'indice':indice,
          }
        $.ajax({
            url: "eliminar",
            type: 'POST',
            data: parametros,
        }).done(function (respuesta) {

            $("#Contenedor").html("");
            $("#Contenedor").append(respuesta);
        });
    }


    
    function calcularPorcentaje(porcentaje){
       $("#porcentaje").val(porcentaje);
    }

    function calcularTotal(){

        var porcentaje = $("#porcentaje").val();
        var base_imponible = $("#base_imponible").val();
        var total = ((parseFloat(porcentaje)/100)*1) * parseFloat(base_imponible);
        $("#total").val(parseFloat(total).toFixed(2));
    }
   
     function GuardarDocumento(){
         var fecha_emision =$("#fecha_emision").val();
        var periodo_fiscal = $("#periodo_fiscal").val();
        var warehouse_id = $("#warehouse_id").val();
        var supplier_id= $("#supplier_id").val(); 
        var tipo_identificacion= $("#tipo_identificacion").val(); 
        var parametros={
            '_token':$('input[name=_token]').val(),
            'fecha_emision':fecha_emision,
            'periodo_fiscal':periodo_fiscal,
            'warehouse_id':warehouse_id,
            'supplier_id':supplier_id,
            'tipo_identificacion':tipo_identificacion
        };

        $.ajax({
            url: "insert",
            type: 'POST',
            data: parametros,
        }).done(function (respuesta) {
            window.location.href = "/retencion";
            console.log(respuesta);
        });
     }
</script>
@endsection
@endsection

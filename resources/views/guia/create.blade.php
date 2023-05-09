@extends('layout.main')
 @section('content')


@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif


<div class="modal" id="AddProduct">
    <div class="modal-dialog">
      <div class="modal-content">
  
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Añadir Producto</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
  
        <!-- Modal body -->
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Código</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="display: none"><input type="hidden" size="5" id="producto_id" ></td>
                        <td><input type="text" size="5" id="qty" value="1"> </td>
                        <td><span id="code" class="badge badge-secondary"></span></td>
                        <td><span id="name" class="badge badge-secondary"></span> </td>
                    </tr>
                </tbody>
            </table>
          </div>
        </div>
  
        <!-- Modal footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="AgregarData();">Agregar</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
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
                                                            <td><input type="date" class="form-control form-control-sm"  id="fecha_emision"></td>    
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
                                        <label><strong>Fecha Inicio Transporte *</strong></label>
                                        <input type="date" class="form-control"  id="fecha_inicio">  
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Fecha Fin Transporte *</strong></label>
                                        <input type="date" class="form-control "  id="fecha_fin">  
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Direccion de partida*</strong></label>
                                        <input type="text"  class="form-control " id="direccion_partida">   
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Nombre Transportista*</strong></label>
                                        <input type="text"  class="form-control " id="transportista">   
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Ruc Transportista*</strong></label>
                                        <input type="text"  class="form-control " id="ruc_transportista">   
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Placa *</strong></label>
                                        <input type="text"  class="form-control"  id="placa">   
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><strong>Motivo Traslado: *</strong></label>
                                        <textarea  id="motivo_traslado" cols="30" class="form-control form-control-sm" rows="3"></textarea>  
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>{{trans('file.Warehouse')}} *</strong></label>
                                        <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control form-control-sm" data-live-search="true" data-live-search-style="begins" title="Seleccione un establecimiento...">
                                            @foreach($lims_warehouse_list as $warehouse)
                                            <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Cliente*</strong></label>
                                        <select required name="customer_id" id="customer_id" class="selectpicker form-control form-control-sm" data-live-search="true" data-live-search-style="begins" title="Seleccione un cliente...">
                                            @foreach($lims_customer_list as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name . ' (' . $customer->tax_no . ')'}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>        
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Tipo de Identificación *</strong></label>
                                        <select required name="tipo_identificacion" id="tipo_identificacion" class="selectpicker form-control form-control-sm" data-live-search="true" data-live-search-style="begins" title="Seleccione tipo de documento">                              
                                            @foreach($tipo_documento as $documento)
                                            <option value="{{$documento->codigo}}">{{$documento->descripcion}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">                           
                                    <label><strong>Buscar Producto *</strong></label>
                                    <select required name="product_id" id="product_id"  onclick="SeccionarProducto(this.id);" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione un producto...">                                                   
                                        @foreach($lims_product_data as $product)                                                 
                                        <option value="{{$product->id}}">{{$product->name . ' (' . $product->code . ')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <h5>Tabla Productos a Transportar *</h5>
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
    $("ul#facturacion #guia-create-menu").addClass("active");
    
    function AbrirModalMotivos(){
        $("#AddMotivo").modal('show');
    }

    $('select[name="product_id"]').on('change', function() {
        var id = $(this).val();
        SeccionarProducto(id);
        $(this).val("");
    });

    function SeccionarProducto(id){
        
        $.ajax({
          url: "productos/"+id,
          type: 'GET',
      }).done(function (respuesta) {

          $("#producto_id").val(respuesta.id)
          $("#qty").val(1);  
          $("#code").html(respuesta.code); 
          $("#name").html(respuesta.name);
          $("#price").html("$"+respuesta.price);         
          $("#AddProduct").modal('show');
          $("#product_id").val("");
          
      }
  ); 
    }
    
    function AgregarData(){
        var  parametros={
            '_token':$('input[name=_token]').val(),
            'id':$("#producto_id").val(),
            'qty':$("#qty").val(),
          }

          $.ajax({
            url: "agregar",
            type: 'POST',
            data: parametros,
        }).done(function (respuesta) {

            //console.log(respuesta);
            $("#Contenedor").html("");
            $("#Contenedor").append(respuesta);
            $("#AddProduct").modal('hide');
            //CalcularTotales();
        });
    }


    function EliminarLinea(indice){
        //alert(indice);
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
            //$("#AddMotivo").modal('hide');
          // CalcularTotales();
        });
    }

   

   
     function GuardarDocumento(){
        var warehouse_id = $("#warehouse_id").val();
        var fecha_emision = $("#fecha_emision").val();
        var fecha_inicio =$("#fecha_inicio").val();
        var fecha_fin =$("#fecha_fin").val();
        var direccion_partida =$("#direccion_partida").val();
        var transportista = $("#transportista").val();
        var ruc_transportista = $("#ruc_transportista").val();
        var placa = $("#placa").val();
        var motivo_traslado = $("#motivo_traslado").val();
        var customer_id= $("#customer_id").val(); 
        var tipo_identificacion= $("#tipo_identificacion").val();
      

    
        var parametros={
            '_token':$('input[name=_token]').val(),
            'warehouse_id':warehouse_id,
            'fecha_emision':fecha_emision,
            'fecha_inicio':fecha_inicio,
            'fecha_fin':fecha_fin,
            'direccion_partida':direccion_partida,
            'transportista':transportista,
            'ruc_transportista':ruc_transportista,
            'placa':placa,
            'motivo_traslado':motivo_traslado,
            'customer_id':customer_id,
            'tipo_identificacion':tipo_identificacion
            
        };

        $.ajax({
            url: "insert",
            type: 'POST',
            data: parametros,
        }).done(function (respuesta) {
            
            window.location.href = "/guia";
            console.log(respuesta);
        });
     }
</script>
@endsection
@endsection

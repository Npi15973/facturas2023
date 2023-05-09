@extends('layout.main')
 @section('content')


@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal_header" class="modal-title"></h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label><strong>{{trans('file.Quantity')}}</strong></label>
                        <input type="number" name="edit_qty" class="form-control" step="any">
                    </div>
                    <div class="form-group">
                        <label><strong>{{trans('file.Unit Discount')}}</strong></label>
                        <input type="number" name="edit_discount" class="form-control" step="any">
                    </div>
                    <div class="form-group">
                        <label><strong>{{trans('file.Unit Price')}}</strong></label>
                        <input type="number" name="edit_unit_price" class="form-control" step="any">
                    </div>
                    <?php
            $tax_name_all[] = 'No Tax';
            $tax_rate_all[] = 0;
            foreach($lims_tax_list as $tax) {
                $tax_name_all[] = $tax->name;
                $tax_rate_all[] = $tax->rate;
            }
        ?>
                        <div class="form-group">
                            <label><strong>{{trans('file.Tax Rate')}}</strong></label>
                            <select name="edit_tax_rate" class="form-control">
                                @foreach($tax_name_all as $key => $name)
                                <option value="{{$key}}">{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="edit_unit" class="form-group">
                            <label><strong>{{trans('file.Product Unit')}}</strong></label>
                            <select name="edit_unit" class="form-control">
                            </select>
                        </div>
                        <button type="button" name="update_btn" class="btn btn-primary">{{trans('file.update')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>
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
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th>ICE</th>
                        <th>IRBPNR</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="display: none"><input type="hidden" size="5" id="product_id" ></td>
                        <td><input type="text" size="5" id="qty" value="1"> </td>
                        <td><span id="code" class="badge badge-secondary"></span></td>
                        <td><span id="name" class="badge badge-secondary"></span> </td>
                        <td><span id="price" class="badge badge-secondary"></span> </td>
                        <td><input type="text" size="5" id="descuento" value="0"></td>
                        <td><input type="text" size="5" id="ice" value="0"></td>
                        <td><input type="text" size="5" id="irbpnr" value="0"></td>
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
                                <div class="col-lg-12">
                                    <h5>Comprobante Modificado</h5>
                                    <hr>    
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Tipo Documento *</strong></label>
                                        <select required name="tipo_documento" id="tipo_documento" class="form-control"  title="Seleccione tipo documento...">
                                            <option value="">..Seleccione..</option>
                                            <option value="01">Factura</option>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Fecha emisión documento *</strong></label>
                                        <input type="date" class="form-control" name="fecha_emision" id="fecha_emision_documento">  
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Número documento *</strong></label>
                                        <input type="text"  class="form-control" name="numero_comprobante" id="numero_comprobante" placeholder="001-001-000000001">   
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Motivo *</strong></label>
                                        <input type="text"  class="form-control" name="motivo" id="motivo" placeholder="Ingrese motivo modificación">   
                                    </div>
                                </div>
                            </div>
                            <div class="row">
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>{{trans('file.customer')}} *</strong></label>
                                        <select required name="customer_id" id="customer_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione un cliente...">
                                            <?php $deposit = []; ?>
                                            @foreach($lims_customer_list as $customer)
                                            <?php $deposit[$customer->id] = $customer->deposit - $customer->expense; ?>
                                            <option value="{{$customer->id}}">{{$customer->name . ' (' . $customer->tax_no . ')'}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!--<div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Tipo de Identificación *</strong></label>
                                        <select required name="tipo_identificacion" id="tipo_identificacion" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione tipo de documento">
                                            <?php $deposit = []; ?>
                                            @foreach($tipo_documento as $documento)
                                            
                                            <option value="{{$documento->codigo}}">{{$documento->descripcion}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>-->
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
                                    <h5>{{trans('file.Order Table')}} *</h5>
                                    <div class="table-responsive mt-3" id="Contenedor">
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                    <div class="col-lg-12">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                              <a class="nav-link active" data-toggle="tab" href="#home">Totales</a>
                                            </li>
                                            
                                          </ul>
                                          
                                          <!-- Tab panes -->
                                          <div class="tab-content">
                                            <div class="tab-pane container active" id="home">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <tbody>
                                                            <tr>
                                                                <th style="width:20%">Subtotal Sin Impuestos:</th>
                                                                <td><input readonly type="text" name="subtotalSinImpuestos" id="subtotalSinImpuestos" value="0.00"> </td> 
                                                             </tr>
                                                            <tr>
                                                               <th style="width:20%">Subtotal 12%:</th>
                                                               <td><input readonly type="text" name="subtotal12" id="subtotal12" value="0.00"> </td> 
                                                            </tr>
                                                            <tr>
                                                                <th>Subtotal 0%:</th>
                                                                <td><input readonly type="text" name="subtotal0" id="subtotal0" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>Subtotal no Objeto de Iva:	</th>
                                                                <td><input readonly type="text" name="subtotalnoiva" id="subtotalnoiva" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>Subtotal Exento de Iva:	</th>
                                                                <td><input readonly type="text" name="subtotalexentoiva" id="subtotalexentoiva" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>Total Descuento:	</th>
                                                                <td><input readonly type="text" name="totaldescuento" id="totaldescuento" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>	IVA 12%:	</th>
                                                                <td><input readonly type="text" name="iva12" id="iva12" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>	Valor Ice:	</th>
                                                                <td><input readonly type="text" name="valorice" id="valorice" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>	Valor Irbpnr:	</th>
                                                                <td><input readonly type="text" name="valorIrbpnr" id="valorIrbpnr" value="0.00"> </td> 
                                                             </tr>
                                                             <tr>
                                                                <th>		Valor Total:	</th>
                                                                <td><input readonly type="text" name="valortotal" id="valortotal" value="0.00"> </td> 
                                                             </tr>
                                                        </tbody>
                                                    </table>
                                                    <div class="row">   
                                                        <div class="col-lg-12">
                                                            <button type="button" onclick="GuardarDocumento();" class="btn btn-primary btn-sm">GUARDAR DOCUMENTO</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane container fade" id="menu1">
                                                <h2>Pendiente</h2>
                                            </div>
                                          </div>
                                    </div>
                            </div>
                       
                    </div>
                {!! Form::close() !!}
                </section>
      




@section('scripts')
<script>
    
    $("ul#facturacion").siblings('a').attr('aria-expanded','true');
    $("ul#facturacion").addClass("show");
    $("ul#facturacion #nota-credito-create-menu").addClass("active");


    $('select[name="product_id"]').on('change', function() {
        var id = $(this).val();
        
        $("#product_id").val("");
        
        SeccionarProducto(id);
    });

    
    function SeccionarProducto(id){
        
          $.ajax({
            url: "productos/"+id,
            type: 'GET',
        }).done(function (respuesta) {

            $("#product_id").val(respuesta.id)
            $("#qty").val(1);  
            $("#code").html(respuesta.code); 
            $("#name").html(respuesta.name);
            $("#price").html("$"+respuesta.price);         
            $("#AddProduct").modal('show');
            
        }
    );  


    }
    function AgregarData(){
        var  parametros={
            '_token':$('input[name=_token]').val(),
            'id':$("#product_id").val(),
            'qty':$("#qty").val(),
            'descuento':$("#descuento").val(),
            'ice':$("#ice").val(),
            'irbpnr':$("#irbpnr").val(),
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
            CalcularTotales();
        });
    }
    function CalcularTotales(){

        var subTotalSinIva12Cl = $('.subTotalSinIva12');
        var subTotalSinIva0Cl = $(".subTotalSinIva0");
        var ivaValorCl = $('.valorIva');
        var descuentoCl = $('.descuento');
        var iceCl = $('.ice');
        var irbpnrCl = $('.irbpnr');

        var total=0.00;
        var SubtotalSinImpuesto =0.00;
        var subTotalSinIva12 =0.00;
        var subTotalSinIva0=0.00;
        var ivaValor=0.00;
        var descuento=0.00;
        var ice=0.00;
        var irbpnr=0.00;

        subTotalSinIva12Cl.each(function()
          {
            subTotalSinIva12 =subTotalSinIva12 + parseFloat($(this).val());   
          }); 
          subTotalSinIva0Cl.each(function(){
            subTotalSinIva0 = subTotalSinIva0 + parseFloat($(this).val());
          });
          ivaValorCl.each(function(){
            ivaValor = ivaValor + parseFloat($(this).val());
          });
          descuentoCl.each(function(){
            descuento = descuento + parseFloat($(this).val());
          });
          iceCl.each(function(){
            ice = ice + parseFloat($(this).val());
          });
          irbpnrCl.each(function(){
            irbpnr = irbpnr + parseFloat($(this).val());
          });
            SubtotalSinImpuesto= parseFloat(subTotalSinIva12) + parseFloat(subTotalSinIva0);
            $("#subtotalSinImpuestos").val(parseFloat(SubtotalSinImpuesto).toFixed(2));
            $("#subtotal12").val(parseFloat(subTotalSinIva12).toFixed(2));
            $("#subtotal0").val(parseFloat(subTotalSinIva0).toFixed(2));
            $("#subtotalnoiva").val(parseFloat(0.00).toFixed(2));
            $("#subtotalexentoiva").val(parseFloat(0.00).toFixed(2));
            $("#totaldescuento").val(parseFloat(descuento).toFixed(2));
            $("#iva12").val(parseFloat(ivaValor).toFixed(2));
            $("#valorice").val(parseFloat(ice).toFixed(2));
            $("#valorIrbpnr").val(parseFloat(irbpnr).toFixed(2));
             total = parseFloat(SubtotalSinImpuesto) + parseFloat(ivaValor);
            $("#valortotal").val(parseFloat(total).toFixed(2));
        }

    function eliminarItem(id){
        $("#"+id).remove();
        CalcularTotales();
    }


     function GuardarDocumento(){
        var warehouse_id = $("#warehouse_id").val();
        var fecha_emision_documento = $("#fecha_emision_documento").val();
        var tipo_documento= $("#tipo_documento").val(); 
        var customer_id= $("#customer_id").val(); 
     
        var numero_comprobante= $("#numero_comprobante").val();
        var motivo= $("#motivo").val();
        var fecha_emision = $("#fecha_emision").val();
        var subtotalSinImpuestos =$("#subtotalSinImpuestos").val();
        var subtotal12 =$("#subtotal12").val();
        var subtotal0 =$("#subtotal0").val();
        var subtotalnoiva =$("#subtotalnoiva").val();
        var subtotalexentoiva =$("#subtotalexentoiva").val();
        var totaldescuento =$("#totaldescuento").val();
        var iva12 =$("#iva12").val();
        var valorice =$("#valorice").val();
        var valorIrbpnr =$("#valorIrbpnr").val();
        var valortotal =$("#valortotal").val();

        var parametros={
            '_token':$('input[name=_token]').val(),
            'warehouse_id':warehouse_id,
            'fecha_emision_documento':fecha_emision_documento,
            'tipo_documento':tipo_documento,

            'customer_id':customer_id,
            'numero_comprobante':numero_comprobante,
            'motivo':motivo,
            'fecha_emision':fecha_emision,
            'subtotalSinImpuestos':subtotalSinImpuestos,
            'subtotal12':subtotal12,
            'subtotal0':subtotal0,
            'subtotalnoiva':subtotalnoiva,
            'subtotalexentoiva':subtotalexentoiva,
            'totaldescuento':totaldescuento,
            'iva12':iva12,
            'valorice':valorice,
            'valorIrbpnr':valorIrbpnr,
            'valortotal':valortotal
        };

        $.ajax({
            url: "insert",
            type: 'POST',
            data: parametros,
        }).done(function (respuesta) {
            
            console.log(respuesta);
            window.location.href = "/nota/credito";
        });
     }
</script>
@endsection
@endsection

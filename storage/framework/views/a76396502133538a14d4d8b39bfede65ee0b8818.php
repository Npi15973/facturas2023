 <?php $__env->startSection('content'); ?>


<?php if(session()->has('message')): ?>
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><?php echo session()->get('message'); ?></div>
<?php endif; ?>
<?php if(session()->has('not_permitted')): ?>
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><?php echo e(session()->get('not_permitted')); ?></div>
<?php endif; ?>


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
        <?php echo Form::open(['route' => 'sales.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']); ?>

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
                                                            <td><?php echo e($emisor->razon_social); ?></td>
                                                            <th>Ruc: </th>
                                                            <td><?php echo e($emisor->ruc); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Ambiente: </th>
                                                            <?php if($emisor->ambiente=="1"): ?>
                                                                 <td><span class="badge badge-secondary">Pruebas</span></td>
                                                            <?php else: ?>
                                                                <td><span class="badge badge-primary">Producción</span></td>
                                                            <?php endif; ?>
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
                                        <label><strong><?php echo e(trans('file.Warehouse')); ?> *</strong></label>
                                        <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control form-control-sm" data-live-search="true" data-live-search-style="begins" title="Seleccione un establecimiento...">
                                            <?php $__currentLoopData = $lims_warehouse_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($warehouse->id); ?>"><?php echo e($warehouse->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Cliente*</strong></label>
                                        <select required name="customer_id" id="customer_id" class="selectpicker form-control form-control-sm" data-live-search="true" data-live-search-style="begins" title="Seleccione un cliente...">
                                            <?php $__currentLoopData = $lims_customer_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name . ' (' . $customer->tax_no . ')'); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>        
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Tipo de Identificación *</strong></label>
                                        <select required name="tipo_identificacion" id="tipo_identificacion" class="selectpicker form-control form-control-sm" data-live-search="true" data-live-search-style="begins" title="Seleccione tipo de documento">                              
                                            <?php $__currentLoopData = $tipo_documento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($documento->codigo); ?>"><?php echo e($documento->descripcion); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">                           
                                    <label><strong>Buscar Producto *</strong></label>
                                    <select required name="product_id" id="product_id"  onclick="SeccionarProducto(this.id);" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Seleccione un producto...">                                                   
                                        <?php $__currentLoopData = $lims_product_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>                                                 
                                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->name . ' (' . $product->code . ')'); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <?php echo Form::close(); ?>

                </section>
      




<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.main', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
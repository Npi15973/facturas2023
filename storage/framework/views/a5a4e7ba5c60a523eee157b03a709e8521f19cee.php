
 <?php $__env->startSection('content'); ?>


<?php if(session()->has('message')): ?>
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><?php echo session()->get('message'); ?></div>
<?php endif; ?>
<?php if(session()->has('not_permitted')): ?>
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><?php echo e(session()->get('not_permitted')); ?></div>
<?php endif; ?>


      
<style>
    .loader {
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite; /* Safari */
      animation: spin 2s linear infinite;
    }
    
    /* Safari */
    @-webkit-keyframes spin {
      0% { -webkit-transform: rotate(0deg); }
      100% { -webkit-transform: rotate(360deg); }
    }
    
    @keyframes  spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    </style>
    <!-- The Modal -->
    <div class="modal" id="modalLoading">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
        
          <!-- Modal Header -->
          <div class="modal-header text-center">
            <h4 class="modal-title">MENSAJE LAZARO - ERP</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          
          <!-- Modal body -->
          <div class="modal-body">
              <center>
                  <div class="loader"></div>
                  <h5>Espere mientras se autoriza el documento.....</h5>
            </center>
              
          </div>
          
          <!-- Modal footer -->
          <div class="modal-footer">
            
          </div>
          
        </div>
      </div>
    </div>
    <div class="modal" id="modalVerFactura">
        <div class="modal-dialog">
          <div class="modal-content">
      
            <!-- Modal Header -->
            <div class="modal-header">
              <h4 class="modal-title">NOTA DE CRÉDITO</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
      
            <!-- Modal body -->
            <div class="modal-body">
                <embed id="emPdf" src="" type="application/pdf" width="100%" height="600px" />
            </div>
      
            <!-- Modal footer -->
            <div class="modal-footer">
              <button type="button" class="btn btn-info" data-dismiss="modal">CERRAR</button>
            </div>
      
          </div>
        </div>
      </div>
<section>
    <div class="container-fluid">
        <a href="<?php echo e(route('nota.credito.create')); ?>"  type="submit" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Añadir Nota de Crédito</a>&nbsp;
    </div>
    <div class="container-fluid">
        <div class="table-responsive">
            <table class="table table-sm table-striped" id="table_nota_credito">
                <thead>
                    <th class="not-exported" >Acción</th>
                    <th>Número comprobante</th>
                    <th>Cliente</th>
                    <th>Clave Acceso</th>
                    <th>Fecha Emisión</th>
                    <th>Fecha Autorización</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Ambiente</th>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $lims_nota_credito; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fa fa-list"></span>
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                       
                                        <?php if($item->estado_sri!="anulado" &&  $item->estado_sri!="autorizado"): ?>
                                        <li>
                                        <form action="/procesarComprobante" method="POST">
                                            <input type="hidden" name="documentId" value="<?php echo e($item->id); ?>">
                                            <input type="hidden" name="documentType" value="04">
                                            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>" />
                                            <button type="submit"  class="btn btn-link"><i class="fa fa-paper-plane"></i>Enviar SRI</button>
                                        </form> 
                                        <!--<button type="button" onclick="obtenerComprobanteFirmado_sri(<?php echo e($item->id); ?>,'04');" class="edit-btn btn btn-link"><i class="fa fa-paper-plane"></i>
                                        Enviar SRI</button>-->
                                        </li>
                                        <?php endif; ?>
                                        <li class="divider"></li>
                                        <?php if($item->estado_sri=="autorizado"): ?>
                                        <li>
                                            <button type="button" onclick="enviarRideCliente(<?php echo e($item->id); ?>,'04')" class="btn btn-link"><i class="fa fa-paper-plane"></i>Renviar Mail</button>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return GenerarPdf(<?php echo e($item->id); ?>,'04')"><i class="fa fa-eye"></i>Generar Descargar PDF</button>
                                        </li>
                                        <li>
                                            <button type="button" onclick="DescargarXml(<?php echo e($item->id); ?>,'04')" class="btn btn-link"><i class="fa fa-download"></i>Descargar Xml</button>
                                        </li>
                                        <?php if($item->estado_sri=="autorizado"): ?>
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return anular(<?php echo e($item->id); ?>)"><i class="fa fa-times"></i> Anular</button>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return confirmDeleteDoc(<?php echo e($item->id); ?>)"><i class="fa fa-trash"></i> <?php echo e(trans('file.delete')); ?></button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td><?php echo e($item->numero_nota); ?></td>
                            <td><?php echo e($item->name); ?></td>
                            <td><?php echo e($item->clave_acceso); ?></td>
                            <td><?php echo e($item->fecha_emision); ?></td>
                            <td><?php echo e($item->fecha_autorizacion); ?></td>
                            <td><?php echo e($item->grand_total); ?></td>
                            <?php if($item->estado_sri=="creado"): ?>
                                <td><span class="badge badge-warning">Creado</span></td>
                            <?php elseif($item->estado_sri=="anulado"): ?>
                                <td><span class="badge badge-danger">Anulado</span></td>
                            <?php elseif($item->estado_sri=="autorizado"): ?>
                                <td><span class="badge badge-success">Autorizado</span></td>
                            <?php endif; ?>

                            <?php if($item->ambiente=="1"): ?>
                            <td><span class="badge badge-secondary">PRUEBAS</span></td>
                            <?php else: ?>
                            <td><span class="badge badge-primary">PRODUCCIÓN</span></td>
                            <?php endif; ?>
                            
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    
</section>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r79/three.min.js"></script>
<script type="text/javascript" src="<?php echo e(asset('facturacion_/facturacion.js')); ?>"></script>

<script>

    $("ul#facturacion").siblings('a').attr('aria-expanded','true');
    $("ul#facturacion").addClass("show");
    $("ul#facturacion #nota-credito-create-menu").addClass("active");

    function Nuevo(){
        $("#modalNuevoNotaCredito").modal('show');
    }

    $("#table_nota_credito").DataTable(
        {
            'language': {
                'searchPlaceholder': "Ingresa nombre del cliente",
                'lengthMenu': '_MENU_ <?php echo e(trans("file.records per page")); ?>',
                 "info":      '<?php echo e(trans("file.Showing")); ?> _START_ - _END_ (_TOTAL_)',
                "search":  '<?php echo e(trans("file.Search")); ?>',
                'paginate': {
                        'previous': '<?php echo e(trans("file.Previous")); ?>',
                        'next': '<?php echo e(trans("file.Next")); ?>'
                }
            },
            dom: '<"row"lfB>rtip',
            buttons: [
            'csv', 'excel', 'pdf',
            {
                extend: 'print',
                text: '<i class="fa fa-print" aria-hidden="true"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    
                },
                footer:true
            },
            ]
             
        }
    );

    function confirmDeleteDoc(id){
        
           
        swal({
        title: "Mensaje",
        text: "Esta seguro de borrar el documento electrónico.? !No podra volver a  recuperar el registro!",
        icon: "warning",
        button: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
            
        $.ajax({
            url: "/nota/credito/delete/"+id,
            type: 'GET',
        }).done(function (respuesta) {

            if(respuesta){
                swal("Documento eliminado correctamente.")
                .then((value) => {
                    location.reload();
            });
            }else{
                swal("Error.")
            }
            
            
        });

        }
      });
        
    }

    function anular(id){
        
           
        swal({
        title: "Mensaje",
        text: "Esta seguro de anular el documento electrónico.?",
        icon: "warning",
        button: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
            
        $.ajax({
            url: "/nota/credito/anular/"+id,
            type: 'GET',
        }).done(function (respuesta) {

            if(respuesta){
                swal("Documento anulado correctamente.")
                .then((value) => {
                    location.reload();
            });
            }else{
                swal("Error.")
            }
            
            
        });

        }
      });
        
    }
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\PROYECTO-LAZARO\PUBLICO_GENERAL\nueva-version\resources\views/nota_credito/index.blade.php ENDPATH**/ ?>
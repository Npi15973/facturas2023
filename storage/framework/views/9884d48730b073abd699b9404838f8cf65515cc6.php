<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <br>
            <h2>Editar Emisor</h2>
            <form action="<?php echo e(route('emisor.update')); ?>" enctype="multipart/form-data" method ="POST">
                <?php echo e(csrf_field()); ?>

                <div class="form-group">
                  <label for="ruc">Ruc :</label>
                  <input type="text" class="form-control" value="<?php echo e($emisor->ruc); ?>" placeholder="Ingrese el ruc" id="ruc" name="ruc">
                </div>
                <div class="form-group">
                    <label for="razon_social">Razón Social :</label>
                    <input type="text" class="form-control" value="<?php echo e($emisor->razon_social); ?>"  id="razon_social" name="razon_social">
                  </div>
                  <div class="form-group">
                    <label for="nombre_comercial">Nombre Comercial :</label>
                    <input type="text" class="form-control" value="<?php echo e($emisor->nombre_comercial); ?>"  id="nombre_comercial" name="nombre_comercial">
                  </div>
                  <div class="form-group">
                    <label for="direccion_matriz">Dirección Matris:</label>
                    <input type="text" class="form-control" value="<?php echo e($emisor->direccion_matriz); ?>"  id="direccion_matriz" name="direccion_matriz">
                  </div>
                  <div class="form-group">
                    <label for="ambiente">Ambiente:</label>
                    <select name="ambiente" id="ambiente" class="form-control">
                            <?php if($emisor->ambiente=="1"): ?>
                            <option value="1" selected>PRUEBAS</option>
                            <option value="2">PRODUCCIÓN</option>
                            <?php else: ?>
                            <option value="1">PRUEBAS</option>
                            <option value="2" selected>PRODUCCIÓN</option>
                            <?php endif; ?>
                            
                            
                    </select>  
                  </div>
                  <div class="form-group">
                    <label for="tipo_emision">Tipo Emisión:</label>
                    <select name="tipo_emision" id="tipo_emision" class="form-control">
                            <?php if($emisor->tipo_emision=="1"): ?>
                            <option value="1" selected>NORMAL</option>
                            <option value="2">INDISPONIBILIDAD SRI</option>
                            <?php else: ?>
                            <option value="1">NORMAL</option>
                            <option value="2" selected >INDISPONIBILIDAD SRI</option>
                            <?php endif; ?>
                        
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="contribuyente">Contribuyente:</label>
                    <input type="text" class="form-control" value="<?php echo e($emisor->contribuyente); ?>"  id="contribuyente" name="contribuyente">
                  </div>
                  <div class="form-group">
                    <label for="obligado_contabilidad">Obligado Contabilidad:</label>
                    <select name="obligado_contabilidad" id="obligado_contabilidad" class="form-control">
                        <?php if($emisor->obligado_contabilidad=="NO"): ?>
                        <option value="NO" selected >NO</option>
                        <option value="SI">SI</option>
                        <?php else: ?>
                        <option value="NO">NO</option>
                        <option value="SI" selected >SI</option>
                        <?php endif; ?>
                        
                    </select>
                  </div>
                  <div class="form-group">
                    <input type="hidden" name="id" id="id" value="<?php echo e($emisor->id); ?>">
                    <label for="regimen">Regimen:</label>
                    <select name="regimen" id="regimen" class="form-control">
                        <?php if($emisor->regimen=="1"): ?>
                        <option value="1" selected>MICROEMPRESA</option>
                        <option value="2">OTRO</option>
                        <?php else: ?>
                        <option value="1">MICROEMPRESA</option>
                        <option value="2" selected>OTRO</option>
                        <?php endif; ?>
                        
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="resolucion_agente_retencion">Agente Retención:</label>
                    <input type="text" class="form-control" value="<?php echo e($emisor->resolusion_agente_retencion); ?>"  id="resolucion_agente_retencion" name="resolucion_agente_retencion">
                  </div>
                  <div class="form-group">
                    <label for="correo_remitente">Correo Remitente:</label>
                    <input type="email" class="form-control" value="<?php echo e($emisor->correo_remitente); ?>"  id="correo_remitente" name="correo_remitente">
                  </div>
                  <div class="form-group">
                    <label for="logo">Logo:</label>
                    <input type="file" class="form-control"  id="logo" name="logo">
                  </div>
                  <div class="form-group">
                    <label for="firma">Firma (p12):</label>
                    <input type="file" class="form-control"  id="firma" name="firma">
                  </div>
                  <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" class="form-control" value="<?php echo e($emisor->password_firma); ?>"  id="password_firma" name="password_firma">
                  </div>
                <button type="submit" class="btn btn-warning">Guardar Cambios</button>
              </form>
        </div>
    </div>
</div>
<script>
  $("ul#setting").siblings('a').attr('aria-expanded','true');
    $("ul#setting").addClass("show");
    $("ul#setting #emisor-setting-menu").addClass("active");
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.main', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
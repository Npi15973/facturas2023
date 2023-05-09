<table class="table">
<thead>
    <tr>
        <th>Tipo Impuesto</th>
        <th>Códig retención</th>
        <th>%</th>
        <th>Base Imponible</th>
        <th>Total</th>
        <th>Número de documento</th>
        <th>Tipo Documento</th>
        <th>Fecha Documento</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
    <?php $__currentLoopData = $retenciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td>
                <?php echo e($value["codigo_retencion"]); ?>

            </td>
            <td>
                <?php echo e($value["tipo_impuesto"]); ?>

            </td>
            <td>
                <?php echo e($value["porcentaje"]); ?>

            </td>
            <td>
                <?php echo e($value["base_imponible"]); ?>

            </td>
            <td>
                <?php echo e($value["total"]); ?>

            </td>
            <td>
                <?php echo e($value["numero_documento"]); ?>

            </td>
            <td>
                <?php echo e($value["tipo_documento"]); ?>

            </td>
            <td>
                <?php echo e($value["fecha_documento"]); ?>

            </td>
            <td>
                <button class="btn btn-danger btn-sm" type="button" onclick="EliminarLinea(<?php echo e($key); ?>);"><i class="fa fa-trash"></i> </button>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
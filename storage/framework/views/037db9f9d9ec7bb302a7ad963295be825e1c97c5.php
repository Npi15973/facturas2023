<table class="table">
    <thead>
        <tr>
            <th>
                Cantidad
            </th>
            <th>
                Código
            </th>
            <th>
                Descripción
            </th>
            <th>
                Acción
            </th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <?php echo e($value["qty"]); ?>

                </td>
                <td>
                    <?php echo e($value["codigo"]); ?>

                </td>
                <td>
                    <?php echo e($value["name"]); ?>

                </td>
                <td>
                    <button class="btn btn-danger btn-sm" type="button" onclick="EliminarLinea(<?php echo e($key); ?>);"><i class="fa fa-trash"></i> </button
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
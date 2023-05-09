<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Razón Modificación </th>
            <th>Valor Modificación</th>
            <th><i class="fa fa-trash"></i></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $total =0;
        ?>
        <?php $__currentLoopData = $motivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <tr id="<?php echo e($key); ?>">
                <td>
                    <?php echo e($key+1); ?>

                </td>
                <td><input type="text"  class="form-control btn-sm razon" value="<?php echo e($item["razon"]); ?>"></td>
                <td><input type="text" class="form-control btn-sm valor" value="<?php echo e($item["valor"]); ?>"></td>
                <td><button class="btn btn-danger btn-sm" type="button" onclick="EliminarLinea(<?php echo e($key); ?>);"><i class="fa fa-trash"></i> </button> </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                        
    </tbody>
</table>
<table class="table">
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Descuento</th>
            <th>Iva</th>
            <th>Valor Iva</th>
            <th>Total</th>
            <th>ICE</th>
            <th>IRBPNR</th>
            <th><i class="fa fa-trash"></i></th>
        </tr>
    </thead>
    <tbody id="contenedorProductos">
        <?php
            $total =0;
        ?>
        <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            ;
        ?>
            <tr id="<?php echo e($item['id']); ?>">
                <td>
                    <?php echo e($item["qty"]); ?>

                </td>
                <td><?php echo e($item["code"]); ?></td>
                <td><?php echo e($item["name"]); ?></td>
                <td><?php echo e($item["price"]); ?></td>
                <td> <input readonly class="form-control btn-sm descuento" type="text" value="<?php echo e($item["descuento"]); ?>"></td>
                <td><?php echo e($item["iva"]); ?></td>
                <td><input readonly class="form-control btn-sm valorIva" value="<?php echo e($item["valorIva"]); ?>" type="text"/></td>
                <?php if($item["iva"]=="SI"): ?>
                    <td><input readonly class="form-control btn-sm subTotalSinIva12" type="text" value="<?php echo e($item["total"]); ?>"></td>
                <?php else: ?>
                    <td><input readonly class="form-control btn-sm subTotalSinIva0" type="text" value="<?php echo e($item["total"]); ?>"></td>
                <?php endif; ?>
                <td><input readonly class="form-control btn-sm ice" type="text" value="<?php echo e($item["ice"]); ?>"> </td>
                <td><input readonly class="form-control btn-sm irbpnr" type="text" value="<?php echo e($item["irbpnr"]); ?>"></td>
                <td><button class="btn btn-danger btn-sm" onclick="EliminarLinea("<?php echo e($item['id']); ?>");"><i class="fa fa-trash"></i> </button> </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                        
    </tbody>
</table>
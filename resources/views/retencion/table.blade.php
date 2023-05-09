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
    @foreach($retenciones as $key => $value)
        <tr>
            <td>
                {{$value["codigo_retencion"]}}
            </td>
            <td>
                {{$value["tipo_impuesto"]}}
            </td>
            <td>
                {{$value["porcentaje"]}}
            </td>
            <td>
                {{$value["base_imponible"]}}
            </td>
            <td>
                {{$value["total"]}}
            </td>
            <td>
                {{$value["numero_documento"]}}
            </td>
            <td>
                {{$value["tipo_documento"]}}
            </td>
            <td>
                {{$value["fecha_documento"]}}
            </td>
            <td>
                <button class="btn btn-danger btn-sm" type="button" onclick="EliminarLinea({{$key}});"><i class="fa fa-trash"></i> </button>
            </td>
        </tr>
    @endforeach
</tbody>
</table>
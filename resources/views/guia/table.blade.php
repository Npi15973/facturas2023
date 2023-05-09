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
        @foreach($productos as $key => $value)
            <tr>
                <td>
                    {{$value["qty"]}}
                </td>
                <td>
                    {{$value["codigo"]}}
                </td>
                <td>
                    {{$value["name"]}}
                </td>
                <td>
                    <button class="btn btn-danger btn-sm" type="button" onclick="EliminarLinea({{$key}});"><i class="fa fa-trash"></i> </button
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
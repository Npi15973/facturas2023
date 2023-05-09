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
        @php
            $total =0;
        @endphp
        @foreach ($motivos as $key => $item)

            <tr id="{{$key}}">
                <td>
                    {{$key+1}}
                </td>
                <td><input type="text"  class="form-control btn-sm razon" value="{{$item["razon"]}}"></td>
                <td><input type="text" class="form-control btn-sm valor" value="{{$item["valor"]}}"></td>
                <td><button class="btn btn-danger btn-sm" type="button" onclick="EliminarLinea({{$key}});"><i class="fa fa-trash"></i> </button> </td>
            </tr>
        @endforeach                                        
    </tbody>
</table>
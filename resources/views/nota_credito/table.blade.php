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
        @php
            $total =0;
        @endphp
        @foreach ($productos as $item)
        @php
            ;
        @endphp
            <tr id="{{$item['id']}}">
                <td>
                    {{$item["qty"]}}
                </td>
                <td>{{$item["code"]}}</td>
                <td>{{$item["name"]}}</td>
                <td>{{$item["price"]}}</td>
                <td> <input readonly class="form-control btn-sm descuento" type="text" value="{{$item["descuento"]}}"></td>
                <td>{{$item["iva"]}}</td>
                <td><input readonly class="form-control btn-sm valorIva" value="{{$item["valorIva"]}}" type="text"/></td>
                @if($item["iva"]=="SI")
                    <td><input readonly class="form-control btn-sm subTotalSinIva12" type="text" value="{{$item["total"]}}"></td>
                @else
                    <td><input readonly class="form-control btn-sm subTotalSinIva0" type="text" value="{{$item["total"]}}"></td>
                @endif
                <td><input readonly class="form-control btn-sm ice" type="text" value="{{$item["ice"]}}"> </td>
                <td><input readonly class="form-control btn-sm irbpnr" type="text" value="{{$item["irbpnr"]}}"></td>
                <td><button class="btn btn-danger btn-sm" onclick="EliminarLinea("{{$item['id']}}");"><i class="fa fa-trash"></i> </button> </td>
            </tr>
        @endforeach                                        
    </tbody>
</table>
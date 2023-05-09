@extends('layout.main')
@section('content')
<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <button class="btn btn-primary" type="button" onclick="AbrirModalEmisor();">
                    Nuevo Emisor
                </button>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-12  col-md-12 col-xs-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ruc</th>
                                <th>Razón Social</th>
                                <th>Nombre Comercial</th>
                                <th>Dirección Matriz</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach ($emisor as $item)
                            @php
                                $estado=$item->is_active;
                                $css ="";
                                $mensaje="";
                                if($estado=="1"){
                                    $mensaje="ACTIVO";
                                    $css="badge badge-success";
                                }else{
                                    $mensaje="INACTIVO";
                                    $css="badge badge-danger";
                                }
                            @endphp
                                <tr>
                                    <td>
                                        {{$item->ruc}}
                                    </td>
                                    <td>
                                        {{$item->razon_social}}
                                    </td>
                                    <td>
                                        {{$item->nombre_comercial}}
                                    </td>
                                    <td>
                                        {{$item->direccion_matriz}}
                                    </td>
                                    <td>
                                        <span  class='{{$css}}'>{{$mensaje}}</span>
                                    </td>
                                    <td>
                                        <a   href="{{route('emisor.edit',['id' => $item->id])}}" class="btn btn-defualt">Editar</a>
                                        <button type="button" onclick="Eliminar({{$item->id}});" class="btn btn-danger btn-sm">Eliminar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<section>
    <!-- The Modal -->
    <div class="modal" id="NuevoEmison">
    <div class="modal-dialog">
        <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
            <h4 class="modal-title">CONFIGURAR EMISOR</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
            <form action="{{route('emisor.create')}}" enctype="multipart/form-data" method ="POST">
                {{ csrf_field() }}
                <div class="form-group">
                  <label for="ruc">Ruc :</label>
                  <input type="text" class="form-control" placeholder="000000000000" id="ruc" name="ruc">
                </div>
                <div class="form-group">
                    <label for="razon_social">Razón Social :</label>
                    <input type="text" class="form-control"  name="razon_social">
                  </div>
                  <div class="form-group">
                    <label for="nombre_comercial">Nombre Comercial :</label>
                    <input type="text" class="form-control"  id="nombre_comercial" name="nombre_comercial">
                  </div>
                  <div class="form-group">
                    <label for="direccion_matriz">Dirección Matris:</label>
                    <input type="text" class="form-control"  id="direccion_matriz" name="direccion_matriz">
                  </div>
                  <div class="form-group">
                    <label for="ambiente">Ambiente:</label>
                    <select name="ambiente" id="ambiente" class="form-control">
                            <option value="1">PRUEBAS</option>
                            <option value="2">PRODUCCIÓN</option>
                    </select>  
                  </div>
                  <div class="form-group">
                    <label for="tipo_emision">Tipo Emisión:</label>
                    <select name="tipo_emision" id="tipo_emision" class="form-control">
                        <option value="1">NORMAL</option>
                        <option value="2">INDISPONIBILIDAD SRI</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="contribuyente">Contribuyente:</label>
                    <input type="text" class="form-control"  id="contribuyente" name="contribuyente">
                  </div>
                  <div class="form-group">
                    <label for="obligado_contabilidad">Obligado Contabilidad:</label>
                    <select name="obligado_contabilidad" id="obligado_contabilidad" class="form-control">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="regimen">Regimen:</label>
                    <select name="regimen" id="regimen" class="form-control">
                        <option value="1">MICROEMPRESA</option>
                        <option value="2">OTRO</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="resolucion_agente_retencion">Agente Retención:</label>
                    <input type="text" class="form-control" placeholder="Resolución" id="resolucion_agente_retencion" name="resolucion_agente_retencion">
                  </div>
                  <div class="form-group">
                    <label for="correo_remitente">Correo Remitente:</label>
                    <input type="email" class="form-control" placeholder="Resolución" id="correo_remitente" name="correo_remitente">
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
                    <input type="password" class="form-control" id="password_firma" name="password_firma">
                  </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
              </form>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        </div>

        </div>
    </div>
    </div>
</section>
<script>

    $("ul#setting").siblings('a').attr('aria-expanded','true');
    $("ul#setting").addClass("show");
    $("ul#setting #emisor-setting-menu").addClass("active");
    function AbrirModalEmisor(){
        $("#NuevoEmison").modal('show');
    }

    function Eliminar(id){
        swal({
            title: "Mensaje",
            text: "Esta seguro de borrar el emisor.? !No podra volver a  recuperar el registro!",
            icon: "warning",
            button: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
                
            $.ajax({
                url: "emisor/delete/"+id,
                type: 'GET',
            }).done(function (respuesta) {
    
                if(respuesta){
                    swal("Emisor eliminado correctamente.")
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
@endsection
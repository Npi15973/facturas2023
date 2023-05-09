@extends('layout.main')
 @section('content')


@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif


<style>
    .loader {
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite; /* Safari */
      animation: spin 2s linear infinite;
    }
    
    /* Safari */
    @-webkit-keyframes spin {
      0% { -webkit-transform: rotate(0deg); }
      100% { -webkit-transform: rotate(360deg); }
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    </style>
    <!-- The Modal -->
    <div class="modal" id="modalLoading">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
        
          <!-- Modal Header -->
          <div class="modal-header text-center">
            <h4 class="modal-title">MENSAJE LAZARO - ERP</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          
          <!-- Modal body -->
          <div class="modal-body">
              <center>
                  <div class="loader"></div>
                  <h5>Espere mientras se autoriza el documento.....</h5>
            </center>
              
          </div>
          
          <!-- Modal footer -->
          <div class="modal-footer">
            
          </div>
          
        </div>
      </div>
    </div>

    <div class="modal" id="modalVerFactura">
        <div class="modal-dialog">
          <div class="modal-content">
      
            <!-- Modal Header -->
            <div class="modal-header">
              <h4 class="modal-title">COMPROBANTE DE RETENCIÓN</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
      
            <!-- Modal body -->
            <div class="modal-body">
                <embed id="emPdf" src="" type="application/pdf" width="100%" height="600px" />
            </div>
      
            <!-- Modal footer -->
            <div class="modal-footer">
              <button type="button" class="btn btn-info" data-dismiss="modal">CERRAR</button>
            </div>
      
          </div>
        </div>
      </div>
<section>
    <div class="container-fluid">
        <a href="{{route('retencion.create')}}" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Añadir Retención</a>&nbsp;
    </div>
    <div class="container-fluid">
        <div class="table-responsive">
            <table class="table table-sm table-striped" id="table_retencion">
                <thead>
                    <th>Acción</th>
                    <th>Número Documento</th>
                    <th>Clave Acceso</th>
                    <th>Fecha Emisión</th>
                    <th>Fecha Autorización</th>
                    <th>Estado</th>
                    <th>Ambiente</th>
                </thead>
                <tbody>
                    @foreach ($lims_retenciones as $item)
                        <tr>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fa fa-list"></span>
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                        @if($item->estado_sri!="anulado" &&  $item->estado_sri!="autorizado")
                                        <li>
                                        <form action="/procesarComprobante" method="POST">
                                            <input type="hidden" name="documentId" value="{{$item->id}}">
                                            <input type="hidden" name="documentType" value="07">
                                            <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                            <button type="submit"  class="btn btn-link"><i class="fa fa-paper-plane"></i>Enviar SRI</button>
                                        </form> 
                                        <!--<button type="button" onclick="obtenerComprobanteFirmado_sri({{$item->id}},'07');" class="edit-btn btn btn-link"><i class="fa fa-paper-plane"></i> Enviar SRI</button>-->
                                        </li>
                                        @endif
                                        <li class="divider"></li>
                                        @if($item->estado_sri=="autorizado")
                                        <li>
                                          <button type="button" onclick="enviarRideCliente({{$item->id}},'07')" class="btn btn-link"><i class="fa fa-paper-plane"></i>Renviar Mail</button>
                                      </li>
                                      @endif
                                      <li>
                                          <button type="submit" class="btn btn-link" onclick="return GenerarPdf({{$item->id}},'07')"><i class="fa fa-eye"></i>Generar Descargar PDF</button>
                                      </li>
                                      <li>
                                          <button type="button" onclick="DescargarXml({{$item->id}},'07')" class="btn btn-link"><i class="fa fa-download"></i>Descargar Xml</button>
                                      </li>
                                      @if($item->estado_sri=="autorizado")
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return anular({{$item->id}})"><i class="fa fa-times"></i> Anular</button>
                                        </li>
                                        @endif
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return confirmDeleteDoc({{$item->id}})"><i class="fa fa-trash"></i> {{trans('file.delete')}}</button>
                                        </li>
                                        
                                    </ul>
                                </div>
                            </td>
                            <td>{{$item->numero_documento}}</td>
                            <td>{{$item->clave_acceso}}</td>
                            <td>{{$item->fecha_emision}}</td>
                            <td>{{$item->fecha_autorizacion}}</td>
                            @if($item->estado_sri=="creado")
                                <td><span class="badge badge-warning">Creado</span></td>
                            @elseif($item->estado_sri=="anulado")
                                <td><span class="badge badge-danger">Anulado</span></td>
                            @elseif($item->estado_sri=="autorizado")
                                <td><span class="badge badge-success">Autorizado</span></td>
                            @endif

                            @if($item->ambiente=="1")
                            <td><span class="badge badge-secondary">PRUEBAS</span></td>
                            @else
                            <td><span class="badge badge-primary">PRODUCCIÓN</span></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
</section>

@section('scripts')
<script type="text/javascript" src="{{asset('facturacion/fiddle.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r79/three.min.js"></script>
<script type="text/javascript" src="{{asset('facturacion_/facturacion.js')}}"></script>
<script>

    $("ul#facturacion").siblings('a').attr('aria-expanded','true');
    $("ul#facturacion").addClass("show");
    $("ul#facturacion #retencion-create-menu").addClass("active");

    function Nuevo(){
        $("#modalNuevoNotaCredito").modal('show');
    }

    $("#table_retencion").DataTable(
        {
            'language': {
                'searchPlaceholder': "Ingresa nombre del cliente",
                'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
                 "info":      '{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)',
                "search":  '{{trans("file.Search")}}',
                'paginate': {
                        'previous': '{{trans("file.Previous")}}',
                        'next': '{{trans("file.Next")}}'
                }
            },
            dom: '<"row"lfB>rtip',
            buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
            ]
             
        }
    );


    function confirmDeleteDoc(id){
        
           
        swal({
        title: "Mensaje",
        text: "Esta seguro de borrar el documento electrónico.? !No podra volver a  recuperar el registro!",
        icon: "warning",
        button: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
            
        $.ajax({
            url: "/retencion/delete/"+id,
            type: 'GET',
        }).done(function (respuesta) {

            if(respuesta){
                swal("Documento eliminado correctamente.")
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

    function anular(id){
        
           
        swal({
        title: "Mensaje",
        text: "Esta seguro de anular el documento electrónico.?",
        icon: "warning",
        button: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
            
        $.ajax({
            url: "/retencion/anular/"+id,
            type: 'GET',
        }).done(function (respuesta) {

            if(respuesta){
                swal("Documento anulado correctamente.")
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
@endsection

@extends('layout.main') @section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.Update Biller')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => ['biller.update', $lims_biller_data->id], 'method' => 'put', 'files' => true]) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Nombre *</strong> </label>
                                    <input type="text" name="name" required value="{{$lims_biller_data->name}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Código *</strong> </label>
                                    <input type="text" name="codigo" value="{{$lims_biller_data->codigo}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Secuencial factura *</strong> </label>
                                    <input type="text" name="secuencial_factura" value="{{$lims_biller_data->secuencial_factura}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Secuencial guía *</strong> </label>
                                    <input type="text" name="secuencial_guia" value="{{$lims_biller_data->secuencial_guia}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Secuencial nota crédito *</strong> </label>
                                    <input type="text" name="secuencial_nota_credito"  value="{{$lims_biller_data->secuencial_nota_credito}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Secuencial nota débito *</strong> </label>
                                    <input type="text" name="secuencial_nota_debito" value="{{$lims_biller_data->secuencial_nota_debito}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Secuencial liquidación *</strong> </label>
                                    <input type="text" name="secuencial_liquidacion" value="{{$lims_biller_data->secuencial_liquidacion}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Secuencial retención *</strong> </label>
                                    <input type="text" name="secuencial_retencion" value="{{$lims_biller_data->secuencial_retencion}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Asignar establecimiento *</strong> </label>
                                    <select class="form-control" name="warehouse_id" id="warehouse_id">
                                        @foreach ($warehouse as $item)
                                        @if($lims_biller_data->warehouse_id==$item->id)
                                        <option selected value="{{$item->id}}">{{$item->name}}</option>
                                        @else
                                        <option value="{{$item->id}}">{{$item->name}}</opti
                                        @endif
                                            
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mt-3">
                                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $("ul#people").siblings('a').attr('aria-expanded','true');
    $("ul#people").addClass("show");
</script>
@endsection

@extends('layout.main') @section('content')
<section>
	{{ Form::open(['route' => ['report.monthlySaleByWarehouse', $year], 'method' => 'post', 'id' => 'report-form']) }}
	<input type="hidden" name="warehouse_id_hidden" value="{{$warehouse_id}}">
	<h4 class="text-center">{{trans('file.Monthly Sale Report')}} &nbsp;&nbsp;
	<select class="selectpicker" id="warehouse_id" name="warehouse_id">
		<option value="0">{{trans('file.All Warehouse')}}</option>
		@foreach($lims_warehouse_list as $warehouse)
		<option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
		@endforeach
	</select>
	</h4>
	{{ Form::close() }}
	<div class="row">
		<div class="col-lg-12">
			<input  class="btn btn-default float-right" type = "button" value = "Imprimir" onclick ="imprimir();" />
		
		</div>
		
	</div>
	<div class="table-responsive mt-4" id="contenedor-print" >
		<table class="table table-bordered table-striped" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
			<thead>
				<tr>
					<th><a href="{{url('report/monthly_sale/'.($year-1))}}"><i class="fa fa-arrow-left"></i> {{trans('file.Previous')}}</a></th>
			    	<th colspan="10" class="text-center">{{$year}}</th>
			    	<th><a href="{{url('report/monthly_sale/'.($year+1))}}">{{trans('file.Next')}} <i class="fa fa-arrow-right"></i></a></th>
			    </tr>
			</thead>
		    <tbody>
			    <tr>
			      <td><strong>January</strong></td>
			      <td><strong>February</strong></td>
			      <td><strong>March</strong></td>
			      <td><strong>April</strong></td>
			      <td><strong>May</strong></td>
			      <td><strong>June</strong></td>
			      <td><strong>July</strong></td>
			      <td><strong>August</strong></td>
			      <td><strong>September</strong></td>
			      <td><strong>October</strong></td>
			      <td><strong>November</strong></td>
			      <td><strong>December</strong></td>
			    </tr>
			    <tr>
			    	@foreach($total_discount as $key => $discount)
			        <td>
			        	@if($discount > 0)
				      	<strong>{{trans("file.Product Discount")}}</strong><br>
				      	<span>{{$discount}}</span><br><br>
				      	@endif
				      	@if($order_discount[$key] > 0)
				      	<strong>{{trans("file.Order Discount")}}</strong><br>
				      	<span>{{$order_discount[$key]}}</span><br><br>
				      	@endif
				      	@if($total_tax[$key] > 0)
				      	<strong>{{trans("file.Product Tax")}}</strong><br>
				      	<span>{{$total_tax[$key]}}</span><br><br>
				      	@endif
				      	@if($order_tax[$key] > 0)
				      	<strong>{{trans("file.Order Tax")}}</strong><br>
				      	<span>{{$order_tax[$key]}}</span><br><br>
				      	@endif
				      	@if($shipping_cost[$key] > 0)
				      	<strong>{{trans("file.Shipping Cost")}}</strong><br>
				      	<span>{{$shipping_cost[$key]}}</span><br><br>
				      	@endif
				      	@if($total[$key] > 0)
				      	<strong>{{trans("file.grand total")}}</strong><br>
				      	<span>{{$total[$key]}}</span><br>
				      	@endif
			        </td>
			        @endforeach
			    </tr>
		    </tbody>
		</table>
	</div>	
</section>

<script type="text/javascript">

	$("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #monthly-sale-report-menu").addClass("active");

	$('#warehouse_id').val($('input[name="warehouse_id_hidden"]').val());
	$('.selectpicker').selectpicker('refresh');

	$('#warehouse_id').on("change", function(){
		$('#report-form').submit();
	});

	function imprimir() {


		var divToPrint=document.getElementById('contenedor-print');
          var newWin=window.open('','Print-Window');
          newWin.document.open();
          newWin.document.write('<link rel="stylesheet" href="<?php echo asset('/vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
          newWin.document.close();
		  setTimeout(function(){newWin.close();},10);

      /*  var divToPrint=document.getElementById("contenedor-print");
        newWin= window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();*/
    }
</script>
@endsection
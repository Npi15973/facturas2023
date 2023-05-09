@extends('layout.main')
@section('content')
<section>
	{{ Form::open(['route' => ['report.dailyPurchaseByWarehouse', $year, $month], 'method' => 'post', 'id' => 'report-form']) }}
	<input type="hidden" name="warehouse_id_hidden" value="{{$warehouse_id}}">
	<h4 class="text-center">{{trans('file.Daily Purchase Report')}} &nbsp;&nbsp;
	<select class="selectpicker" id="warehouse_id" name="warehouse_id">
		<option value="0">{{trans('file.All Warehouse')}}</option>
		@foreach($lims_warehouse_list as $warehouse)
		<option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
		@endforeach
	</select>
	</h4>

	<div class="row">
		<div class="col-lg-12">
			<input  class="btn btn-default float-right" type = "button" value = "Imprimir" onclick ="imprimir();" />
		
		</div>
		
	</div>
	<div class="table-responsive mt-4" id="contenedor-print">
		<table class="table table-bordered table-striped" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
			<thead>
				<tr>
					<th><a href="{{url('report/daily_purchase/'.$prev_year.'/'.$prev_month)}}"><i class="fa fa-arrow-left"></i> {{trans('file.Previous')}}</a></th>
			    	<th colspan="5" class="text-center">{{date("F", strtotime($year.'-'.$month.'-01')).' ' .$year}}</th>
			    	<th><a href="{{url('report/daily_purchase/'.$next_year.'/'.$next_month)}}">{{trans('file.Next')}} <i class="fa fa-arrow-right"></i></a></th>
			    </tr>
			</thead>
		    <tbody>
			    <tr>
				    <td><strong>Sunday</strong></td>
				    <td><strong>Monday</strong></td>
				    <td><strong>Tuesday</strong></td>
				    <td><strong>Wednesday</strong></td>
				    <td><strong>Thrusday</strong></td>
				    <td><strong>Friday</strong></td>
				    <td><strong>Saturday</strong></td>
			    </tr>
			    <?php 
			    	$i = 1;
			    	$flag = 0;
			    	while ($i <= $number_of_day) {
			    		echo '<tr>';
			    		for($j=1 ; $j<=7 ; $j++){
			    			if($i > $number_of_day)
			    				break;

			    			if($flag){
			    				if($year.'-'.$month.'-'.$i == date('Y').'-'.date('m').'-'.(int)date('d'))
			    					echo '<td><p style="color:red"><strong>'.$i.'</strong></p>';
			    				else
			    					echo '<td><p><strong>'.$i.'</strong></p>';

			    				if($total_discount[$i]){
			    					echo '<strong>'.trans("file.Product Discount").'</strong><br><span>'.$total_discount[$i].'</span><br><br>';
			    				}
			    				if($order_discount[$i]){
			    					echo '<strong>'.trans("file.Order Discount").'</strong><br><span>'.$order_discount[$i].'</span><br><br>';
			    				}
			    				if($total_tax[$i]){
			    					echo '<strong>'.trans("file.Product Tax").'</strong><br><span>'.$total_tax[$i].'</span><br><br>';
			    				}
			    				if($order_tax[$i]){
			    					echo '<strong>'.trans("file.Order Tax").'</strong><br><span>'.$order_tax[$i].'</span><br><br>';
			    				}
			    				if($shipping_cost[$i]){
			    					echo '<strong>'.trans("file.Shipping Cost").'</strong><br><span>'.$shipping_cost[$i].'</span><br><br>';
			    				}
			    				if($grand_total[$i]){
			    					echo '<strong>'.trans("file.grand total").'</strong><br><span>'.$grand_total[$i].'</span><br><br>';
			    				}
			    				echo '</td>';
			    				$i++;
			    			}
			    			elseif($j == $start_day){
			    				if($year.'-'.$month.'-'.$i == date('Y').'-'.date('m').'-'.(int)date('d'))
			    					echo '<td><p style="color:red"><strong>'.$i.'</strong></p>';
			    				else
			    					echo '<td><p><strong>'.$i.'</strong></p>';

			    				if($total_discount[$i]){
			    					echo '<strong>'.trans("file.Product Discount").'</strong><br><span>'.$total_discount[$i].'</span><br><br>';
			    				}
			    				if($order_discount[$i]){
			    					echo '<strong>'.trans("file.Order Discount").'</strong><br><span>'.$order_discount[$i].'</span><br><br>';
			    				}
			    				if($total_tax[$i]){
			    					echo '<strong>'.trans("file.Product Tax").'</strong><br><span>'.$total_tax[$i].'</span><br><br>';
			    				}
			    				if($order_tax[$i]){
			    					echo '<strong>'.trans("file.Order Tax").'</strong><br><span>'.$order_tax[$i].'</span><br><br>';
			    				}
			    				if($shipping_cost[$i]){
			    					echo '<strong>'.trans("file.Shipping Cost").'</strong><br><span>'.$shipping_cost[$i].'</span><br><br>';
			    				}
			    				if($grand_total[$i]){
			    					echo '<strong>'.trans("file.grand total").'</strong><br><span>'.$grand_total[$i].'</span><br><br>';
			    				}
			    				echo '</td>';
			    				$flag = 1;
			    				$i++;
			    				continue;
			    			}
			    			else {
			    				echo '<td></td>';
			    			}
			    		}
			    	    echo '</tr>';
			    	}
			    ?>
		    </tbody>
		</table>
	</div>	
</section>

<script type="text/javascript">

	$("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #daily-purchase-report-menu").addClass("active");

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
		  
        /*var divToPrint=document.getElementById("contenedor-print");
        newWin= window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();*/
    }
</script>
@endsection
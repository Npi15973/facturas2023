<?php
include 'digito_verificador.php';
class xml{
	public function xmlFactura($fecha,$correo,$secuencial,$codigo,$cantidad,$descripcion,$preciou,$descuento,$preciot,$subtotal,$iva12,$total,$numero_cedula_receptor,$nombres_receptor,$nombre_empresa,$direccion,$tipo_identificacion,$estableciminento_f,$punto_emision_f,$porcentaje_iva_f,$numero_identidad_emisor){
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;
		//PRIMERA PARTE
		$xml_fac = $xml->createElement('factura');
		$cabecera = $xml->createAttribute('id');
		$cabecera->value = 'comprobante';
		$cabecerav = $xml->createAttribute('version');
		$cabecerav->value = '1.0.0';
		$xml_inf = $xml->createElement('infoTributaria');
		$xml_amb = $xml->createElement('ambiente','2');
		$xml_tip = $xml->createElement('tipoEmision','1');
		$xml_raz = $xml->createElement('razonSocial',$nombre_empresa);
		$xml_nom = $xml->createElement('nombreComercial',$nombre_empresa);
		$xml_ruc = $xml->createElement('ruc',$numero_identidad_emisor);
		$fecha_actual = date("d-m-Y");
		$fechasf =  str_replace("-","",date("d-m-Y",strtotime($fecha_actual." -0 hours")));
		$dig = new modulo();
		$clave_acceso= $fechasf.'01'.$numero_identidad_emisor.'2'.$estableciminento_f.$punto_emision_f.$secuencial.'123456781';
		$clave_acceso =  str_replace(" ","",$clave_acceso);
		$xml_cla = $xml->createElement('claveAcceso',$clave_acceso.$dig->getMod11Dv($clave_acceso));
		$xml_doc = $xml->createElement('codDoc','01');
		$xml_est = $xml->createElement('estab', $estableciminento_f);
		$xml_emi = $xml->createElement('ptoEmi', $punto_emision_f);
		$xml_sec = $xml->createElement('secuencial',$secuencial);
		$xml_dir = $xml->createElement('dirMatriz',$direccion);

		//SEGUNDA PARTE
		$xml_def = $xml->createElement('infoFactura');
		$xml_fec = $xml->createElement('fechaEmision',$fecha);
		$xml_des = $xml->createElement('dirEstablecimiento',$direccion);
		//$xml_con = $xml->createElement('contribuyenteEspecial','NO');
		$xml_obl = $xml->createElement('obligadoContabilidad','SI');
		$xml_ide = $xml->createElement('tipoIdentificacionComprador',$tipo_identificacion);
		$xml_rco = $xml->createElement('razonSocialComprador',$nombres_receptor);
		$xml_idc = $xml->createElement('identificacionComprador',$numero_cedula_receptor);
		$xml_tsi = $xml->createElement('totalSinImpuestos',$preciot);
		$xml_tds = $xml->createElement('totalDescuento','0.00');

		//SEGUNDA PARTE 2.2
		$xml_imp = $xml->createElement('totalConImpuestos');
		$xml_tim = $xml->createElement('totalImpuesto');
		$xml_tco = $xml->createElement('codigo','2');
		$xml_cpr = $xml->createElement('codigoPorcentaje','0');
		$xml_bas = $xml->createElement('baseImponible',$iva12);
		$xml_val = $xml->createElement('valor','0');

		//PARTE 2.3
		$xml_pro = $xml->createElement('propina','0.00');
		$xml_imt = $xml->createElement('importeTotal',$preciot);
		$xml_mon = $xml->createElement('moneda','DOLAR');

		//PARTE PAGOS
		$xml_pgs = $xml->createElement('pagos');
		$xml_pag = $xml->createElement('pago');
		$xml_fpa = $xml->createElement('formaPago','01');
		$xml_tot = $xml->createElement('total',$preciot);
		$xml_pla = $xml->createElement('plazo','1');
		$xml_uti = $xml->createElement('unidadTiempo','dias');



		$xml_dts = $xml->createElement('detalles');
		$xml_det = $xml->createElement('detalle');
		$xml_cop = $xml->createElement('codigoPrincipal',$codigo);
		$xml_dcr = $xml->createElement('descripcion','hola');
		$xml_can = $xml->createElement('cantidad',$cantidad.'.00');
		$xml_pru = $xml->createElement('precioUnitario',$preciou);
		$xml_dsc = $xml->createElement('descuento',$descuento);
		$xml_tsm = $xml->createElement('precioTotalSinImpuesto',$preciot);



		$xml_ips = $xml->createElement('impuestos');
		$xml_ipt = $xml->createElement('impuesto');
		$xml_cdg = $xml->createElement('codigo','2');
		$xml_cpt = $xml->createElement('codigoPorcentaje','0');
		$xml_trf = $xml->createElement('tarifa','0.00');
		$xml_bsi = $xml->createElement('baseImponible','1.00');
		$xml_vlr = $xml->createElement('valor','0.00');



		//INFO ADICIONAL
		$xml_ifa = $xml->createElement('infoAdicional');
		$xml_cp1 = $xml->createElement('campoAdicional',$correo);
		$atributo = $xml->createAttribute('nombre');
		$atributo->value = 'email';

		 //PRIMERA PARTE
		$xml_inf->appendChild($xml_amb);
		$xml_inf->appendChild($xml_tip);
		$xml_inf->appendChild($xml_raz);
		$xml_inf->appendChild($xml_nom);
		$xml_inf->appendChild($xml_ruc);
		$xml_inf->appendChild($xml_cla);
		$xml_inf->appendChild($xml_doc);
		$xml_inf->appendChild($xml_est);
		$xml_inf->appendChild($xml_emi);
		$xml_inf->appendChild($xml_sec);
		$xml_inf->appendChild($xml_dir);
		$xml_fac->appendChild($xml_inf);

		//SEGUNDA PARTE
		$xml_def->appendChild($xml_fec);
		$xml_def->appendChild($xml_des);
		//$xml_def->appendChild($xml_con);
		$xml_def->appendChild($xml_obl);
		$xml_def->appendChild($xml_ide);
		$xml_def->appendChild($xml_rco);
		$xml_def->appendChild($xml_idc);
		$xml_def->appendChild($xml_tsi);
		$xml_def->appendChild($xml_tds);
		$xml_def->appendChild($xml_imp);
		$xml_imp->appendChild($xml_tim);
		$xml_tim->appendChild($xml_tco);
		$xml_tim->appendChild($xml_cpr);
		$xml_tim->appendChild($xml_bas);
		$xml_tim->appendChild($xml_val);
		$xml_fac->appendChild($xml_def);



		//SEGUNDA PARTE 2.3

		$xml_def->appendChild($xml_pro);
		$xml_def->appendChild($xml_imt);
		$xml_def->appendChild($xml_mon);



		$xml_def->appendChild($xml_pgs);
		$xml_pgs->appendChild($xml_pag);
		$xml_pag->appendChild($xml_fpa);
		$xml_pag->appendChild($xml_tot);
		$xml_pag->appendChild($xml_pla);
		$xml_pag->appendChild($xml_uti);



		$xml_fac->appendChild($xml_dts);
		$xml_dts->appendChild($xml_det);
		$xml_det->appendChild($xml_cop);
		$xml_det->appendChild($xml_dcr);
		$xml_det->appendChild($xml_can);
		$xml_det->appendChild($xml_pru);
		$xml_det->appendChild($xml_dsc);
		$xml_det->appendChild($xml_tsm);
		$xml_det->appendChild($xml_ips);
		$xml_ips->appendChild($xml_ipt);
		$xml_ipt->appendChild($xml_cdg);
		$xml_ipt->appendChild($xml_cpt);
		$xml_ipt->appendChild($xml_trf);
		$xml_ipt->appendChild($xml_bsi);
		$xml_ipt->appendChild($xml_vlr);


		$xml_fac->appendChild($xml_ifa);
		$xml_ifa->appendChild($xml_cp1);
		$xml_cp1->appendChild($atributo);





		$xml_fac->appendChild($cabecera);
		$xml_fac->appendChild($cabecerav);
		$xml->appendChild($xml_fac);

		//$xml->save("file.xml");
		//print_r ($xml->saveXML());
		echo $xml->save('../comprobantes/no_firmados/prueba.xml');
		//"./no_firmado/".$xml_cla.".xml"
	}
}



?>

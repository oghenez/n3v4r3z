<?php 
class nomina_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * Obtiene el listado Paginado de
	 */
	public function getNominaPilotos($per_page='9999'){
		$sql = '';
		$sql_date_v = '';
		$sql_date_a = '';
		//paginacion
		$params = array(
				'result_items_per_page' => $per_page,
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
		
		
		$sql_date_v = "AND DATE(v.fecha)<='".date("Y-m-d")."'";
		$sql_date_a = "AND DATE(pa.fecha)<='".date("Y-m-d")."'";
		//Filtros para buscar		
		if($this->input->get('ffecha1')!='' && $this->input->get('ffecha2')!=''){
			if(strtotime($this->input->get('ffecha1'))<=strtotime($this->input->get('ffecha2'))){
				$sql_date_v = "AND DATE(v.fecha)<='{$this->input->get('ffecha2')}'";
				$sql_date_a = "AND DATE(pa.fecha)<='{$this->input->get('ffecha2')}'";
			}
			else{
				$sql_date_v = "AND DATE(v.fecha)<='{$this->input->get('ffecha1')}'";
				$sql_date_a = "AND DATE(pa.fecha)<='{$this->input->get('ffecha1')}'";
			}
		}
		
		$query = BDUtil::pagination("
				SELECT p.id_proveedor, p.nombre, 
						SUM(v.costo_piloto) as total_costo_piloto, 
						SUM(v.iva_piloto) as total_iva_piloto,
						SUM(v.costo_piloto)+SUM(v.iva_piloto) as total_vuelos, 
						COALESCE(ta.total_abonos,0) as total_abonos,
						(SUM(v.costo_piloto)+SUM(v.iva_piloto))-COALESCE(ta.total_abonos,0) as total_saldo
				FROM proveedores as p
				INNER JOIN vuelos as v ON p.id_proveedor=v.id_piloto 
				LEFT JOIN (
						SELECT COALESCE(SUM(pa.total),0) as total_abonos, pa.id_proveedor 
						FROM proveedores_abonos as pa WHERE pa.tipo='ab' $sql_date_a 
						GROUP BY pa.id_proveedor
					) as ta ON ta.id_proveedor=p.id_proveedor
				WHERE p.tipo='pi' $sql_date_v
				".$sql."
				GROUP BY p.id_proveedor, p.nombre, total_abonos
				ORDER BY p.nombre ASC
				", $params, true);
		$res = $this->db->query($query['query']);
		
		$response = array(
				'pilotos' => array(),
				'total_rows' 		=> $query['total_rows'],
				'items_per_page' 	=> $params['result_items_per_page'],
				'result_page' 		=> $params['result_page']
		);
		if($res->num_rows() > 0)
			$response['pilotos'] = $res->result();
		
		return $response;
	}
	
	public function getDetallePiloto() {
		$query = $this->db->select("*")->
							from("proveedores")->
							where("id_proveedor",$this->input->get("id"))->
							get();
		$response['piloto'] = $query->row();
		
		$query->free_result();
		if($this->input->get('ffecha1')!='' && $this->input->get('ffecha2')!=''){
			if(strtotime($this->input->get('ffecha1'))<=strtotime($this->input->get('ffecha2'))){
				$fecha1 = $this->input->get("ffecha1");
				$fecha2 = $this->input->get("ffecha2");
			}
			else{
				$fecha1 = $this->input->get("ffecha2");
				$fecha2 = $this->input->get("ffecha1");
			}
		}

		$query = $this->db->query("
				SELECT p.id_proveedor,
					SUM(v.costo_piloto) as total_costo_piloto, 
					SUM(v.iva_piloto) as total_iva_piloto,
					SUM(v.costo_piloto)+SUM(v.iva_piloto) as total_vuelos, 
					COALESCE(ta.total_abonos,0) as total_abonos,
					(SUM(v.costo_piloto)+SUM(v.iva_piloto))-COALESCE(ta.total_abonos,0) as total_saldo
				FROM proveedores as p
				INNER JOIN vuelos as v ON p.id_proveedor=v.id_piloto AND DATE(v.fecha)<'$fecha1'
				LEFT JOIN (
							SELECT COALESCE(SUM(pa.total),0) as total_abonos, pa.id_proveedor 
							FROM proveedores_abonos as pa 
							WHERE pa.tipo='ab' AND DATE(pa.fecha)<'$fecha1' GROUP BY pa.id_proveedor
					) as ta ON ta.id_proveedor=p.id_proveedor
				WHERE p.tipo='pi' AND p.id_proveedor='{$_GET['id']}'
				GROUP BY p.id_proveedor, ta.total_abonos
				ORDER BY p.id_proveedor ASC");
		
		$response['anterior'] = $query->row();
		
		$query->free_result();
		$query = $this->db->query("
					(SELECT v.fecha as f, DATE(v.fecha) as fecha, a.matricula, p.descripcion, COUNT(*) as cantidad_vuelos, 
							SUM(v.costo_piloto)+SUM(v.iva_piloto) as total_vuelos, 0 as total_abonos, 'vu' as tipo
					FROM vuelos as v
					INNER JOIN aviones as a ON v.id_avion=a.id_avion
					INNER JOIN productos as p ON v.id_producto=p.id_producto
					WHERE v.id_piloto='{$_GET['id']}' AND DATE(fecha)>='$fecha1' AND DATE(fecha)<='$fecha2'
					GROUP BY v.id_piloto, v.fecha, a.matricula, p.descripcion
					ORDER BY fecha ASC)
				UNION
					(SELECT fecha as f, DATE(fecha) as fecha, '' as matricula, concepto as descripcion, 0 as cantidad, 
							0 as total_vuelos, SUM(total) total_abonos, 'ab' as tipo
					FROM proveedores_abonos
					WHERE id_proveedor='{$_GET['id']}' AND tipo='ab' AND DATE(fecha)>='$fecha1' AND DATE(fecha)<='$fecha2'
					GROUP BY fecha, concepto
					ORDER BY fecha ASC)
					ORDER BY fecha ASC");
		
		$response['cuentas'] = $query->result();
		return $response;
	}
	
	
	/**
	 * Crea PDF de la nomina de todos los pilotos 
	 */
	public function nominaPDF(){
		$this->load->library('mypdf');
		// Creación del objeto de la clase heredada
		$pdf = new MYpdf('P', 'mm', 'Letter');
		$pdf->titulo2 = 'Nomina Pilotos';
		$pdf->titulo3 = 'Del: '.$this->input->get('ffecha1')." Al ".$this->input->get('ffecha2')."\n";
		$pdf->AliasNbPages();
		//$pdf->AddPage();
		$pdf->SetFont('Arial','',8);
		
		$aligns = array('L', 'C', 'C', 'R');
		$widths = array(80,35,35,55);
		$header = array('Piloto', 'Vuelos', 'Abonos', 'Saldo');
		
		$res = $this->getNominaPilotos();
		$total_saldo = 0;
		foreach($res['pilotos'] as $key => $item){
			$band_head = false;
			if($pdf->GetY() >= $pdf->limiteY || $key==0){ //salta de pagina si exede el max
				$pdf->AddPage();
		
				$pdf->SetFont('Arial','B',8);
				$pdf->SetTextColor(255,255,255);
				$pdf->SetFillColor(160,160,160);
				$pdf->SetX(6);
				$pdf->SetAligns($aligns);
				$pdf->SetWidths($widths);
				$pdf->Row($header, true);
			}
				
			$pdf->SetFont('Arial','',8);
			$pdf->SetTextColor(0,0,0);
			$datos = array($item->nombre, String::formatoNumero($item->total_vuelos),String::formatoNumero($item->total_abonos),String::formatoNumero($item->total_saldo));
			$total_saldo += $item->total_saldo;
				
			$pdf->SetX(6);
			$pdf->SetAligns($aligns);
			$pdf->SetWidths($widths);
			$pdf->Row($datos, false);
		}
		
		$pdf->SetX(6);
		$pdf->SetFont('Arial','B',8);
		$pdf->SetTextColor(255,255,255);
		$pdf->Row(array('','','Total:', String::formatoNumero($total_saldo)), true);
		
		$pdf->Output('nomina_pilotos.pdf', 'I');
	}
	
	/**
	 * Crea PDF de la nomina un piloto especifico 
	 */
	public function dp_pdf(){
		$res = $this->getDetallePiloto();
		
		$this->load->library('mypdf');
		// Creación del objeto de la clase heredada
		$pdf = new MYpdf('L', 'mm', 'Letter');
		$pdf->titulo2 = 'Nomina del Piloto '.$res['piloto']->nombre;
		$pdf->titulo3 = 'Del: '.$this->input->get('ffecha1')." Al ".$this->input->get('ffecha2')."\n";
// 		$pdf->titulo3 .= ($this->input->get('ftipo') == 'pv'? 'Plazo vencido': 'Pendientes por pagar');
		$pdf->AliasNbPages();
		//$pdf->AddPage();
		$pdf->SetFont('Arial','',8);
		
		$aligns = array('C', 'C', 'C', 'C', 'C', 'C', 'C');
		$widths = array(20, 30, 20, 107, 30, 30, 30);
		$header = array('Fecha', 'Avión', 'Cantidad', 'Descripción', 'Vuelos', 'Abonos', 'Saldo');
		
		$total_cargo = 0;
		$total_abono = 0;
		$total_saldo = 0;
		
		$bad_saldo_ante = true;
		if(isset($res['anterior']->total_saldo)){ //se suma a los totales del saldo anterior
			$total_cargo += $res['anterior']->total_vuelos;
			$total_abono += $res['anterior']->total_abonos;
			$total_saldo += $res['anterior']->total_saldo;
		}else{
			$res['anterior'] = new stdClass();
			$res['anterior']->total_vuelos = 0;
			$res['anterior']->total_abonos = 0;
			$res['anterior']->total_saldo = 0;
		}
		$res['anterior']->concepto = 'Saldo anterior a '.$this->input->get('ffecha1');
		
		foreach($res['cuentas'] as $key => $item){
			$band_head = false;
			if($pdf->GetY() >= $pdf->limiteY || $key==0){ //salta de pagina si exede el max
				$pdf->AddPage();
		
				$pdf->SetFont('Arial','B',8);
				$pdf->SetTextColor(255,255,255);
				$pdf->SetFillColor(160,160,160);
				$pdf->SetX(6);
				$pdf->SetAligns($aligns);
				$pdf->SetWidths($widths);
				$pdf->Row($header, true);
			}
				
			$pdf->SetFont('Arial','',8);
			$pdf->SetTextColor(0,0,0);
			if($bad_saldo_ante){
				$pdf->SetX(6);
				$pdf->SetAligns($aligns);
				$pdf->SetWidths($widths);
				$pdf->Row(array('', '', '', $res['anterior']->concepto,
						String::formatoNumero($res['anterior']->total_vuelos),
						String::formatoNumero($res['anterior']->total_abonos),
						String::formatoNumero($res['anterior']->total_saldo)), false);
				$bad_saldo_ante = false;
			}
			
			if($item->tipo=='vu'){
				$total_cargo += $item->total_vuelos;
				$total_saldo += $item->total_vuelos;
			}
			elseif($item->tipo=='ab'){
				$item->cantidad_vuelos = '';
				$total_abono +=	$item->total_abonos;
				$total_saldo -= $item->total_abonos;
			}
			
			$datos = array($item->fecha, $item->matricula, $item->cantidad_vuelos,
					$item->descripcion, String::formatoNumero($item->total_vuelos), String::formatoNumero($item->total_abonos),
					String::formatoNumero($total_saldo));
		
			$pdf->SetX(6);
			$pdf->SetAligns($aligns);
			$pdf->SetWidths($widths);
			$pdf->Row($datos, false);
		}
		
		$pdf->SetX(6);
		$pdf->SetFont('Arial','B',8);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetWidths(array(177, 30, 30, 30));
		$pdf->Row(array('Totales:',
				String::formatoNumero($total_cargo),
				String::formatoNumero($total_abono),
				String::formatoNumero($total_saldo)), true);
		
		$pdf->Output('cuentas_proveedor.pdf', 'I');
	}
	
	/**
	 *  
	 */
	public function nominaPilotoExcel(){
		$res = $this->getDetallePiloto();
		
		$this->load->library('myexcel');
		$xls = new myexcel();
		
		$worksheet =& $xls->workbook->addWorksheet();
		
		$xls->titulo2 = 'Cuenta de '.$res['piloto']->nombre;
		$xls->titulo3 = 'Del: '.$this->input->get('ffecha1')." Al ".$this->input->get('ffecha2')."\n";
// 		$xls->titulo4 = ($this->input->get('ftipo') == 'pv'? 'Plazo vencido': 'Pendientes por pagar');
		$total_cargo = $total_abono = $total_saldo = $cantidad_vuelos= 0;
		if(isset($res['anterior']->total_saldo)){ //se suma a los totales del saldo anterior
			$total_cargo += $res['anterior']->total_vuelos;
			$total_abono += $res['anterior']->total_abonos;
			$total_saldo += $res['anterior']->total_saldo;
		}else{
			$res['anterior'] = new stdClass();
			$res['anterior']->total_vuelos = 0;
			$res['anterior']->total_abonos = 0;
			$res['anterior']->total_saldo = 0;
		}
		$res['anterior']->fecha = $res['anterior']->matricula = $res['anterior']->cantidad_vuelos = '';
		$res['anterior']->descripcion = '';
		
		foreach ($res['cuentas'] as $item){
			if($item->tipo=='vu'){
				$cantidad_vuelos += $item->cantidad_vuelos;
				$total_cargo += $item->total_vuelos;
				$total_saldo += $item->total_vuelos;
			}
			elseif($item->tipo=='ab'){
				$item->cantidad_vuelos = '';
				$total_abono += $item->total_abonos;
				$total_saldo -= $item->total_abonos;
			}
			$item->total_saldo = $total_saldo;
		}
		$res['totales'] = new stdClass();
		$res['totales']->fecha = $res['totales']->matricula = '';
		$res['totales']->cantidad_vuelos = $cantidad_vuelos;
		$res['totales']->descripcion = '';
		$res['totales']->total_vuelos = $total_cargo;
		$res['totales']->total_abonos = $total_abono;
		$res['totales']->total_saldo = $total_saldo;
		$res['totales']->is_total_final = true;
		
		array_unshift($res['cuentas'], $res['anterior']);
		$res['cuentas'][] = $res['totales'];
		$data_fac = $res['cuentas'];
			
		$row=0;
		//Header
		$xls->excelHead($worksheet, $row, 8, array(
				array($xls->titulo2, 'format_title2'),
				array($xls->titulo3, 'format_title3')
// 				,array($xls->titulo4, 'format_title3')
		));
		
		$row +=3;
		$xls->excelContent($worksheet, $row, $data_fac, array(
				'head' => array('Fecha', 'Avion', 'Cantidad', 'Descripcion', 'Vuelos', 'Abonos', 'Saldo'),
				'conte' => array(
						array('name' => 'fecha', 'format' => 'format4', 'sum' => -1),
						array('name' => 'matricula', 'format' => 'format4', 'sum' => -1),
						array('name' => 'cantidad_vuelos', 'format' => 'format4', 'sum' => -1),
						array('name' => 'descripcion', 'format' => 'format4', 'sum' => -1),
						array('name' => 'total_vuelos', 'format' => 'format4', 'sum' => -1),
						array('name' => 'total_abonos', 'format' => 'format4', 'sum' => -1),
						array('name' => 'total_saldo', 'format' => 'format4', 'sum' => -1)
					)
		));
		
		$xls->workbook->send('nominaPiloto.xls');
		$xls->workbook->close();
	}
	
	
	public function abonar_piloto($id_piloto=null,$abono=null,$concepto=null) {
		$id_piloto	= ($id_piloto==null) ? $this->input->get('id') : $id_factura;
		$abono	= ($abono==null) ? floatval($this->input->get('fabono')) : floatval($abono);
		$concepto	= ($concepto==null) ? $this->input->post('fconcepto') : $concepto;
		$info_abonos = $this->get_info_abonos();
		if($abono<=$info_abonos->restante){
			$id_abono = BDUtil::getId();
			$data = array(
						'id_abono'		=> $id_abono,
						'id_proveedor'	=> $id_piloto,
						'fecha'			=> $this->input->post('ffecha').' '.date('H:i:s'),
						'concepto'		=> $this->input->post('fconcepto'),
						'total'			=> $this->input->post('fabono'),
					);
			$this->db->insert("proveedores_abonos",$data);
			return array(TRUE);
		}
		else return array(FALSE, 'msg'=>'El Abono que ingreso no puede ser mayor al Saldo');
	}
	
	public function get_info_abonos($id_piloto=null){
		$id_piloto = ($id_piloto==null) ? $this->input->get('id') : $id_piloto;
		$res =	$this->db->query("
				SELECT p.id_proveedor, p.nombre, 
						SUM(v.costo_piloto) as total_costo_piloto, 
						SUM(v.iva_piloto) as total_iva_piloto,
						SUM(v.costo_piloto)+SUM(v.iva_piloto) as total, 
						COALESCE(ta.total_abonos,0) as abonado,
						(SUM(v.costo_piloto)+SUM(v.iva_piloto))-COALESCE(ta.total_abonos,0) as restante
				FROM proveedores as p
				INNER JOIN vuelos as v ON p.id_proveedor=v.id_piloto
				LEFT JOIN (
						SELECT COALESCE(SUM(pa.total),0) as total_abonos, pa.id_proveedor 
						FROM proveedores_abonos as pa WHERE pa.tipo='ab' 
						GROUP BY pa.id_proveedor
					) as ta ON ta.id_proveedor=p.id_proveedor
				WHERE p.tipo='pi' AND p.id_proveedor='$id_piloto'
				GROUP BY p.id_proveedor, p.nombre, total_abonos
				ORDER BY p.nombre ASC");
	
		if($res->num_rows==0){
			$res =	$this->db->select('(0) as abonado, SUM(v.costo_piloto)+SUM(v.iva_piloto) as total, SUM(v.costo_piloto)+SUM(v.iva_piloto) as restante')
			->from("vuelos as v")
			->where(array("v.id_piloto"=>$id_piloto))
			->get();
		}
		return $res->row();
	}
	
}?>
<?php

class vuelos_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * Obtiene todos los vuelos
	 */
	public function getVuelos(){
		$sql = '';
		//paginacion
		$params = array(
				'result_items_per_page' => '30',
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
		
		//Filtros para buscar
		if($this->input->get('ffecha_ini') != '')
			$sql = ($this->input->get('ffecha_fin') != '') ? " AND DATE(v.fecha)>='".$this->input->get('ffecha_ini')."'" : " AND DATE(v.fecha)='".$this->input->get('ffecha_ini')."'";

		if($this->input->get('ffecha_fin') != '')
			$sql .= ($this->input->get('ffecha_ini') != '') ? " AND DATE(v.fecha)<='".$this->input->get('ffecha_fin')."'" : " AND DATE(v.fecha)='".$this->input->get('ffecha_fin')."'";
		
		if($sql=='')
			$sql = " AND DATE(v.fecha)=DATE(now())";

		$query = BDUtil::pagination("
				SELECT v.id_vuelo, get_clientes_vuelo(v.id_vuelo,null) as clientes, pi.nombre as piloto, a.matricula, v.fecha, existe_tickets_vuelos(v.id_vuelo) as existe
				FROM vuelos as v
				INNER JOIN proveedores as pi ON v.id_piloto = pi.id_proveedor
				INNER JOIN aviones as a ON v.id_avion = a.id_avion
				WHERE pi.status='ac' AND a.status='ac'				
				$sql
				ORDER BY (DATE(v.fecha),get_clientes_vuelo(v.id_vuelo,null), pi.nombre,a.matricula) DESC
				", $params, true);
		$res = $this->db->query($query['query']);
		
		$response = array(
			'vuelos' 			=> array(),
			'total_rows' 		=> $query['total_rows'],
			'items_per_page' 	=> $params['result_items_per_page'],
			'result_page' 		=> $params['result_page']
		);
		$response['vuelos'] = $res->result();
		return $response;
	}
	
	/**
	 * Obtiene los vuelos de un cliente
	 * 
	 * @param id_cliente
	 */
	public function getVuelosCliente($id_cliente=''){
		$sql = ($id_cliente!='') ? "WHERE id_cliente = '$id_cliente'": "";
		$res = $this->db->query("
					SELECT clientes, piloto, matricula, fecha, id_piloto, id_avion, total_vuelos
					FROM get_vuelos_pendientes $sql
					ORDER BY (fecha,clientes) DESC
			");
		$resultado = array();
		if($res->num_rows()>0)
			$resultado['vuelos'] = $res->result(); 
		
		return $resultado;
	}
	
	/**
	 * Obtiene los vuelos de un cliente
	 *
	 * @param id_cliente
	 */
	public function getVuelosPiloto($id_piloto=''){
		$sql = ($id_piloto!='') ? "WHERE id_piloto = '$id_piloto'": "";
		//paginacion
		$params = array(
				'result_items_per_page' => '15',
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
		
		//Filtros para buscar
		if($this->input->get('ffecha_ini') != '')
			$sql .= ($this->input->get('ffecha_fin') != '') ? " AND DATE(fecha)>='".$this->input->get('ffecha_ini')."'" : " AND DATE(fecha)='".$this->input->get('ffecha_ini')."'";
		
		if($this->input->get('ffecha_fin') != '')
			$sql .= ($this->input->get('ffecha_ini') != '') ? " AND DATE(fecha)<='".$this->input->get('ffecha_fin')."'" : " AND DATE(fecha)='".$this->input->get('ffecha_fin')."'";
		
		if($sql=='')
			$sql = " AND DATE(v.fecha)=DATE(now())";
		
		$query = BDUtil::pagination("
				SELECT id_vuelo, clientes, piloto, matricula, fecha, id_piloto, id_avion, total_vuelos
				FROM get_vuelos_piloto_pendientes $sql
				ORDER BY (fecha,piloto,clientes) DESC
				", $params, true);
				$res = $this->db->query($query['query']);
		
		$response = array(
				'vuelos' 			=> array(),
				'total_rows' 		=> $query['total_rows'],
				'items_per_page' 	=> $params['result_items_per_page'],
				'result_page' 		=> $params['result_page']
		);
		$response['vuelos'] = $res->result();
		return $response;
	}
	
	/**
	 * Agrega la informacion de un vuelo
	 * @param unknown_type $sucu
	 */
	public function addVuelo(){
		
		$id_vuelo = BDUtil::getId();
		$data = array(
			'id_vuelo' 		=> $id_vuelo,
			'id_piloto'		=> $this->input->post('hpiloto'),
			'id_avion'		=> $this->input->post('havion'),
			'fecha'			=> $this->input->post('dfecha').':'.date('s'),
			'id_producto'	=> $this->input->post('dproducto'),
			'costo_piloto'	=> $this->input->post('hcosto_piloto')
		);
		
		$expide_factura = $this->db->select('expide_factura')->from('proveedores')->where('id_proveedor',$this->input->post('hpiloto'))->get()->row()->expide_factura;
		
		if($expide_factura=='t'){
			$data['iva_piloto'] = floatval($data['costo_piloto']) * 0.16;
		}
		
		$this->db->insert('vuelos', $data);
		$data = array();
		foreach ($_POST['hids'] as $cid)
			$data[] = array('id_vuelo' => $id_vuelo, 'id_cliente' => $cid);
		
		$this->db->insert_batch('vuelos_clientes',$data);
		$msg = 4;
		return array(true, '', $msg);
	}	
	
	/**
	 * Elimina a un cliente, cambia su status a "e":eliminado
	 */
	public function delVuelo(){
		$this->db->delete('vuelos', array('id_vuelo' => $_GET['id']));
		return array(true, '');
	}

	public function data_rv()
	{
		$_POST['dfecha1'] = (isset($_POST['dfecha1']))?$_POST['dfecha1']:date('Y-m').'-01';
		$_POST['dfecha2'] = (isset($_POST['dfecha2']))?$_POST['dfecha2']:date('Y-m-d');
		$_POST['did_cliente'] = (isset($_POST['did_cliente']))?$_POST['did_cliente']:'';
		$_POST['did_proveedor'] = (isset($_POST['did_proveedor']))?$_POST['did_proveedor']:'';

		$sql= '';
		$inner_cli= '';
		if( $this->input->post('dfecha1') != '' )
			$sql = " AND DATE(v.fecha)>='".$this->input->post('dfecha1')."'";

		if( $this->input->post('dfecha2') != '' )
			$sql .= " AND DATE(v.fecha)<='".$this->input->post('dfecha2')."'";

		if ( $_POST['did_cliente'] != '' )
			$inner_cli = " INNER JOIN vuelos_clientes as vc ON v.id_vuelo=vc.id_vuelo AND vc.id_cliente='{$_POST['did_cliente']}'";

		if ( $_POST['did_proveedor'] != '' ) 
			$sql .= " AND p.id_proveedor='{$_POST['did_proveedor']}'";

		$query = $this->db->query("SELECT v.fecha, get_clientes_vuelo(v.id_vuelo,null) as clientes, p.nombre as piloto, (a.modelo || ' - ' || a.matricula ) as avion, 
															1 as vuelos, pp.precio, pp.nombre as tipo_vuelo, pp.id_familia
											FROM vuelos v
											INNER JOIN proveedores p ON v.id_piloto=p.id_proveedor
											INNER JOIN aviones a ON v.id_avion=a.id_avion
											INNER JOIN 
												( 
													SELECT plp.id_producto, plp.precio, p.id_familia, p.nombre, p.status 
													FROM productos_listas_precios plp 
													INNER JOIN productos p ON plp.id_producto=p.id_producto 
												) 
												pp ON pp.id_producto=v.id_producto
											$inner_cli
											WHERE p.status='ac' AND pp.status='ac' AND a.status='ac' $sql
											ORDER BY fecha ASC"
									);
		$query_tipos = array();
		if ($query->num_rows() > 0 ) {
			$query_tipos = $this->db->query("SELECT nombre, 0 as tvuelos, 0 as ttotal FROM productos WHERE id_familia='{$query->first_row()->id_familia}'");
			$query_tipos = $query_tipos->result();
		}
		return array('data'=>$query->result(), 'tipos'=>$query_tipos);
	}
	
	/**
	 * Genera el reporte de los vuelos
	 * @param unknown_type $data
	 */
	public function pdf_rv($data){
		if($_POST['dfecha1']!='' && $_POST['dfecha2']!='')
			$labelFechas = "Desde la fecha ".$_POST['dfecha1']." hasta ".$_POST['dfecha2'];
		elseif($_POST['dfecha1']!="")
		$labelFechas = "Desde la fecha ".$_POST['dfecha1'];
		elseif($_POST['dfecha2']!='')
		$labelFechas = "Hasta la fecha ".$_POST['dfecha2'];
	
		$this->load->library('mypdf');
		// Creación del objeto de la clase heredada
		$pdf = new MYpdf('P', 'mm', 'Letter');
		$pdf->show_head = true;
		$pdf->titulo2 = 'Reporte de Vuelos';

		$lbl_cli = (!empty($_POST['did_cliente']))?"Del Cliente {$_POST['dcliente']}":"";
		$lbl_pil = (!empty($_POST['did_proveedor']))?  (($lbl_cli!='')?" y Piloto {$_POST['dproveedor']}":"Del Piloto: {$_POST['dproveedor']}"):"";

		$pdf->titulo3 =  $lbl_cli . $lbl_pil . "\n". $labelFechas;
		$pdf->AliasNbPages();
		$pdf->AddPage();
			
		// $links = array('', '', '', '', '', '', '');
		$aligns = array('C', 'C', 'C', 'C', 'C', 'C', 'C');
		$widths = array(20, 50, 30, 30, 15, 30, 30);
		$header = array('fecha', 'cliente(s)', 'Piloto', 'Avión', 'Vuelos', 'T. Vuelo', 'Precio');
	
		$tvuelos = 0;
		$ttotal = 0;
		foreach($data['data'] as $key => $item){
			$band_head = false;
				if($pdf->GetY() >= $pdf->limiteY || $key==0){ //salta de pagina si exede el max
					if($key > 0)
						$pdf->AddPage();
						
					$pdf->SetFont('Arial','B',9);
					$pdf->SetTextColor(255,255,255);
					$pdf->SetFillColor(140,140,140);
					$pdf->SetX(6);
					$pdf->SetAligns($aligns);
					$pdf->SetWidths($widths);
					$pdf->Row($header, true);
				}
					
				$pdf->SetFont('Arial','',8);
				$pdf->SetTextColor(0,0,0);
				
				$tvuelos += floatval($item->vuelos);
				$ttotal += floatval($item->precio);

				foreach ($data['tipos'] as $key => $tipo) {
					if ($item->tipo_vuelo == $tipo->nombre) {
						$data['tipos'][$key]->tvuelos += 1;
						$data['tipos'][$key]->ttotal += floatval($item->precio);
						break;
					}
				}

				$datos = array(substr($item->fecha, 0, 19), str_replace('<br>', ', ', $item->clientes), $item->piloto, $item->avion, $item->vuelos, $item->tipo_vuelo, String::formatoNumero($item->precio));
					
				$pdf->SetX(6);
				$pdf->SetAligns($aligns);
				$pdf->SetWidths($widths);
				$pdf->Row($datos, false);
		}

		if ( count($data['data']) > 0 ) {
			$y = $pdf->GetY();
			$pdf->SetFont('Arial','B',9);

			if($pdf->GetY()+6 >= $pdf->limiteY){
				$pdf->AddPage();
				$y = $pdf->GetY();
			}
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFillColor(255,255,255);
			$pdf->SetXY(106, $y+5);
			$pdf->Cell(30, 6, 'De los Cuales: ', 0, 0, 'C', 1);

			$y = $y + 5;
			$pdf->SetFont('Arial','',8);
			foreach ($data['tipos'] as $key => $tipo) {
				if($pdf->GetY() >= $pdf->limiteY || $key==0){ //salta de pagina si exede el max
					if($key > 0)
						$pdf->AddPage();
				}
				$y += 6;
				$pdf->SetTextColor(255,255,255);
				$pdf->SetFillColor(140,140,140);
				$pdf->SetXY(106, $y);
				$pdf->Cell(30, 6, $tipo->nombre , 1, 0, 'C',1);
				$pdf->SetXY(151, $y);
				$pdf->Cell(30, 6, 'Sub-Total' , 1, 0, 'C',1);

				$pdf->SetTextColor(0,0,0);
				$pdf->SetFillColor(255,255,255);	
				$pdf->SetXY(136, $y);
				$pdf->Cell(15, 6, $tipo->tvuelos , 1, 0, 'C',1);
				$pdf->SetXY(181, $y);
				$pdf->Cell(30, 6, String::formatoNumero($tipo->ttotal), 1, 0, 'C',1);
			}

			$pdf->SetFont('Arial','B',9);

			$pdf->SetTextColor(255,255,255);
			$pdf->SetFillColor(140,140,140);
			$pdf->SetXY(106, ($y+5));
			$pdf->Cell(30, 6, 'Total Vuelos' , 1, 0, 'C',1);
			$pdf->SetXY(151, ($y+5));
			$pdf->Cell(30, 6, 'Total' , 1, 0, 'C',1);

			$pdf->SetTextColor(0,0,0);
			$pdf->SetFillColor(255,255,255);	
			$pdf->SetXY(136, ($y+5));
			$pdf->Cell(15, 6, $tvuelos , 1, 0, 'C',1);
			$pdf->SetXY(181, ($y+5));
			$pdf->Cell(30, 6, String::formatoNumero($ttotal), 1, 0, 'C',1);


		}			
		$pdf->Output('reporte_vuelos.pdf', 'I');
	}
	
}
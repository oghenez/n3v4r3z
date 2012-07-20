<?php
class facturacion_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	public function getFacturas(){
		$sql = '';
		//paginacion
		$params = array(
				'result_items_per_page' => '30',
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
	
		//Filtros para buscar
	
		switch ($this->input->get('fstatus')){
			case 'todos':
				$sql = "f.status<>''";
				break;
			case 'pendientes':
				$sql = "f.status='p'";
				break;
			case 'pagados':
				$sql = "f.status='pa'";
				break;
		}
	
		if($this->input->get('fstatus') =='')
			$sql = "f.status<>''";
	
		if($this->input->get('ffecha_ini') != '')
			$sql .= ($this->input->get('ffecha_fin') != '') ? " AND DATE(f.fecha)>='".$this->input->get('ffecha_ini')."'" : " AND DATE(f.fecha)='".$this->input->get('ffecha_ini')."'";
	
		if($this->input->get('ffecha_fin') != '')
			$sql .= ($this->input->get('ffecha_ini') != '') ? " AND DATE(f.fecha)<='".$this->input->get('ffecha_fin')."'" : " AND DATE(f.fecha)='".$this->input->get('ffecha_fin')."'";
	
		// 		if($this->input->get('ffecha_ini') == '' && $this->input->get('ffecha_fin') == '')
			// 			$sql .= " AND DATE(tnv.fecha)=DATE(now())";
	
		$query = BDUtil::pagination("
				SELECT f.id_factura, f.serie, f.folio, f.fecha, f.condicion_pago, nombre as cliente, f.status
				FROM facturacion as f
				WHERE $sql
				ORDER BY (f.id_factura, DATE(f.fecha)) DESC
				", $params, true);
				$res = $this->db->query($query['query']);
	
				$response = array(
						'facturas' 			=> array(),
						'total_rows' 		=> $query['total_rows'],
						'items_per_page' 	=> $params['result_items_per_page'],
						'result_page' 		=> $params['result_page']
				);
						$response['facturas'] = $res->result();
						return $response;
	}
	
	public function ajax_get_folio($id_serie_folio=null){
		$id_serie_folio = ($id_serie_folio!=null) ? $id_serie_folio : $_POST['id'];
		$query = $this->db->query("SELECT COALESCE(f.folio,null) as ultimo_folio, fsf.folio_inicio, fsf.folio_fin, fsf.serie, fsf.no_aprobacion, fsf.ano_aprobacion
									FROM facturacion as f
									RIGHT JOIN facturacion_series_folios as fsf ON f.serie=fsf.serie
									WHERE fsf.id_serie_folio = '$id_serie_folio'
									ORDER BY (f.id_factura, f.fecha) DESC LIMIT 1
				");
		$result = $query->result();

		$folio=null;
		if($result[0]->ultimo_folio>=$result[0]->folio_inicio && $result[0]->ultimo_folio<$result[0]->folio_fin){
			$folio = floatval($result[0]->ultimo_folio) + 1;
		}
		elseif($result[0]->ultimo_folio==null || $result[0]->ultimo_folio<$result[0]->folio_inicio || $result[0]->ultimo_folio>$result[0]->folio_fin){
			$folio=$result[0]->folio_inicio;
		}
		
		$params = ($folio!=null) ? array(true,'serie'=>$result[0]->serie,'folio'=>$folio, 'ano_aprobacion'=>$result[0]->ano_aprobacion, 'no_aprobacion'=>$result[0]->no_aprobacion) 
								 : array(false,'msg'=>'Ya no hay Folios disponibles');
		return $params;
	}
	
	public function ajax_get_total_tickets(){
		$response = array();
	
		foreach ($_POST['tickets'] as $t){
				
			$res_q1 = $this->db->query("
					SELECT t.id_ticket, t.folio, t.subtotal as subtotal_ticket, t.iva as iva_ticket, t.total as total_ticket
					FROM tickets as t
					WHERE t.id_ticket='$t'
					GROUP BY t.id_ticket, t.folio, t.subtotal, t.iva, t.total
					");
						
			$res_q2 = $this->db->query("
					SELECT cantidad, unidad, descripcion, precio_unitario, importe, tipo
					FROM tickets_vuelos_productos
					WHERE id_ticket='$t'
					GROUP BY  cantidad, unidad, descripcion, precio_unitario, importe, tipo
					");
						
// 			$res = $this->db->query("
		// 					SELECT t.id_ticket, t.folio, t.fecha, t.subtotal as subtotal_ticket, t.iva as iva_ticket, t.total as total_ticket, 1 as cantidad, t.total as precio_unitario,
		// 					COALESCE(SUM(tvp16.importe_iva),0) as importe_iva_16, COALESCE(SUM(tvp10.importe_iva),0) as importe_iva_10, COALESCE(SUM(tvp0.importe_iva),0) as importe_iva_
		// 					FROM tickets as t
		// 					LEFT JOIN tickets_vuelos_productos as tvp16 ON t.id_ticket=tvp16.id_ticket AND tvp16.taza_iva='0.16'
		// 					LEFT JOIN tickets_vuelos_productos as tvp10 ON t.id_ticket=tvp10.id_ticket AND tvp10.taza_iva='0.1'
		// 					LEFT JOIN tickets_vuelos_productos as tvp0 ON t.id_ticket=tvp0.id_ticket AND tvp0.taza_iva='0'
		// 					WHERE t.id_ticket='$t'
		// 					GROUP BY t.id_ticket, t.folio, t.fecha, t.subtotal, t.iva, t.total
		// 					");

			if($res_q1->num_rows()>0)
				foreach ($res_q1->result() as $itm)
					$response['tickets'][] = $itm;
				
			if($res_q2->num_rows()>0)
				foreach ($res_q2->result() as $itm)
					$response['productos'][$t][] = $itm;
		}
		return $response;
	}
	
	public function ajax_actualiza_digitos(){
		$this->load->library('cfd');
		$this->db->update('facturacion',array('metodo_pago_digitos'=>$this->input->post('digitos')),array('id_factura'=>$this->input->post('id')));		
		$data = $this->getDataFactura($this->input->post('id'),true);
		$cadena = $this->cfd->obtenCadenaOriginal($data);
		$this->db->update('facturacion',array('cadena_original'=>$cadena),array('id_factura'=>$this->input->post('id')));
		$data = $this->getDataFactura($this->input->post('id'),true);
		$this->cfd->actualizarArchivos($data);
		return array(true);
	}
	
	public function addFactura(){
// 		Carga la libreria de Facturacion
		$this->load->library('cfd');
		$id_factura = BDUtil::getId(); // ID FACTURA
		
		$fecha_xml 	= str_replace(' ', 'T', $this->input->post('dfecha'));
		$forma_pago	= ($_POST['dforma_pago']==1) ? $this->input->post('dforma_pago_parcialidad') : 'Pago en una sola exhibición';
		
		$no_cta_pago = '';
		if($_POST['dmetodo_pago']!='efectivo')
			if($_POST['dmetodo_pago_digitos']!='' || $_POST['dmetodo_pago_digitos']=='No identificado')
				$no_cta_pago =  $this->input->post('dmetodo_pago_digitos');
		
		// Parametros para construir la cadena original
		$cad_data = array(
					'serie'			=> $this->input->post('dserie'), 
					'folio'			=> $this->input->post('dfolio'), 
					'fecha_xml'		=> $fecha_xml,
					'no_aprobacion'	=> $this->input->post('dno_aprobacion'),
					'ano_aprobacion'=> $this->input->post('dano_aprobacion'),
					'tipo_comprobante'	=> $this->input->post('dtipo_comprobante'), 
					'forma_pago'		=> $forma_pago, 
					'subtotal'			=> String::float($this->input->post('subtotal')), 
					'total'				=> String::float($this->input->post('total')),
					'metodo_pago'		=> $this->input->post('dmetodo_pago'), 
					'no_cuenta_pago'	=> $no_cta_pago,
					'moneda'			=> 'pesos',
				 
					'crfc'			=> $this->input->post('frfc'), 
					'cnombre'		=> $this->input->post('dcliente'), 
					'ccalle'		=> $this->input->post('fcalle'), 
					'cno_exterior'	=> $this->input->post('fno_exterior'), 
					'cno_interior'	=> $this->input->post('fno_interior'), 
					'ccolonia'		=> $this->input->post('fcolonia'), 
					'clocalidad'	=> $this->input->post('flocalidad'), 
					'cmunicipio'	=> $this->input->post('fmunicipio'), 
					'cestado'		=> $this->input->post('festado'),
					'cpais'			=> $this->input->post('fpais'), 
					'ccp'			=> $this->input->post('fcp')
				);
		if(floatval($_POST['total_isr'])>0)
			$cad_data['total_isr'] = $this->input->post('total_isr');
		
		$productos = array();
		$data_t = array();
		
		$iva_16 = 0;
		$iva_10 = 0;
		$iva_0	= 0;
		$total_iva = 0;
		$tot_prod_iva_0 = 0 ;
		// Ciclo que construye los datos de los tickets a insertar. Tambien obtiene los productos de cada ticket.
		foreach ($_POST as $ticket){
			if(is_array($ticket)){
				$data_t[] = array(
							'id_factura'	=> $id_factura,
							'id_ticket'		=> $ticket['id_ticket']
				);		
				
				$res_q1= $this->db->query("
							SELECT tvp.id_ticket, tvp.id_ticket_producto, tvp.cantidad, tvp.unidad, tvp.descripcion, tvp.precio_unitario, tvp.importe
							FROM tickets_vuelos_productos as tvp
							WHERE tvp.id_ticket='{$ticket['id_ticket']}'
							GROUP BY tvp.id_ticket, tvp.id_ticket_producto, tvp.cantidad, tvp.unidad, tvp.descripcion, tvp.precio_unitario, tvp.importe
						");
				
				$res_q2 = $this->db->query("SELECT 
									(SELECT COALESCE(SUM(importe_iva),0) FROM tickets_vuelos_productos WHERE id_ticket='{$ticket['id_ticket']}' AND taza_iva='0.16') as importe_iva_16,
									(SELECT COALESCE(SUM(importe_iva),0) FROM tickets_vuelos_productos WHERE id_ticket='{$ticket['id_ticket']}' AND taza_iva='0.1') as importe_iva_10,
									(SELECT COALESCE(SUM(importe_iva),0) FROM tickets_vuelos_productos WHERE id_ticket='{$ticket['id_ticket']}' AND taza_iva='0') as importe_iva_0,
									(SELECT COUNT(*) FROM tickets_vuelos_productos WHERE id_ticket='{$ticket['id_ticket']}' AND taza_iva='0') as tot_prof_iva_0
								");
				
				if($res_q1->num_rows>0)
					foreach ($res_q1->result() as $prod)
						$productos[] = array('cantidad'=>$prod->cantidad,'unidad'=>$prod->unidad,'descripcion'=>$prod->descripcion,'precio_unit'=>$prod->precio_unitario,'importe'=>$prod->importe);
				
				if($res_q2->num_rows>0)
					foreach ($res_q2->result() as $iva){
						$iva_16 += floatval($iva->importe_iva_16);
						$iva_10 += floatval($iva->importe_iva_10);
						$iva_0 += floatval($iva->importe_iva_0);
						$tot_prod_iva_0 += intval($iva->tot_prof_iva_0);
					}
			}
		}

		if($iva_16>0)
			$cad_data['ivas'][] = array('tasa_iva'=>'16','importe_iva'=>$iva_16);
		if($iva_10>0)
			$cad_data['ivas'][] = array('tasa_iva'=>'10','importe_iva'=>$iva_10);
		if($tot_prod_iva_0>0)
			$cad_data['ivas'][] = array('tasa_iva'=>'0','importe_iva'=>$iva_0);
		
		$cad_data['iva_total'] = $iva_16 + $iva_10 + $iva_0;
		$cad_data['productos'] = $productos;
		$cadena_original = $this->cfd->obtenCadenaOriginal($cad_data); // OBTIENE CADENA ORIGINAL	
		$sello 	= $this->cfd->obtenSello($cadena_original); // OBTIENE EL SELLO DIGITAL
		
// 		Datos de la factura a insertar
		$data = array(
				'id_factura'	=> $id_factura,
				'id_cliente'	=> $this->input->post('hcliente'),
				'id_empleado'	=> $_SESSION['id_empleado'],
				'serie'			=> $this->input->post('dserie'),
				'folio'			=> $this->input->post('dfolio'),
				'no_aprobacion'	=> $this->input->post('dno_aprobacion'),
				'ano_aprobacion'=> $this->input->post('dano_aprobacion'),
				'fecha'			=> $this->input->post('dfecha'),
				'importe_iva'	=> $this->input->post('iva'),
				'subtotal'		=> $this->input->post('subtotal'),
				'total'			=> $this->input->post('total'),
				'total_letra'	=> $this->input->post('dtotal_letra'),
				'tipo_comprobante'	=> $this->input->post('dtipo_comprobante'),
				'sello'				=> $sello,
				'cadena_original'	=> $cadena_original,
				'no_certificado'	=> $this->input->post('dno_certificado'),
				'version'			=> '2.2',
				'fecha_xml'			=> $fecha_xml,
				'metodo_pago'		=> $this->input->post('dmetodo_pago'),
				'condicion_pago'	=> ($_POST['dcondicion_pago']=='credito') ? 'cr' : 'co',
				'plazo_credito'		=> $this->input->post('fplazo_credito'),
				'nombre'		=> $this->input->post('dcliente'),
				'rfc'			=> $this->input->post('frfc'),
				'calle'			=> $this->input->post('fcalle'),
				'no_exterior'	=> $this->input->post('fno_exterior'),
				'no_interior'	=> $this->input->post('fno_interior'),
				'colonia'		=> $this->input->post('fcolonia'),
				'localidad'		=> $this->input->post('flocalidad'),
				'municipio'		=> $this->input->post('fmunicipio'),
				'estado'		=> $this->input->post('festado'),
				'cp'			=> $this->input->post('fcp'),
				'pais'			=> $this->input->post('fpais'),
				'total_isr'		=> $this->input->post('total_isr'),
		);
		
		if($_POST['dforma_pago']==1)
			$data['forma_pago'] = $this->input->post('dforma_pago_parcialidad');
		
		if($_POST['dmetodo_pago']!='efectivo')
			if($_POST['dmetodo_pago_digitos']!='' || $_POST['dmetodo_pago_digitos']=='No identificado')
				$data['metodo_pago_digitos'] = $this->input->post('dmetodo_pago_digitos');
		
		if($_POST['dcondicion_pago']=='credito')
			$data['status'] = 'p';

		$this->db->insert('facturacion',$data); // INSERTA LA INFORMACION DE FACTURA
		$this->db->insert_batch('facturacion_tickets',$data_t); // INSERTA LOS TICKETS DE LA FACTURA
	
		if($_POST['dcondicion_pago']=='contado'){
			$concepto = "Pago total de la Venta ({$_POST['dfolio']})";
			$res = $this->abonar_factura(true,$id_factura,null,$concepto);
		}
		elseif($_POST['dcondicion_pago']=='credito'){
			$res = $this->abonar_factura(false,$id_factura,null,"");
		}
	
		$data_f = $this->getDataFactura($id_factura,true);
		$this->cfd->generaArchivos($data_f);
		
		return array(true,'id_factura'=>$id_factura);
	}
	
	public function cancelFactura($id_factura=''){
		$this->db->update('facturacion',array('status'=>'ca'),array('id_factura'=>$id_factura));
		return array(true);
	}
	
	
	/**
	 * @param string $id_factura -- ID de la factura
	 * @param boolean $ivas -- TRUE: Agrega los IVAS al resultado FALSE: No agrega los IVAS
	 * @return array  
	 */
	public function getDataFactura($id_factura=null, $ivas=false){
		$id_factura = ($id_factura) ? $id_factura : $_GET['id'];

		$res_q1 = $this->db->select("*")->from('facturacion')->where('id_factura',$id_factura)->get()->result();
			
		$res_q2 = $this->db->query("
					SELECT tvp.id_ticket, tvp.id_ticket_producto, tvp.cantidad, tvp.unidad, tvp.descripcion, tvp.precio_unitario, tvp.importe
					FROM facturacion as f
					INNER JOIN facturacion_tickets as ft ON f.id_factura=ft.id_factura
					INNER JOIN tickets_vuelos_productos as tvp ON ft.id_ticket=tvp.id_ticket
					WHERE f.id_factura='$id_factura'
					GROUP BY tvp.id_ticket, tvp.id_ticket_producto, tvp.cantidad, tvp.unidad, tvp.descripcion, tvp.precio_unitario, tvp.importe
				");
					
		$productos = array();
		foreach($res_q2->result() as $itm)
			$productos[] = array('cantidad'=>$itm->cantidad, 'unidad'=>$itm->unidad, 'descripcion'=>$itm->descripcion, 'precio_unit'=>$itm->precio_unitario, 'importe'=>$itm->importe);
		
		$data = array(
					'serie' => $res_q1[0]->serie,
					'folio'	=> $res_q1[0]->folio,
					'no_aprobacion'		=> $res_q1[0]->no_aprobacion,
					'ano_aprobacion'	=> $res_q1[0]->ano_aprobacion,
					'importe_iva'		=> $res_q1[0]->importe_iva,
					'subtotal'			=> $res_q1[0]->subtotal,
					'total'				=> $res_q1[0]->total,
					'total_letra'		=> $res_q1[0]->total_letra,
					'sello'				=> $res_q1[0]->sello,
					'cadena_original'	=> $res_q1[0]->cadena_original,
					'no_certificado'	=> $res_q1[0]->no_certificado,
					'version'			=> $res_q1[0]->version,
					'fecha_xml'			=> $res_q1[0]->fecha_xml,
				
					'tipo_comprobante'	=> $res_q1[0]->tipo_comprobante,
					'forma_pago'		=> $res_q1[0]->forma_pago,
					'metodo_pago'		=> $res_q1[0]->metodo_pago,
					'descuento'			=> 0,
					'moneda'			=> 'pesos',
					'no_cuenta_pago'	=> $res_q1[0]->metodo_pago_digitos,
				
					'cnombre'			=> $res_q1[0]->nombre,
					'crfc'				=> $res_q1[0]->rfc,
					'ccalle'				=> $res_q1[0]->calle,
					'cno_exterior'	=> $res_q1[0]->no_exterior,
					'cno_interior'	=> $res_q1[0]->no_interior,
					'ccolonia'		=> $res_q1[0]->colonia,
					'clocalidad'	=> $res_q1[0]->localidad,
					'cmunicipio'	=> $res_q1[0]->municipio,
					'cestado'		=> $res_q1[0]->estado,
					'ccp'		=> $res_q1[0]->cp,
					'cpais'		=> $res_q1[0]->pais,
					'productos' => $productos,
				
					'condicion_pago'=> $res_q1[0]->condicion_pago,
					'plazo_credito'	=> $res_q1[0]->plazo_credito,
					'status'		=> $res_q1[0]->status
		);
		
		if(floatval($res_q1[0]->total_isr)>0)
			$data['total_isr'] = $res_q1[0]->total_isr;
		
		if($ivas){
			$ivas = $this->getIvas($id_factura);
			
			$data['ivas'] = $ivas['ivas'];
			$data['iva_total'] = $ivas['iva_total'];
		}
		return $data;
	}
	
	private function getIvas($id_factura){
		$iva_16 = 0;
		$iva_10 = 0;
		$iva_0	= 0;
		$total_iva = 0;
		$tot_prod_iva_0 = 0 ;
		
		$ivas = array();
		$res_q1= $this->db->select("id_ticket")->from("facturacion_tickets")->where("id_factura",$id_factura)->get()->result();
		
		// Ciclo que construye los datos de los tickets a insertar. Tambien obtiene los productos de cada ticket.
		foreach ($res_q1 as $ticket){		
			$res_q2 = $this->db->query("SELECT 
							(SELECT COALESCE(SUM(importe_iva),0) FROM tickets_vuelos_productos WHERE id_ticket='$ticket->id_ticket' AND taza_iva='0.16') as importe_iva_16,
							(SELECT COALESCE(SUM(importe_iva),0) FROM tickets_vuelos_productos WHERE id_ticket='$ticket->id_ticket' AND taza_iva='0.1') as importe_iva_10,
							(SELECT COALESCE(SUM(importe_iva),0) FROM tickets_vuelos_productos WHERE id_ticket='$ticket->id_ticket' AND taza_iva='0') as importe_iva_0,
							(SELECT COUNT(*) FROM tickets_vuelos_productos WHERE id_ticket='$ticket->id_ticket' AND taza_iva='0') as tot_prof_iva_0
					");
		
			if($res_q2->num_rows>0)
				foreach ($res_q2->result() as $iva){
					$iva_16 += floatval($iva->importe_iva_16);
					$iva_10 += floatval($iva->importe_iva_10);
					$iva_0 += floatval($iva->importe_iva_0);
					$tot_prod_iva_0 += intval($iva->tot_prof_iva_0);
				}
		}
		
		if($iva_16>0)
			$ivas['ivas'][] = array('tasa_iva'=>'16','importe_iva'=>$iva_16);
		if($iva_10>0)
			$ivas['ivas'][] = array('tasa_iva'=>'10','importe_iva'=>$iva_10);
		if($tot_prod_iva_0>0)
			$ivas['ivas'][] = array('tasa_iva'=>'0','importe_iva'=>$iva_0);

		$ivas['iva_total'] = $iva_16 + $iva_10 + $iva_0;
		
		return $ivas;
	}
	
	public function abonar_factura($liquidar=false,$id_factura=null,$abono=null,$concepto=null){
	
		$id_factura	= ($id_factura==null) ? $this->input->get('id') : $id_factura;
		$concepto	= ($concepto==null) ? $this->input->post('fconcepto') : $concepto;
	
		$factura_info = $this->get_info_abonos($id_factura);
	
		if($factura_info->status=='p'){
			$pagado = false;
			$total = false;
			if($liquidar){
				if($factura_info->abonado <= $factura_info->total)
					$total = $factura_info->restante;
				elseif($factura_info->restante == $factura_info->total)
				$total = $factura_info->total;
	
				$pagado = true;
			}
			else{
				if(!is_null($abono)){
					$total = $abono;
					if(floatval(($abono+$factura_info->abonado))>=floatval($factura_info->total))
						$pagado=true;
				}
				else{
					$total_abonado_tickets = $this->db->select("SUM(ta.total) as total_abonado_tickets")
														->from("tickets_abonos AS ta")
														->join("facturacion_tickets AS ft","ta.id_ticket=ft.id_ticket","inner")
														->where("ft.id_factura",$id_factura)
														->get()->row()->total_abonado_tickets;
						
					if(floatval($total_abonado_tickets)>0){
						$concepto = 'Pagos y abonos de los tickets agregados a la factura';
						$total = $total_abonado_tickets;
	
						if(floatval($total_abonado_tickets)>=$factura_info->total)
							$pagado=true;
					}
						
				}
			}
				
			if($total!=false){
				$id_abono = BDUtil::getId();
				$data = array(
						'id_abono'	=> $id_abono,
						'id_factura'=> $id_factura,
						'fecha' 	=> $this->input->post('ffecha')!='' ? $this->input->post('ffecha') : date("Y-m-d"),
						'concepto'	=> $concepto,
						'total'		=> floatval($total)
				);
				$this->db->insert('facturacion_abonos',$data);
	
				if($pagado)
					$this->db->update('facturacion',array('status'=>'pa'),array('id_factura'=>$id_factura));
	
				return array(true);
			} return array(false, 'msg'=>'No puede realizar la operación');
		}
		else return array(false,'msg'=>'No puede realizar mas abonos porque la factura ya esta totalmente pagada');
	}
	
	public function get_info_abonos($id_factura=null){
	
		$id_factura = ($id_factura==null) ? $this->input->get('id') : $id_factura;
		$res =	$this->db->select("SUM(fa.total) AS abonado, (f.total-SUM(fa.total)) as restante, f.total, f.status")
		->from("facturacion_abonos as fa")
		->join("facturacion as f", "fa.id_factura=f.id_factura","inner")
		->where(array("tipo"=>"ab","f.status !=" =>"ca","fa.id_factura"=>$id_factura))
		->group_by("f.total, f.status")
		->get();
	
		if($res->num_rows==0){
			$res =	$this->db->select('(0) as abonado, f.total as restante, f.total, f.status')
			->from("facturacion as f")
			->where(array("f.status !=" =>"ca","f.id_factura"=>$id_factura))
			->get();
		}		
		return $res->row();
	}
	
	public function getSeriesFolios(){
		
		//paginacion
		$params = array(
				'result_items_per_page' => '30',
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
		
// 		if($this->input->get('fserie')!='')
// 			$this->db->where('serie',$this->input->get('fserie'));
		
		$this->db->like('lower(serie)',mb_strtolower($this->input->get('fserie'), 'UTF-8'));
		$this->db->order_by('serie');
		$this->db->get('facturacion_series_folios');
		$sql	= $this->db->last_query();
		
		$query = BDUtil::pagination($sql, $params, true);
		$res = $this->db->query($query['query']);
		
		$data = array(
				'series' 			=> array(),
				'total_rows' 		=> $query['total_rows'],
				'items_per_page' 	=> $params['result_items_per_page'],
				'result_page' 		=> $params['result_page']
		);
		
		if($res->num_rows() > 0)
			$data['series'] = $res->result();
		
		return $data;
	}
	
	public function getInfoSerieFolio($id_serie_folio = ''){
		$id_serie_folio = ($id_serie_folio != '') ? $id_serie_folio : $this->input->get('id'); 

		$res = $this->db->select('*')->from('facturacion_series_folios')->where('id_serie_folio',$id_serie_folio)->get()->result();
		return $res;
	}
	
	public function addSerieFolio(){
		$path_img = '';
		//valida la imagen
		$upload_res = UploadFiles::uploadImgSerieFolio();

		if(is_array($upload_res)){
			if($upload_res[0] == false)
				return array(false, $upload_res[1]);
			$path_img = APPPATH.'images/series_folios/'.$upload_res[1]['file_name'];
		}
		
		$id_serie_folio	= BDUtil::getId();
		$data	= array(
				'id_serie_folio' => $id_serie_folio,
				'serie'	=> strtoupper($this->input->post('fserie')),
				'no_aprobacion'	=> $this->input->post('fno_aprobacion'),
				'folio_inicio'	=> $this->input->post('ffolio_inicio'),
				'folio_fin'		=> $this->input->post('ffolio_fin'),
				'ano_aprobacion'=> $this->input->post('fano_aprobacion'),
				'imagen' => $path_img,
		);
		
		if($this->input->post('fleyenda')!='')
			$data['leyenda'] = $this->input->post('fleyenda');
		
		if($this->input->post('fleyenda1')!='')
			$data['leyenda1'] = $this->input->post('fleyenda1');
		
		if($this->input->post('fleyenda2')!='')
			$data['leyenda2'] = $this->input->post('fleyenda2');		
		
		$this->db->insert('facturacion_series_folios',$data);
		return array(true);
	}
	
	public function editSerieFolio($id_serie_folio=''){
		$id_serie_folio = ($id_serie_folio != '') ? $id_serie_folio : $this->input->get('id');

		$data	= array(
				'serie'	=> strtoupper($this->input->post('fserie')),
				'no_aprobacion'	=> $this->input->post('fno_aprobacion'),
				'folio_inicio'	=> $this->input->post('ffolio_inicio'),
				'folio_fin'		=> $this->input->post('ffolio_fin'),
				'ano_aprobacion'=> $this->input->post('fano_aprobacion')
		);
		
		$path_img = '';
		//valida la imagen
		$upload_res = UploadFiles::uploadImgSerieFolio();
		
		if(is_array($upload_res)){
			if($upload_res[0] == false)
				return array(false, $upload_res[1]);
			$path_img = APPPATH.'images/series_folios/'.$upload_res[1]['file_name'];
				
			$old_img = $this->db->select('imagen')->from('facturacion_series_folios')->where('id_serie_folio',$id_serie_folio)->get()->row()->imagen;
			
			if($old_img!='')
				UploadFiles::deleteFile($old_img);
			
			$data['imagen'] = $path_img;
		}
		
		if($this->input->post('fleyenda')!='')
			$data['leyenda'] = $this->input->post('fleyenda');
		
		if($this->input->post('fleyenda1')!='')
			$data['leyenda1'] = $this->input->post('fleyenda1');
		
		if($this->input->post('fleyenda2')!='')
			$data['leyenda2'] = $this->input->post('fleyenda2');		
		
		$this->db->update('facturacion_series_folios',$data, array('id_serie_folio'=>$id_serie_folio));
		
		return array(true);
	}	
	
	public function exist($table, $sql, $return_res=false){
		$res = $this->db->get_where($table, $sql);
		if($res->num_rows() > 0){
			if($return_res)
				return $res->row();
			return TRUE;
		}
		return FALSE;
	}
	
	public function getFacturasReporteMensual() {
		$sql = $this->db->query("SELECT rfc, serie, folio, no_aprobacion, EXTRACT(YEAR from fecha) as anio, fecha, total, importe_iva, status
									FROM facturacion
									WHERE EXTRACT(YEAR from fecha) = '{$this->input->post('fano')}' AND EXTRACT(MONTH from fecha) = '{$this->input->post('fmes')}'
									ORDER BY fecha ASC
								");
		
		$str_data = "";
		if($sql->num_rows() > 0){
			$res = $sql->result();
			foreach( $res as $f){
				$str_data .= "|".$f->rfc."|".$f->serie."|".$f->folio."|".$f->anio.$f->no_aprobacion."|".substr($f->fecha,0,19)."|".$f->total."|".$f->importe_iva."|".(($f->status == "pa")?"0":"1")."|I||||\n";
			}
		}
		
		return $str_data;
	}
	
	public function getPdfReporteMensual() {
		$_POST['fano'] = $_GET['fano'];
		$_POST['fmes'] = $_GET['fmes'];
		$string = $this->getFacturasReporteMensual();
		
		$this->load->library('mypdf');
		// Creación del objeto de la clase heredada
		$pdf = new MYpdf('P', 'mm', 'Letter');
		$pdf->show_head = true;
		$pdf->titulo2 = 'Reporte Mensual';
		$pdf->titulo3 = String::mes($_POST['fmes'])." del {$_POST['fano']}\n";
		//$pdf->titulo3 .=  $nombre_producto;
		$pdf->AliasNbPages();
		$pdf->AddPage();
		
		$pdf->SetXY(5, 30);
		$pdf->SetFont('Arial','',9);
		$pdf->SetAligns(array('L'));
		$pdf->SetWidths(array(205));
		$pdf->Row(array($string), false, false);
			
		
		$pdf->Output('Reporte_Mensual_'.$_POST['fano'].$_POST['fmes'].'.pdf', 'I');
	}
}
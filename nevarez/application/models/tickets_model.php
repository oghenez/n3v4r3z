<?php
class tickets_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	public function getNxtFolio(){
		$folio = $this->db->select('(folio+1) as folio')->from('tickets')->order_by('folio','DESC')->limit(1)->get();
		return array($folio->result());
	}
	
	public function getTickets(){
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
					$sql = "t.status<>'ca'";
					break;
			case 'pendientes':
					$sql = "t.status='p'";
					break;
			case 'pagados':
					$sql = "t.status='pa'";
					break;
		}
		
		if($this->input->get('fstatus') =='')
			$sql = "t.status<>'ca'";
		
		if($this->input->get('ffecha_ini') != '')
			$sql .= ($this->input->get('ffecha_fin') != '') ? " AND DATE(t.fecha)>='".$this->input->get('ffecha_ini')."'" : " AND DATE(t.fecha)='".$this->input->get('ffecha_ini')."'";

		if($this->input->get('ffecha_fin') != '')
			$sql .= ($this->input->get('ffecha_ini') != '') ? " AND DATE(t.fecha)<='".$this->input->get('ffecha_fin')."'" : " AND DATE(t.fecha)='".$this->input->get('ffecha_fin')."'";
		
		if($this->input->get('ffecha_ini') == '' && $this->input->get('ffecha_fin') == '')
			$sql .= " AND DATE(t.fecha)=DATE(now())";
		
		$query = BDUtil::pagination("
				SELECT t.id_ticket, t.folio, t.fecha, t.tipo_pago, c.nombre_fiscal as cliente, t.status
				FROM tickets as t
				INNER JOIN clientes as c ON t.id_cliente=c.id_cliente
				WHERE $sql
				ORDER BY DATE(t.fecha) DESC
				", $params, true);
				$res = $this->db->query($query['query']);
		
				$response = array(
						'tickets' 			=> array(),
						'total_rows' 		=> $query['total_rows'],
						'items_per_page' 	=> $params['result_items_per_page'],
						'result_page' 		=> $params['result_page']
				);
				$response['tickets'] = $res->result();
				return $response;
		
	}
	
	public function getTotalVuelosAjax(){
		$response = array();
		
		foreach ($_POST as $v){
			$v['clientes'] = str_replace('-', '<br>', $v['clientes']);
			$res = $this->db->query("
					SELECT vc.id_vuelo, p.codigo, p.descripcion, plp.dias_credito, CASE WHEN plp.precio<>0 THEN plp.precio ELSE get_precio_default() END as precio, get_clientes_vuelo(v.id_vuelo,null) as clientes
					FROM vuelos as v
					INNER JOIN productos as p ON v.id_producto=p.id_producto
					INNER JOIN vuelos_clientes as vc ON v.id_vuelo=vc.id_vuelo
					LEFT JOIN (SELECT plp.id_producto, plp.precio, c.dias_credito FROM productos_listas_precios as plp INNER JOIN clientes as c ON plp.id_lista=c.id_lista_precio WHERE c.id_cliente='{$v['id_cliente']}') as plp ON plp.id_producto=v.id_producto
					WHERE vc.id_cliente = '{$v['id_cliente']}' AND v.id_piloto = '{$v['id_piloto']}' AND v.id_avion = '{$v['id_avion']}' AND v.fecha = '{$v['fecha']}' AND get_clientes_vuelo(v.id_vuelo,null) = '{$v['clientes']}';
			");
			
			if($res->num_rows()>0)
				foreach ($res->result() as $itm)
					$response['vuelos'][] = $itm;
		}
		
		$response['tabla']['dias_credito']	= $response['vuelos'][0]->dias_credito;
		$response['tabla']['cantidad']		= count($response['vuelos']);
		$response['tabla']['codigo']		= $response['vuelos'][0]->codigo;
		$response['tabla']['descripcion']	= $response['vuelos'][0]->descripcion;
		$response['tabla']['p_uni']			= String::float($response['vuelos'][0]->precio);
		$response['tabla']['importe']		= String::float($response['tabla']['cantidad']*$response['tabla']['p_uni']);
			
		return $response;
	}
	
	public function getInfoTicket($id_ticket=''){
		if($this->exist('tickets', array('id_ticket'=>$id_ticket))){
			$response = array();
			$res_q1 = $this->db->query("
						SELECT t.folio, t.tipo_pago, t.fecha, t.subtotal, t.iva, t.total, c.nombre_fiscal, c.rfc, ('Calle: ' || c.calle || '<br>Colonia: ' || c.colonia || '<br>Localidad: ' || c.localidad || '<br>Municipio: ' || c. municipio ||
								'<br>Estado: ' || c.estado || '<br>C.P: ' || c.cp) as domicilio, get_clientes_vuelo(vc.id_vuelo,t.id_cliente) as otros_clientes
						FROM tickets as t
						INNER JOIN clientes as c ON t.id_cliente=c.id_cliente
						INNER JOIN tickets_vuelos as tv ON t.id_ticket = tv.id_ticket
						INNER JOIN vuelos_clientes as vc ON t.id_cliente=vc.id_cliente AND tv.id_vuelo=vc.id_vuelo
						WHERE t.id_ticket='$id_ticket'
						GROUP BY t.folio, t.tipo_pago, t.fecha, t.subtotal, t.iva, t.total, c.nombre_fiscal, c.rfc, c.calle, c.colonia, c.localidad, c. municipio, c.estado, c.cp, get_clientes_vuelo(vc.id_vuelo,t.id_cliente)
					");
			$response['cliente_info'] = $res_q1->result();
			
			$res_q2 = $this->db->query("
						SELECT v.fecha, pi.nombre, COUNT(*) as vuelos, tv.precio_unitario as precio, SUM(tv.precio_unitario) as importe, p.codigo, p.descripcion, av.matricula
						FROM tickets as t
						INNER JOIN tickets_vuelos as tv ON t.id_ticket=tv.id_ticket
						INNER JOIN vuelos as v ON tv.id_vuelo=v.id_vuelo
						INNER JOIN proveedores as pi ON v.id_piloto=pi.id_proveedor
						INNER JOIN productos as p ON v.id_producto=p.id_producto
						INNER JOIN aviones as av ON av.id_avion=v.id_avion
						WHERE t.id_ticket='$id_ticket'
						GROUP BY v.fecha,v.id_piloto, pi.nombre, tv.precio_unitario, p.codigo, p.descripcion, av.matricula
					");
			$response['vuelos_info'] = $res_q2->result();
			
			return array(true,$response);
		}
		else return array(false); 
	}
	
	public function addTicket(){
		
		$id_ticket = BDUtil::getId();
		$data = array(
					'id_ticket'		=> $id_ticket,
					'id_cliente'	=> $this->input->post('tcliente'),
					'folio'			=> $this->input->post('tfolio'),
					'fecha'			=> $this->input->post('tfecha'),
					'tipo_pago'		=> $this->input->post('tipo_pago'),
					'dias_credito'	=> $this->input->post('tdias_credito'),
					'subtotal'		=> $this->input->post('subtotal'),
					'iva'			=> $this->input->post('iva'),
					'total'			=> $this->input->post('total')
				);
		$this->db->insert('tickets',$data);
		
		foreach ($_POST as $vuelo){
			if(is_array($vuelo)){
				$data_v = array(
						'id_ticket'	=> $id_ticket,
						'id_vuelo'	=> $vuelo['id_vuelo'],
						'cantidad'	=> String::float($vuelo['cantidad']),
						'taza_iva'	=> String::float($vuelo['taza_iva']),
						'precio_unitario'	=> String::float($vuelo['precio_unitario']),
						'importe'			=> String::float($vuelo['importe']),
						'importe_iva'		=> String::float($vuelo['importe_iva']),
						'total'				=> String::float($vuelo['total'])
				);
				$this->db->insert('tickets_vuelos',$data_v);
			}
		}
		
		$folio = $this->getNxtFolio();		
		return array(true,'id_ticket'=>$id_ticket,'folio'=>$folio[0][0]->folio);
	}
	
	public function cancelTicket($id_ticket=''){
		$this->db->update('tickets',array('status'=>'ca'),array('id_ticket'=>$id_ticket));
		return array(true);
	}
	
	public function abonar_ticket($liquidar=false,$id_ticket=null,$concepto=null){
		
		$id_ticket	= ($id_ticket==null) ? $this->input->get('id') : $id_ticket;
		$concepto	= ($concepto==null) ? $this->input->post('fconcepto') : $concepto;
		
		$ticket_info = $this->get_info_abonos($id_ticket);
		
		if($ticket_info->status=='p'){
			$pagado = false;
			if($liquidar){
				if($ticket_info->abonado = $ticket_info->total)
					$total = $ticket_info->restante;
				elseif($ticket_info->restante = $ticket_info->total)
					$total = $ticket_info->total;
				
				$pagado = true;
			}
			else{
				
			}
			
			$id_abono = BDUtil::getId();
			$data = array(
					'id_abono'	=> $id_abono,
					'id_ticket'	=> $id_ticket,
					'fecha' 	=> $this->input->post('ffecha'),
					'concepto'	=> $concepto,
					'total'		=> floatval($total)
			);
			$this->db->insert('tickets_abonos',$data);
			
			if($pagado) 
				$this->db->update('tickets',array('status'=>'pa'),array('id_ticket'=>$id_ticket));
			
			return array(true);
		}
		else return array(false,'msg'=>'No puede realizar mas abonos porque el ticket ya esta totalmente pagado');
	}
	
	public function get_info_abonos($id_ticket=null){
		
		$id_ticket = ($id_ticket==null) ? $this->input->get('id') : $id_ticket;
		$res =	$this->db->select("SUM(ta.total) AS abonado, (t.total-SUM(ta.total)) as restante, t.total, t.status")
							->from("tickets_abonos as ta")
							->join("tickets as t", "ta.id_ticket=t.id_ticket","inner")
							->where(array("tipo"=>"ab","t.status !=" =>"ca","ta.id_ticket"=>$id_ticket))
							->group_by("t.total, t.status")
							->get();
		
 		if($res->num_rows==0){
 			$res =	$this->db->select('(0) as abonado, t.total as restante, t.total, t.status')
					 			->from("tickets as t")
					 			->where(array("t.status !=" =>"ca","t.id_ticket"=>$id_ticket))
					 			->get();
 		}
			
// 		var_dump($res->row());exit;
		return $res->row();
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
	
	
}
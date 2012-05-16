<?php
class notas_venta_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	public function getNxtFolio(){
		$folio = $this->db->select('(folio+1) as folio')->from('tickets_notas_venta')->order_by('folio','DESC')->limit(1)->get();
		return array($folio->result());
	}
	
	public function getNotasVenta(){
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
					$sql = "tnv.status<>'ca'";
					break;
			case 'pendientes':
					$sql = "tnv.status='p'";
					break;
			case 'pagados':
					$sql = "tnv.status='pa'";
					break;
		}
		
		if($this->input->get('fstatus') =='')
			$sql = "tnv.status<>'ca'";
		
		if($this->input->get('ffecha_ini') != '')
			$sql .= ($this->input->get('ffecha_fin') != '') ? " AND DATE(tnv.fecha)>='".$this->input->get('ffecha_ini')."'" : " AND DATE(tnv.fecha)='".$this->input->get('ffecha_ini')."'";

		if($this->input->get('ffecha_fin') != '')
			$sql .= ($this->input->get('ffecha_ini') != '') ? " AND DATE(tnv.fecha)<='".$this->input->get('ffecha_fin')."'" : " AND DATE(tnv.fecha)='".$this->input->get('ffecha_fin')."'";
		
		if($this->input->get('ffecha_ini') == '' && $this->input->get('ffecha_fin') == '')
			$sql .= " AND DATE(tnv.fecha)=DATE(now())";
		
		$query = BDUtil::pagination("
				SELECT tnv.id_nota_venta, tnv.folio, tnv.fecha, tnv.tipo_pago, c.nombre_fiscal as cliente, tnv.status
				FROM tickets_notas_venta as tnv
				INNER JOIN clientes as c ON tnv.id_cliente=c.id_cliente
				WHERE $sql
				ORDER BY DATE(tnv.fecha) DESC
				", $params, true);
				$res = $this->db->query($query['query']);
		
				$response = array(
						'notas' 			=> array(),
						'total_rows' 		=> $query['total_rows'],
						'items_per_page' 	=> $params['result_items_per_page'],
						'result_page' 		=> $params['result_page']
				);
				$response['notas'] = $res->result();
				return $response;
		
	}
	
	public function getTotalTicketsAjax(){
		$response = array();

		foreach ($_POST['tickets'] as $t){
			$res = $this->db->query("
							SELECT t.id_ticket, t.folio, t.fecha, t.subtotal as subtotal_ticket, t.iva as iva_ticket, t.total as total_ticket, count(*) as cantidad, t.total as precio_unitario, 
									0.16 as taza_iva, (t.total*0.16) as importe_iva, (t.total+(t.total*0.16)) as total_nv
							FROM tickets as t
							WHERE t.id_ticket='$t'
							GROUP BY t.id_ticket, t.folio, t.fecha, t.subtotal, t.iva, t.total
					");
			
			if($res->num_rows()>0)
				foreach ($res->result() as $itm)
					$response['tickets'][] = $itm;
		}
			
		return $response;
	}
	
	public function getInfoNotaVenta($id_nota_venta=''){
		if($this->exist('tickets_notas_venta', array('id_nota_venta'=>$id_nota_venta))){
			$response = array();
			$res_q1 = $this->db->query("
						SELECT t.folio, t.tipo_pago, t.fecha, t.subtotal, t.iva, t.total, c.nombre_fiscal, c.rfc, ('Calle: ' || c.calle || '<br>Colonia: ' || c.colonia || '<br>Localidad: ' || c.localidad || '<br>Municipio: ' || c. municipio ||
								'<br>Estado: ' || c.estado || '<br>C.P: ' || c.cp) as domicilio, ('' || c.calle || ', ' || c.colonia || ', ' || c.localidad || ', ' || c. municipio) as domiciliof2
						FROM tickets_notas_venta as t
						INNER JOIN clientes as c ON t.id_cliente=c.id_cliente
						WHERE t.id_nota_venta='$id_nota_venta'
						GROUP BY t.folio, t.tipo_pago, t.fecha, t.subtotal, t.iva, t.total, c.nombre_fiscal, c.rfc, c.calle, c.colonia, c.localidad, c. municipio, c.estado, c.cp
				");
			$response['cliente_info'] = $res_q1->result();
			
			$res_q2 = $this->db->query("
						SELECT t.folio, t.fecha, t.total
						FROM tickets as t 
						INNER JOIN tickets_notas_venta_tickets as nvt ON t.id_ticket=nvt.id_ticket
						WHERE nvt.id_nota_venta='$id_nota_venta'
					");
			$response['tickets_info'] = $res_q2->result();
			
			return array(true,$response);
		}
		else return array(false); 
	}
	
	public function addNotaVenta(){
		
		$id_nota_venta = BDUtil::getId();
		$data = array(
					'id_nota_venta'	=> $id_nota_venta,
					'id_cliente'	=> $this->input->post('tcliente'),
					'folio'			=> $this->input->post('tfolio'),
					'fecha'			=> $this->input->post('tfecha'),
					'tipo_pago'		=> $this->input->post('tipo_pago'),
					'dias_credito'	=> $this->input->post('tdias_credito'),
					'subtotal'		=> $this->input->post('subtotal'),
					'iva'			=> $this->input->post('iva'),
					'total'			=> $this->input->post('total')
				);
		$this->db->insert('tickets_notas_venta',$data);
		
		foreach ($_POST as $ticket){
			if(is_array($ticket)){
				$data_t = array(
						'id_nota_venta'	=> $id_nota_venta,
						'id_ticket'	=> $ticket['id_ticket'],
						'cantidad'	=> String::float($ticket['cantidad']),
						'taza_iva'	=> String::float($ticket['taza_iva']),
						'precio_unitario'	=> String::float($ticket['precio_unitario']),
						'importe'			=> String::float($ticket['importe']),
						'importe_iva'		=> String::float($ticket['importe_iva']),
						'total'				=> String::float($ticket['total'])
				);
				$this->db->insert('tickets_notas_venta_tickets',$data_t);
			}
		}
		
		$folio = $this->getNxtFolio();		
		return array(true,'id_nota_venta'=>$id_nota_venta,'folio'=>$folio[0][0]->folio);
	}
	
	public function cancelNotaVenta($id_nota_venta=''){
		$this->db->update('tickets_notas_venta',array('status'=>'ca'),array('id_nota_venta'=>$id_nota_venta));
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

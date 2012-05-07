<?php
class tickets_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	public function getNxtFolio(){
		$folio = $this->db->select('(folio+1) as folio')->from('tickets')->order_by('folio','DESC')->limit(1)->get();
		return array($folio->result());
	}
	
	public function getTotalVuelosAjax(){
		
		$response = array();
		
		foreach ($_POST as $v){
			$res = $this->db->query("
					SELECT v.id_vuelo, p.codigo, p.descripcion, plp.dias_credito, CASE WHEN plp.precio<>0 THEN plp.precio ELSE get_precio_default() END as precio
					FROM vuelos as v
					INNER JOIN productos as p ON v.id_producto=p.id_producto
					LEFT JOIN (SELECT plp.id_producto, plp.precio, c.dias_credito FROM productos_listas_precios as plp INNER JOIN clientes as c ON plp.id_lista=c.id_lista_precio WHERE c.id_cliente='{$v['id_cliente']}') as plp ON plp.id_producto=v.id_producto
					WHERE v.id_cliente = '{$v['id_cliente']}' AND v.id_piloto = '{$v['id_piloto']}' AND v.id_avion = '{$v['id_avion']}' AND v.fecha = '{$v['fecha']}'
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
		if($this->exist('ticket', array('id_ticket'=>$id_ticket))){
			
			
			
			return array(true);
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
		return array(true,'id_ticket'=>$id_ticket,folio=>$folio[0][0]->folio);
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
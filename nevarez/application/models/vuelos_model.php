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
				SELECT v.id_vuelo, c.nombre_fiscal, pi.nombre as piloto, a.matricula, v.fecha, existe_tickets_vuelos(v.id_vuelo) as existe
				FROM vuelos as v
				INNER JOIN clientes as c ON v.id_cliente = c.id_cliente
				INNER JOIN proveedores as pi ON v.id_piloto = pi.id_proveedor
				INNER JOIN aviones as a ON v.id_avion = a.id_avion
				WHERE c.status='ac' AND pi.status='ac' AND a.status='ac'				
				$sql
				ORDER BY DATE(v.fecha) DESC
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
					SELECT nombre_fiscal, piloto, matricula, fecha, id_cliente, id_piloto, id_avion, total_vuelos
					FROM get_vuelos_pendientes $sql
			");
		$resultado = array();
		if($res->num_rows()>0)
			$resultado['vuelos'] = $res->result(); 
		
		return $resultado;
	}
	
	/**
	 * Agrega la informacion de un vuelo
	 * @param unknown_type $sucu
	 */
	public function addVuelo(){
		
		$id_vuelo = BDUtil::getId();
		$data = array(
			'id_vuelo' 		=> $id_vuelo,
			'id_cliente'	=> $this->input->post('hcliente'),
			'id_piloto'		=> $this->input->post('hpiloto'),
			'id_avion'		=> $this->input->post('havion'),
			'fecha'			=> $this->input->post('dfecha')
		);
		$this->db->insert('vuelos', $data);
		
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
	
	
}
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
	
	
}
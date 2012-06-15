<?php
class salidas_model extends CI_Model{
	
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * Obtiene el listado de compras
	 */
	public function getSalidas(){
		$sql = '';
		//paginacion
		$params = array(
				'result_items_per_page' => '30',
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
		
		//Filtros para buscar
		if($this->input->get('ffecha') != '')
			$sql = " AND Date(s.fecha) = '".$this->input->get('ffecha')."'";
		if($this->input->get('ftipo_salida') != '')
			$sql .= " AND s.tipo_salida = '".mb_strtolower($this->input->get('ftipo_salida'))."'";
		if($this->input->get('ftipo') != ''){
			$sql .= " AND s.status = '".$this->input->get('ftipo')."'";
		}
		
		$query = BDUtil::pagination("
				SELECT s.id_salida, Date(s.fecha) AS fecha, s.folio, s.tipo_salida, s.status
				FROM salidas as s
				WHERE s.status <> 'ca' $sql
				ORDER BY (Date(s.fecha), s.folio) ASC
				", $params, true);
		
		$res = $this->db->query($query['query']);
		
		$response = array(
				'salidas' => array(),
				'total_rows' 		=> $query['total_rows'],
				'items_per_page' 	=> $params['result_items_per_page'],
				'result_page' 		=> $params['result_page']
		);
		if($res->num_rows() > 0)
			$response['salidas'] = $res->result();
		
		return $response;
	}
	
	/**
	 * Obtiene la informacion de una compra
	 */
	public function getInfoSalida($id, $info_basic=false){
		$res = $this->db
			->select('*')
			->from('salidas')
			->where("id_salida = '".$id."'")
		->get();
		if($res->num_rows() > 0){
			$response['info'] = $res->row();
			$response['info']->fecha = substr($response['info']->fecha, 0, 10);
			$res->free_result();
			if($info_basic)
				return $response;
			
			//productos
			$res = $this->db
				->select('p.id_producto, p.codigo, p.nombre, sp.taza_iva, sp.cantidad, sp.precio_unitario, 
						sp.importe, sp.importe_iva, sp.total')
				->from('salidas_productos AS sp')
					->join('productos AS p', 'p.id_producto = sp.id_producto', 'inner')
				->where("sp.id_salida = '".$id."'")
			->get();
			if($res->num_rows() > 0){
				$response['productos'] = $res->result();
			}
			$res->free_result();
			
			if($response['info']->tipo_salida=='av'){
				$res = $this->db->select("id_avion, matricula, modelo")->from("aviones")->where("id_avion",$response['info']->id_avion)->get();
			}
			elseif($response['info']->tipo_salida=='tr'){
				if($response['info']->tipo_trabajador=='pi')
					$res = $this->db->select("id_proveedor as id_trabajador, nombre, calle, no_exterior, colonia, municipio, estado")
										->from("proveedores")->where("id_proveedor",$response['info']->id_trabajador)->get();
				elseif($response['info']->tipo_trabajador=='tr')
					$res = $this->db->select("id_empleado as id_trabajador, (nombre || ' ' apellido_paterno || ' ' || apellido_materno) as nombres, calle, numero as no_exterior, colonia, municipio, estado")
										->from("empleados")->where("id_empleado",$response['info']->id_trabajador)->get();
			}
			elseif($response['info']->tipo_salida=='ve'){
				$res = $this->db->select("id_vehiculo, nombre, placas, modelo")->from("vehiculos")->where("id_vehiculo",$response['info']->id_vehiculo)->get();
			}
			
			if($res->num_rows()>0)
				$response['info_tipo'] = $res->result();
			
			return $response;
		}else
			return false;
	}
	
	/**
	 * Agrega una compra a la bd
	 */
	public function addSalida(){
		$id_salida = BDUtil::getId();
		$data = array(
			'id_salida' => $id_salida,
			'folio' => $this->input->post('dfolio'),
			'fecha' => $this->input->post('dfecha') ,
			'tipo_salida' => $this->input->post('dtipo_salida'),
			'status' => $this->input->post('dtipo'),
			'id_usuario' => $_SESSION['id_empleado']
		);
		
		switch($_POST['dtipo_salida']){
			case 'av':
					$data['id_avion'] = $this->input->post('did_avion');
				break;
			case 'tr':
				$data['id_trabajador'] = $this->input->post('did_trabajador');
				$data['tipo_trabajador'] = $this->input->post('dtipo_trabajador');
				$data['fecha_entrega'] = $this->input->post('dfecha_entrega');
				break;
			case 've':
				$data['id_vehiculo'] = $this->input->post('did_vehiculo');
				break;
		}
		$this->db->insert('salidas', $data);
		
		//productos para las compras
		if(isset($_POST['dpid_producto'])){
			$this->addProductos($id_salida);
		}		
		return array(true, $id_salida);
	}
	
	/**
	 * Cancela una compra, la elimina
	 */
	public function cancelSalida(){
		$this->db->update('salidas', array('status' => 'ca'), "id_salida = '".$_GET['id']."'");
		return array(true, '');
	}
	
	
	/**
	 * Agrega los productos a una compra
	 * @param unknown_type $id_compra
	 * @param unknown_type $tipo
	 */
	public function addProductos($id_salida, $tipo='add'){
		if(is_array($_POST['dpid_producto'])){
			$data_productos = array();
			foreach($_POST['dpid_producto'] as $key => $producto){
				//Datos de los productos de la compra
				$data_productos[] = array(
					'id_salida' => $id_salida,
					'id_producto' => $producto,
					'taza_iva' => $_POST['dptaza_iva'][$key],
					'cantidad' => $_POST['dpcantidad'][$key],
					'precio_unitario' => $_POST['dpprecio_unitario'][$key],
					'importe' => $_POST['dpimporte'][$key],
					'importe_iva' => $_POST['dpimporte_iva'][$key],
					'total' => ($_POST['dpimporte'][$key]+$_POST['dpimporte_iva'][$key]),
				);
			}

			if(count($data_productos) > 0){
// 				if($tipo != 'add' && $this->input->post('dpupdate') == 'si'){
// 					$this->db->delete('salidas_productos', "id_salida = '".$id_compra."'");
// 				}
				//se insertan los productos de la compra	
				$this->db->insert_batch('salidas_productos', $data_productos);
			}
			
			return array(true, '');
		}
		return array(false, '');
	}
}
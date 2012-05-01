<?php
class aviones_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	public function getAviones($id_avion=false, $order = 'matricula ASC'){
		
		//paginacion
		$params = array(
				'result_items_per_page' => '30',
				'result_page' => (isset($_GET['pag'])? $_GET['pag']: 0)
		);
		if($params['result_page'] % $params['result_items_per_page'] == 0)
			$params['result_page'] = ($params['result_page']/$params['result_items_per_page']);
		
		$order = (!empty($order)) ? $order : 'matricula ASC';
		
		($id_avion) ? $this->db->where('id_avion',$id_avion) : null;
		$this->db->where('status','ac');
		$this->db->like('lower(matricula)',mb_strtolower($this->input->get('fmatricula'), 'UTF-8'));
		$this->db->order_by($order);
		$this->db->get('aviones');	
		
		$sql	= $this->db->last_query();
		
		$query = BDUtil::pagination($sql, $params, true);
		$res = $this->db->query($query['query']);
		
		$data = array(
				'aviones' 			=> array(),
				'total_rows' 		=> $query['total_rows'],
				'items_per_page' 	=> $params['result_items_per_page'],
				'result_page' 		=> $params['result_page']
		);
		
		if($res->num_rows() > 0)
			$data['aviones'] = $res->result();
		
		return $data;
	}
	
	public function addAvion(){
		if($this->db->select('id_avion')->from('aviones')->where(array('matricula'=>$this->input->post('fmatricula'),'status'=>'ac'))->get()->num_rows()<1)
		{
			$id_avion	= BDUtil::getId();
			$data	= array(
					'id_avion'	=> $id_avion,
					'matricula'	=> $this->input->post('fmatricula'),
					'modelo'	=> $this->input->post('fmodelo'),
					'tipo'		=> $this->input->post('ftipo')
			);
			$this->db->insert('aviones',$data);
			return array(true);
		}
		return array(false);
	}
	
	public function editAvion($id_avion){
		if($this->exist('aviones',array('id_avion'=>$id_avion,'status'=>'ac')))
		{
			$data	= array(
					'matricula'	=> $this->input->post('fmatricula'),
					'modelo'	=> $this->input->post('fmodelo'),
					'tipo'		=> $this->input->post('ftipo')
			);
			$this->db->where('id_avion',$id_avion);
			$this->db->update('aviones',$data);
			
			return array(true);
		}
		return array(false);
	}
	
	public function delAvion($id_avion){
		if($this->db->select('id_avion')->from('aviones')->where(array('id_avion'=>$id_avion,'status'=>'ac'))->get()->num_rows()==1){
			$this->db->update('aviones',array('status'=>'e'),array('id_avion'=>$id_avion));
			return array(true);
		}
		return array(false);
	}
	
	public function getAvionesAjax(){
		$sql = '';
		$res = $this->db->query("
				SELECT id_avion, matricula, modelo, tipo
				FROM aviones
				WHERE status = 'ac' AND lower(matricula) LIKE '%".mb_strtolower($this->input->get('term'), 'UTF-8')."%'
				ORDER BY matricula ASC");
	
		$response = array();
		if($res->num_rows() > 0){
			foreach($res->result() as $itm){
				$response[] = array(
						'id' => $itm->id_avion,
						'label' => $itm->matricula,
						'value' => $itm->matricula,
						'item' => $itm,
				);
			}
		}
	
		return $response;
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
<?php
class facturacion_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
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
		
		$path_img = '';
		//valida la imagen
		$upload_res = UploadFiles::uploadImgSerieFolio();

		if(is_array($upload_res)){
			if($upload_res[0] == false)
				return array(false, $upload_res[1]);
			$path_img = APPPATH.'images/series_folios/'.$upload_res[1]['file_name'];
			
			$old_img = $this->db->select('imagen')->from('facturacion_series_folios')->where('id_serie_folio',$id_serie_folio)->get()->row()->imagen;
			
			UploadFiles::deleteFile($old_img);
		}

		$data	= array(
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
	
	
}
<?php
class alertas_model extends privilegios_model{
	
	function __construct(){
		parent::__construct();
	}
	
	public function herramientas($wdate=true){
		$date = '';
		if($wdate)
			$date = "AND date(now())>=date(fecha_vencimiento)";
		
		$query = $this->db->select("*")->
							 from("alertas")->
							 where("tabla_obj = 'salidas_productos' $date")->
							 get();
		$html_alert = '';
		if($query->num_rows() > 0){
			$params['data']['alertas'] = $query->result();
			$params['total'] = $query->num_rows();
			$html_alert = $this->load->view("panel/alertas/alerta_herramientas.php",$params,TRUE); 
		}
		return $html_alert; 
	}
	
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class nomina extends MY_Controller {
	
	/**
	 * Evita la validacion (enfocado cuando se usa ajax). Ver mas en privilegios_model
	 * @var unknown_type
	 */
	private $excepcion_privilegio = array('nomina/pilotos_pdf/','nomina/detalle_piloto/','nomina/dp_pdf/','nomina/dp_xls/');
	
	public function _remap($method){
		$this->carabiner->css(array(
				array('libs/jquery-ui.css', 'screen'),
				array('libs/ui.notify.css', 'screen'),
				array('libs/jquery.treeview.css', 'screen'),
				array('base.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.min.js'),
				array('libs/jquery-ui.js'),
				array('libs/jquery.notify.min.js'),
				array('libs/jquery.treeview.js'),
				array('general/alertas.js')
		));
		
		$this->load->model("empleados_model");
		$this->load->model("aviones_model");
		if($this->empleados_model->checkSession()){
			$this->empleados_model->excepcion_privilegio = $this->excepcion_privilegio;
			$this->info_empleado = $this->empleados_model->getInfoEmpleado($_SESSION['id_empleado'], true);
			if($this->empleados_model->tienePrivilegioDe('', get_class($this).'/'.$method.'/')){
				$this->{$method}();
			}else
				redirect(base_url('panel/home?msg=1'));
		}else
			redirect(base_url('panel/home'));
	}
	
	private function index(){
		
	}
	
	private function pilotos(){
		$this->carabiner->css(array(
			array('libs/jquery.msgbox.css', 'screen'),
			array('libs/jquery.superbox.css', 'screen'),
			array('general/tables.css', 'screen'),
			array('general/forms.css', 'screen')
		));
		$this->carabiner->js(array(
			array('libs/jquery.msgbox.min.js'),
			array('libs/jquery.superbox.js'),
			array('nomina/admin.js')
		));
		$this->load->model('nomina_model');
		$this->load->library('pagination');
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['opcmenu_active'] = 'Pilotos'; //activa la opcion del menu
		$params['seo'] = array(
			'titulo' => 'Nomina Pilotos'
		);
		
		$params['nomina'] = $this->nomina_model->getNominaPilotos('30');
		
		if(!isset($_GET['ffecha1']))
			$_GET['ffecha1'] = date('Y-m').'-01';
		if(!isset($_GET['ffecha2']))
			$_GET['ffecha2'] = date('Y-m-d');
		
		if(isset($_GET['msg']{0}))
			$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
		$this->load->view('panel/header', $params);
		$this->load->view('panel/general/menu', $params);
		$this->load->view('panel/nomina/pilotos/nomina', $params);
		$this->load->view('panel/footer');
	}
	
	private function detalle_piloto() {
		$this->carabiner->css(array(
				array('libs/jquery.msgbox.css', 'screen'),
				array('libs/jquery.superbox.css', 'screen'),
				array('general/tables.css', 'screen'),
				array('general/forms.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.msgbox.min.js'),
				array('libs/jquery.superbox.js'),
				array('nomina/detalle_piloto.js')
		));
		$this->load->model('nomina_model');
		$this->load->library('pagination');
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['opcmenu_active'] = 'Pilotos'; //activa la opcion del menu
		$params['seo'] = array(
				'titulo' => 'Detalle de Piloto'
		);
		
		$params['cuentasp'] = $this->nomina_model->getDetallePiloto();
		
		if(!isset($_GET['ffecha1']))
			$_GET['ffecha1'] = date('Y-m').'-01';
		if(!isset($_GET['ffecha2']))
			$_GET['ffecha2'] = date('Y-m-d');
		
		if(isset($_GET['msg']{0}))
				$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
			$this->load->view('panel/header', $params);
			$this->load->view('panel/general/menu', $params);
			$this->load->view('panel/nomina/pilotos/detalle_piloto', $params);
			$this->load->view('panel/footer');
	}
	
	private function abono_piloto() {
		if(isset($_GET['id']{0})){
			$this->carabiner->css(array(
					array('general/forms.css', 'screen'),
					array('general/tables.css', 'screen'),
			));
			$this->carabiner->js(array(
					array('nomina/abono_piloto.js'),
					array('libs/jquery.numeric.js')
			));
			$this->load->model('nomina_model');
			$this->configAddAbonoPiloto();
			if($this->form_validation->run() == FALSE){
				$params['frm_errors']= $this->showMsgs(2, preg_replace("[\n|\r|\n\r]", '', validation_errors()));
			}
			else{
				$res = $this->nomina_model->abonar_piloto();
				if($res[0]){
					$params['frm_errors'] = $this->showMsgs(3);
					$params['load'] = true;
				}else $params['frm_errors']= $this->showMsgs(2, $res['msg']);
			}
			$res = $this->nomina_model->get_info_abonos();
			$params['total'] = $res;
			$params['seo']['titulo'] = 'Abonar a Piloto';
			$this->load->view('panel/nomina/pilotos/abono',$params);
		}else redirect(base_url('panel/nomina/detalle_piloto/?'.String::getVarsLink(array('msg')).'&msg=1'));
	}
	
	private function pilotos_pdf() {
		$this->load->model('nomina_model');
		$this->nomina_model->nominaPDF();
	}
	
	private function dp_pdf() {
		$this->load->model('nomina_model');
		$this->nomina_model->dp_pdf();
	}
	
	/**
	 * Muestra el listado de compras en XLS de un piloto seleccionado
	 */
	private function dp_xls(){
		$this->load->model('nomina_model');
		$this->nomina_model->nominaPilotoExcel();
	}
	
	private function configAddAbonoPiloto() {
		$this->load->library('form_validation');
		$rules = array(
				array('field'   => 'ffecha',
						'label'         => 'Fecha',
						'rules'         => 'required|max_length[10]|callback_isValidDate'),
				array('field'   => 'fconcepto',
						'label'         => 'Concepto',
						'rules'         => 'required|max_length[200]'),
				array('field'   => 'fabono',
						'label'         => 'Total a Abonar',
						'rules'         => 'required|callback_verifica_abono')
		);
		$this->form_validation->set_rules($rules);
	}
	
	public function verifica_abono($str) {
		$res = $this->nomina_model->get_info_abonos();
		$abono = floatval($str);
		if($abono>$res->restante){
			$this->form_validation->set_message('verifica_abono', 'El Abono que ingreso no puede ser mayor al Saldo');
			return false;
		}
		if($abono==0){
			$this->form_validation->set_message('verifica_abono', 'El Abono que ingreso no puede ser de Cero (0)');
			return false;
		}
	}
	
	/**
	 * Form_validation: Valida su una fecha esta en formato correcto
	 */
	public function isValidDate($str){
		if($str != ''){
			if(String::isValidDate($str) == false){
				$this->form_validation->set_message('isValidDate', 'El campo %s no es una fecha valida');
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Muestra mensajes cuando se realiza alguna accion
	 * @param unknown_type $tipo
	 * @param unknown_type $msg
	 * @param unknown_type $title
	 */
	private function showMsgs($tipo, $msg='', $title='Nomina !'){
		switch($tipo){
			case 1:
				$txt = 'El campo ID es requerido.';
				$icono = 'error';
				break;
			case 2: //Cuendo se valida con form_validation
				$txt = $msg;
				$icono = 'error';
				break;
			case 3:
				$txt = 'El Abono se realizo correctamente.';
				$icono = 'ok';
				break;
			case 4:
				$txt = 'El Vuelo se agrego correctamente.';
				$icono = 'ok';
				break;
			case 5:
				$txt = 'El Vuelo se elimino correctamente.';
				$icono = 'ok';
				break;
		}
	
		return array(
				'title' => $title,
				'msg' => $txt,
				'ico' => $icono);
	}
	
}?>
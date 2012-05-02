<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class tickets extends MY_Controller {
	
	/**
	 * Evita la validacion (enfocado cuando se usa ajax). Ver mas en privilegios_model
	 * @var unknown_type
	 */
	private $excepcion_privilegio = array('tickets/ajax_get_total_vuelos/','tickets/ajax_submit_form/');
	
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
	
	/**
	 * Default. Mustra el listado de proveedores para administrarlos
	 */
	public function index(){
		$this->carabiner->css(array(
			array('libs/jquery.msgbox.css', 'screen'),
			array('general/forms.css', 'screen'),
			array('general/tables.css', 'screen')
		));
		$this->carabiner->js(array(
			array('libs/jquery.msgbox.min.js'),
			array('general/msgbox.js')
		));
		$this->load->model('pilotos_model');
		$this->load->library('pagination');
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['opcmenu_active'] = 'Pilotos'; //activa la opcion del menu
		$params['seo'] = array(
			'titulo' => 'Administrar Pilotos'
		);
		
		$params['pilotos'] = $this->pilotos_model->getPilotos();
		
		if(isset($_GET['msg']{0}))
			$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
		$this->load->view('panel/header', $params);
		$this->load->view('panel/general/menu', $params);
		$this->load->view('panel/pilotos/listado', $params);
		$this->load->view('panel/footer');
	}
	
	public function agregar(){
		$this->carabiner->css(array(
				array('libs/jquery.msgbox.css', 'screen'),
				array('libs/jquery.superbox.css', 'screen'),
				array('general/forms.css', 'screen'),
				array('general/tables.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.msgbox.min.js'),
				array('libs/jquery.superbox.js'),
				array('general/util.js'),
				array('general/msgbox.js'),
				array('tickets/frm_addmod.js')
		));
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['seo'] = array(
				'titulo' => 'Agregar Ticket'
		);
		$params['opcmenu_active'] = 'Tickets'; //activa la opcion del menu
		
		$this->load->model('tickets_model');
		$params['ticket'] = $this->tickets_model->getNxtFolio();
		
		if(isset($_GET['msg']{0}))
				$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
			$this->load->view('panel/header', $params);
			$this->load->view('panel/general/menu', $params);
			$this->load->view('panel/tickets/agregar', $params);
			$this->load->view('panel/footer');
	}
	
	
	public function modificar(){
		$this->carabiner->css(array(
				array('libs/jquery.msgbox.css', 'screen'),
				array('general/forms.css', 'screen'),
				array('general/tables.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.msgbox.min.js'),
				array('general/msgbox.js'),
				array('pilotos/frm_addmod.js')
		));
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['seo'] = array(
				'titulo' => 'Modificar Piloto'
		);
		$params['opcmenu_active'] = 'Pilotos'; //activa la opcion del menu
		
		if(isset($_GET['id']{0}))
		{
			$this->load->model('pilotos_model');
			$this->configAddPiloto('update');
			
			if($this->form_validation->run() == FALSE){
				$params['frm_errors'] = $this->showMsgs(2, preg_replace("[\n|\r|\n\r]", '', validation_errors()));
			}else{
				
				$respons = $this->pilotos_model->updatePiloto();
			
				if($respons[0])
					redirect(base_url('panel/pilotos/modificar/?'.String::getVarsLink(array('msg')).'&msg=3'));
			}
			
			$params['piloto']	= $this->pilotos_model->getInfoPiloto($_GET['id']);
			
			if(!is_array($params['piloto']))
				unset($params['piloto']);
			
			if(isset($_GET['msg']{0}))
					$params['frm_errors'] = $this->showMsgs($_GET['msg']);
			
				$this->load->view('panel/header', $params);
				$this->load->view('panel/general/menu', $params);
				$this->load->view('panel/pilotos/modificar', $params);
				$this->load->view('panel/footer');
		}
		else
			$params['frm_errors'] = $this->showMsgs(1);
	}
	
	public function eliminar(){
		
		if(isset($_GET['id']{0})){
			$this->load->model('pilotos_model');
			$respons = $this->pilotos_model->delPiloto();
			
			if($respons[0])
				redirect('panel/pilotos/?&msg=5');
		}
		else
			$params['frm_errors']	= $this->showMsgs(1);
	}
	
	public function agregar_contacto(){
		
		if(isset($_GET['id']{0}))
		{
			$this->load->library('form_validation');
			
			$rules[] = array('field'	=> 'dcnombre',
					'label'		=> 'Contacto Nombre',
					'rules'		=> 'max_length[120]');
			$rules[] = array('field'	=> 'dcdomicilio',
					'label'		=> 'Contacto Domicilio',
					'rules'		=> 'max_length[200]');
			$rules[] = array('field'	=> 'dcmunicipio',
					'label'		=> 'Contacto Municipio',
					'rules'		=> 'max_length[40]');
			$rules[] = array('field'	=> 'dcestado',
					'label'		=> 'Contacto Estado',
					'rules'		=> 'max_length[40]');
			$rules[] = array('field'	=> 'dctelefono',
					'label'		=> 'Contacto Teléfono',
					'rules'		=> 'max_length[15]');
			$rules[] = array('field'	=> 'dccelular',
					'label'		=> 'Contacto Celular',
					'rules'		=> 'max_length[20]');
			
			$this->form_validation->set_rules($rules);
			
			if($this->form_validation->run() == FALSE)
			{
				$params['msg'] = $this->showMsgs(2, preg_replace("[\n|\r|\n\r]", '', validation_errors()));
			}
			else
			{
				$this->load->model('pilotos_model');
				$params['msg']	= $this->pilotos_model->addContacto($_GET['id']);
				
				if($params['msg'][0])
				{
					$res = $this->db->select('*')->from('proveedores_contactos_piloto')->where("id_contacto = '".$params['msg'][2]."'")->get();
					$params['info'] = $res->row();
					$params['msg']	= $this->showMsgs(6);
				}
			}
		}
		else
			$params['frm_errors'] = $this->showMsgs(1);
		
		echo json_encode($params);
	}
	
	public function eliminar_contacto(){
		if(isset($_GET['id']{0}))
		{
			$this->load->model('pilotos_model');
			$response['msg'] = $this->pilotos_model->delContacto($_GET['id']);
			if($response['msg'][0])
				$params['msg'] = $this->showMsgs(7);
		}
		else
			$params['msg']  = $this->showMsgs(1);
		
		echo json_encode($params);
	}
	
	/**
	 * Obtiene lostado de pilotos para el autocomplete, ajax
	 */
	public function ajax_get_total_vuelos(){
		$this->load->model('tickets_model');
		$params = $this->tickets_model->getTotalVuelosAjax();
	
		echo json_encode($params);
	}
	
	
	public function ajax_submit_form(){
		
		$this->load->library('form_validation');
		$rules = array(
				array('field'	=> 'tcliente',
						'label'		=> 'Cliente',
						'rules'		=> 'required|max_length[25]'),
				array('field'	=> 'tfolio',
						'label'		=> 'Folio',
						'rules'		=> 'required|is_natural_no_zero'),
				array('field'	=> 'tfecha',
						'label'		=> 'Fecha',
						'rules'		=> 'required|max_length[10]|callback_isValidDate'),
				array('field'	=> 'tipo_pago',
						'label'		=> 'Tipo pago',
						'rules'		=> 'required|max_length[10]'),
				array('field'	=> 'tdias_credito',
						'label'		=> 'Dias de Credito',
						'rules'		=> 'is_natural'),
				array('field'	=> 'subtotal',
						'label'		=> 'Subtotal',
						'rules'		=> 'required'),
				array('field'	=> 'iva',
						'label'		=> 'Iva',
						'rules'		=> 'required'),
				array('field'	=> 'total',
						'label'		=> 'Total',
						'rules'		=> 'required'),
				array('field'	=> 'vuelos',
						'label'		=> 'Vuelos',
						'rules'		=> 'required')
		);
		$this->form_validation->set_rules($rules);
		
		if($this->form_validation->run() == FALSE)
		{
			$params['msg']= $this->showMsgs(2, preg_replace("[\n|\r|\n\r]", '', validation_errors()));
		}
		else
		{
			$this->load->model('tickets_model');
			$params	= $this->tickets_model->addTicket();
		
			if($params[0])
				$params['msg'] = $this->showMsgs(4);
		}
		
		echo json_encode($params);
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
	private function showMsgs($tipo, $msg='', $title='Tickets !'){
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
				$txt = 'El Ticket se modifico correctamente.';
				$icono = 'ok';
				break;
			case 4:
				$txt = 'El Ticket se agrego correctamente.';
				$icono = 'ok';
				break;
			case 5:
				$txt = 'El Ticket se elimino correctamente.';
				$icono = 'ok';
				break;
		}
	
		return array(
				'title' => $title,
				'msg' => $txt,
				'ico' => $icono);
	}
	
}
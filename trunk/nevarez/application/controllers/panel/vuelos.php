<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class vuelos extends MY_Controller {
	
	/**
	 * Evita la validacion (enfocado cuando se usa ajax). Ver mas en privilegios_model
	 * @var unknown_type
	 */
	private $excepcion_privilegio = array('vuelos/vuelos_cliente/');
	
	
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
			array('general/msgbox.js'),
			array('vuelos/admin.js')
		));
		
		$this->load->model('vuelos_model');
		$this->load->library('pagination');
		$this->load->library('form_validation');
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['opcmenu_active'] = 'Vuelos'; //activa la opcion del menu
		$params['seo'] = array(
				'titulo' => 'Administrar Vuelos'
		);
		
		if($this->input->get('ffecha_ini')!=''){
			$_POST['ffecha_ini'] = $this->input->get('ffecha_ini');
			$rules[] = array('field'=>'ffecha_ini','label'=>'Fecha inicio','rules'=>'callback_isValidDate');
		}		
		if($this->input->get('ffecha_fin')!=''){
			$_POST['ffecha_fin'] = $this->input->get('ffecha_fin');
			$rules[] = array('field'=>'ffecha_fin','label'=>'Fecha fin','rules'=>'callback_isValidDate');
		}
				
		if(isset($rules))
		{ 
			$this->form_validation->set_rules($rules);
			
			if($this->form_validation->run() == FALSE)
			{
				$params['frm_errors'] = $this->showMsgs(2, preg_replace("[\n|\r|\n\r]", '', validation_errors()));
				$params['vuelos']['vuelos'] = array();
				$params['vuelos']['total_rows'] = 0;
				$params['vuelos']['items_per_page'] = 0;
				$params['vuelos']['result_page'] = 0;
			}
			else
				$params['vuelos'] = $this->vuelos_model->getVuelos();		
		}
		else
			$params['vuelos'] = $this->vuelos_model->getVuelos();
		
		if(isset($_GET['msg']{0}))
			$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
		$this->load->view('panel/header', $params);
		$this->load->view('panel/general/menu', $params);
		$this->load->view('panel/vuelos/listado', $params);
		$this->load->view('panel/footer');
	}
	
	public function agregar(){
		$this->carabiner->css(array(
				array('libs/jquery.msgbox.css', 'screen'),
				array('general/forms.css', 'screen'),
				array('general/tables.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.msgbox.min.js'),
				array('general/msgbox.js'),
				array('vuelos/frm_addmod.js'),
				array('libs/jquery-ui-timepicker-addon.js')
		));
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['seo'] = array(
				'titulo' => 'Agregar Vuelo'
		);
		$params['opcmenu_active'] = 'Vuelos'; //activa la opcion del menu
		$this->configAddVuelo('add');
		
		if($this->form_validation->run() == FALSE){
			$params['frm_errors'] = $this->showMsgs(2, preg_replace("[\n|\r|\n\r]", '', validation_errors()));
		}else{
			$this->load->model('vuelos_model');
			$respons = $this->vuelos_model->addVuelo();
				
			if($respons[0])
				redirect(base_url('panel/vuelos/agregar/?'.String::getVarsLink(array('msg')).'&msg='.$respons[2]));
		}
		$params['infoc'] = array();
		if(isset($_POST['hids'])){
			$this->load->model('clientes_model');
			foreach ($_POST['hids'] as $id)
				$params['infoc'][] = $this->clientes_model->getInfoCliente($id, true);
			
			foreach ($params['infoc'] as $c){
				$split = explode('.', $c['info']->id_cliente);
				$c['info']->id = $split[0].''.$split[1];
			}
				
		}
		
		$params['prod_venta'] = $this->db->query("
									SELECT p.id_producto, p.nombre 
									FROM productos as p 
									INNER JOIN productos_familias AS pf ON p.id_familia=pf.id_familia 
									WHERE pf.tipo='venta'")->result();
		
		if(isset($_GET['msg']{0}))
				$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
			$this->load->view('panel/header', $params);
			$this->load->view('panel/general/menu', $params);
			$this->load->view('panel/vuelos/agregar', $params);
			$this->load->view('panel/footer');		
	}
		
	public function eliminar(){
		
		if(isset($_GET['id']{0})){
			$this->load->model('vuelos_model');
			$respons = $this->vuelos_model->delVuelo();
			
			if($respons[0])
				redirect('panel/vuelos/?&msg=5');
		}
		else
			$params['frm_errors']	= $this->showMsgs(1);
	}
	
	/**
	 * Muestra el listado de vuelos para un cliente
	 * Usado en el superbox.
	 *
	 */
	public function vuelos_cliente(){
		if(isset($_GET['id']{0})){
			
			$this->carabiner->css(array(
					array('general/forms.css', 'screen'),
					array('general/tables.css', 'screen')
			));
			
			$this->carabiner->js(array(
					array('vuelos/vuelos_tickets.js')		
			));
			
			
			$params['seo'] = array(
					'titulo' => 'Vuelos'
			);
		
			$this->load->model('vuelos_model');
			$params['cliente'] = $this->vuelos_model->getVuelosCliente($_GET['id']);
		}
		else
			$params['frm_errors'] = $this->showMsgs(1);
	
		$this->load->view('panel/vuelos/vuelos_cliente',$params);
	}
	
	
	private function configAddVuelo($tipo){
		$this->load->library('form_validation');
		$rules = array(
				array('field'	=> 'hids[]',
						'label'		=> 'Cliente',
						'rules'		=> 'required|max_length[25]'),
				array('field'	=> 'havion',
						'label'		=> 'Avión',
						'rules'		=> 'required|max_length[25]'),
				array('field'	=> 'hpiloto',
						'label'		=> 'Piloto',
						'rules'		=> 'required|max_length[25]'),
				array('field'	=> 'dfecha',
						'label'		=> 'Fecha',
						'rules'		=> 'required|max_length[16]'),
				array('field'	=> 'dcliente',
						'label'		=> '',
						'rules'		=> ''),
				array('field'	=> 'dcliente_info',
						'label'		=> '',
						'rules'		=> ''),
				array('field'	=> 'davion',
						'label'		=> '',
						'rules'		=> ''),
				array('field'	=> 'davion_info',
						'label'		=> '',
						'rules'		=> ''),
				array('field'	=> 'dpiloto',
						'label'		=> 'Cliente',
						'rules'		=> ''),
				array('field'	=> 'dpiloto_info',
						'label'		=> '',
						'rules'		=> ''),
				array('field'	=> 'dproducto',
						'label'		=> '',
						'rules'		=> '')
			);
				
		$this->form_validation->set_rules($rules);
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
	private function showMsgs($tipo, $msg='', $title='Vuelos !'){
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
				$txt = 'El Vuelo se modifico correctamente.';
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
	
}
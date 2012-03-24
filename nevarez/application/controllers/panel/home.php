<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class home extends MY_Controller {
	
	public function _remap($method){
		$this->carabiner->css(array(
				array('libs/jquery-ui.css', 'screen'),
				array('libs/ui.notify.css', 'screen'),
				array('base.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.min.js'),
				array('libs/jquery-ui.js'),
				array('libs/jquery.notify.min.js'),
				array('general/alertas.js'),
		));
		
		$this->load->model("empleados_model");
		if($this->empleados_model->checkSession()){
			$this->info_empleado = $this->empleados_model->getInfoEmpleado($_SESSION['id_empleado'], true);
			$this->{$method}();
		}else
			$this->{'login'}();
	}
	
	public function index(){
		$this->carabiner->css(array(
				array('libs/jquery.treeview.css', 'screen')
		));
		$this->carabiner->js(array(
				array('libs/jquery.treeview.js')
		));
		
		$params['info_empleado'] = $this->info_empleado['info']; //info empleado
		$params['seo'] = array(
			'titulo' => 'Panel de Administración'
		);
		
		$this->load->model('privilegios_model');
		
		$this->load->view('panel/header', $params);
		$this->load->view('panel/general/menu', $params);
		$this->load->view('panel/general/home', $params);
		$this->load->view('panel/footer');
	}
	
	/**
	 * carga el login para entrar al panel
	 */
	public function login(){
		$this->carabiner->css(array(
				array('general/forms.css', 'screen')
		));
		
		$params['seo'] = array(
			'titulo' => 'Login'
		);
		
		$this->load->library('form_validation');
		$rules = array(
			array('field'	=> 'usuario',
				'label'		=> 'Usuario',
				'rules'		=> 'required'),
			array('field'	=> 'pass',
				'label'		=> 'Contraseña',
				'rules'		=> 'required')
		);
		$this->form_validation->set_rules($rules);
		if($this->form_validation->run() == FALSE){
			$params['frm_errors'] = array(
					'title' => 'Error al Iniciar Sesión!', 
					'msg' => preg_replace("[\n|\r|\n\r]", '', validation_errors()), 
					'ico' => 'error');
		}else{
			$respons = $this->empleados_model->login();
			if($respons[0])
				redirect(base_url('panel/home'));
			else{
				$params['frm_errors'] = array(
					'title' => 'Error al Iniciar Sesión!',
					'msg' => $respons[1],
					'ico' => 'error');
			}
		}
		
		$this->load->view('panel/header', $params);
		$this->load->view('panel/general/login', $params);
		$this->load->view('panel/footer');
	}
	
	/**
	 * cierra la sesion del usuario
	 */
	public function logout(){
		session_destroy();
		redirect(base_url('panel/home'));
	}
}

?>
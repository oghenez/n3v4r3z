<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class vehiculo extends MY_Controller {
	
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
			$this->info_empleado = $this->empleados_model->getInfoEmpleado($_SESSION['id_empleado'], true);
			if($this->empleados_model->tienePrivilegioDe('', get_class($this).'/'.$method.'/')){
				$this->{$method}();
			}else
				redirect(base_url('panel/home?msg=1'));
		}else
			redirect(base_url('panel/home'));
	}
	
	public function index(){
		$this->carabiner->css(array(
					array('libs/jquery.msgbox.css','screen'),
					array('libs/jquery.superbox.css','screen'),
					array('general/forms.css','screen'),
					array('general/tables.css','screen')
				));
		
		$this->carabiner->js(array(
					array('libs/jquery.msgbox.min.js'),
					array('libs/jquery.superbox.js'),
					array('vehiculos/vehiculos.js'),
					array('general/msgbox.js')
					));
		
		$params['info_empleado']	= $this->info_empleado['info'];
		$params['opcmenu_active'] = 'Vehiculos'; //activa la opcion del menu
		$params['seo']	= array('titulo' => 'Vehículos');
		
		$params['empleados'] = $this->empleados_model->getEmpleados();
		
		$this->load->view('panel/header',$params);
		$this->load->view('panel/general/menu',$params);
		$this->load->view('panel/vehiculo/listado',$params);
		$this->load->view('panel/footer',$params);
	}
	
	public function agregar(){
		$this->carabiner->css(array(
				array('libs/jquery.msgbox.css','screen'),
				array('general/forms.css','screen'),
				array('general/tables.css','screen')
		));
		
		$this->carabiner->js(array(
				array('libs/jquery.msgbox.min.js'),
				array('general/msgbox.js')
		));
		
		$params['info_empleado']	= $this->info_empleado['info'];
		$params['opcmenu_active'] = 'Vehiculos'; //activa la opcion del menu
		$params['seo']	= array('titulo' => 'Agregar Vehículos');
		
		$this->configAddVehiculo();

		if($this->form_validation->run() == FALSE)
		{
			$params['frm_errors']	= $this->showMsgs(2,preg_replace("[\n|\r|\n\r]", '', validation_errors()));
		}
		else
		{
// 			$model_resp	= $this->vehiculos_model->addVehiculo();
// 			if($model_resp[0])
				redirect(base_url('panel/vehiculo/agregar/?'.String::getVarsLink(array('msg')).'&msg=4'));
		}

		if(isset($_GET['msg']{0}))
				$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
		$this->load->view('panel/header',$params);
		$this->load->view('panel/general/menu',$params);
		$this->load->view('panel/vehiculo/agregar',$params);
		$this->load->view('panel/footer',$params);
	}
	
	private function modificar(){
		$this->carabiner->css(array(
				array('libs/jquery.msgbox.css','screen'),
				array('general/forms.css','screen'),
				array('general/tables.css','screen')
		));
		
		$this->carabiner->js(array(
				array('libs/jquery.msgbox.min.js'),
				array('general/msgbox.js')
		));
		
		
		
		$params['seo']['titulo']	= 'Modificar Vehículo';
		
		$this->load->view('panel/vehiculo/modificar',$params);
	}
	
	private function configAddVehiculo(){
		$this->load->library('form_validation');
		
		$rules = array(
						array('field'	=> 'fnombre',
								'label'	=> 'Nombre',
							  	'rules'	=> 'required|max_lenght[100]'),
						array('field'	=> 'fplacas',
								'label'	=> 'Placas',
								'rules'	=> 'required|max_lenght[100]'),
						array('field'	=> 'fcolor',
								'label'	=> 'Color',
								'rules'	=> 'max_lenght[20]'),
						array('field'	=> 'fmodelo',
								'label'	=> 'Modelo',
								'rules'	=> 'max_lenght[20]'),
						array('field'	=> 'fano',
								'label'	=> 'Año',
								'rules'	=> 'numeric')
				);
		$this->form_validation->set_rules($rules);
	}
	
	/**
	 * Muestra mensajes cuando se realiza alguna accion
	 * @param unknown_type $tipo
	 * @param unknown_type $msg
	 * @param unknown_type $title
	 */
	private function showMsgs($tipo, $msg='', $title='Vehiculo!'){
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
				$txt = 'El Vehículo se modifico correctamente.';
				$icono = 'ok';
				break;
			case 4:
				$txt = 'El Vehículo se agrego correctamente.';
				$icono = 'ok';
				break;
			case 5:
				$txt = 'El Vehículo se elimino correctamente.';
				$icono = 'ok';
				break;
		}
	
		return array(
				'title' => $title,
				'msg' => $txt,
				'ico' => $icono);
	}	
}
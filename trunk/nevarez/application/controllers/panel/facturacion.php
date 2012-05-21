<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class facturacion extends MY_Controller {
	
	/**
	 * Evita la validacion (enfocado cuando se usa ajax). Ver mas en privilegios_model
	 * @var unknown_type
	 */
	private $excepcion_privilegio = array('');
	
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
		$this->carabiner->css(array(
					array('libs/jquery.msgbox.css','screen'),
					array('libs/jquery.superbox.css','screen'),
					array('general/forms.css','screen'),
					array('general/tables.css','screen')
				));
		
		$this->carabiner->js(array(
					array('libs/jquery.msgbox.min.js'),
					array('libs/jquery.superbox.js'),
					array('general/msgbox.js')
					));
		
		$this->load->library('pagination');
		
		$params['info_empleado']	= $this->info_empleado['info'];
		$params['opcmenu_active']	= 'Aviones'; //activa la opcion del menu
		$params['seo']				= array('titulo' => 'Administrar Aviones');
		
		$params['datos_a'] = $this->aviones_model->getAviones();
		
		if(isset($_GET['msg']))
		{
			$msg = ($_GET['msg']==2) ? 'El Avión no existe' : '';
			$params['frm_errors']	= $this->showMsgs($_GET['msg'],$msg);
		}
		
		$this->load->view('panel/header',$params);
		$this->load->view('panel/general/menu',$params);
		$this->load->view('panel/aviones/admin',$params);
		$this->load->view('panel/footer',$params);
	}
	
	
	private function index_series_folios(){
		$this->carabiner->css(array(
				array('general/forms.css','screen'),
				array('general/tables.css','screen')
		));
	
		$this->load->library('pagination');
		$this->load->model('facturacion_model');
	
		$params['info_empleado']	= $this->info_empleado['info'];
		$params['opcmenu_active']	= 'Facturación'; //activa la opcion del menu
		$params['seo']				= array('titulo' => 'Administrar Series y Folios');
	
		$params['datos_s'] = $this->facturacion_model->getSeriesFolios();
	
		$this->load->view('panel/header',$params);
		$this->load->view('panel/general/menu',$params);
		$this->load->view('panel/facturacion/series_folios/admin',$params);
		$this->load->view('panel/footer',$params);
	}
	
	private function agregar_serie_folio(){
		$this->carabiner->css(array(
				array('general/forms.css','screen'),
				array('general/tables.css','screen')
		));
				
		$params['info_empleado']	= $this->info_empleado['info'];
		$params['opcmenu_active'] = 'Facturación'; //activa la opcion del menu
		$params['seo']	= array('titulo' => 'Agregar Series y Folios');
		
		$this->load->model('facturacion_model');
		$this->configAddSerieFolio();

		if($this->form_validation->run() == FALSE)
		{
			$params['frm_errors']	= $this->showMsgs(2,preg_replace("[\n|\r|\n\r]", '', validation_errors()));
		}
		else
		{
			$model_resp	= $this->facturacion_model->addSerieFolio();
			if($model_resp[0])
				redirect(base_url('panel/facturacion/agregar_serie_folio/?'.String::getVarsLink(array('msg')).'&msg=6'));
			else
				$params['frm_errors'] = $this->showMsgs(2,$model_resp[1]);	
		}

		if(isset($_GET['msg']{0}))
				$params['frm_errors'] = $this->showMsgs($_GET['msg']);
		
		$this->load->view('panel/header',$params);
		$this->load->view('panel/general/menu',$params);
		$this->load->view('panel/facturacion/series_folios/agregar',$params);
		$this->load->view('panel/footer',$params);
	}
	
	private function modificar_serie_folio(){
		
		if(isset($_GET['id']{0})){
			$this->carabiner->css(array(
					array('general/forms.css','screen'),
					array('general/tables.css','screen')
			));
			
			$this->load->model('facturacion_model');
			$this->configAddSerieFolio('edit');
			
			if($this->form_validation->run() == FALSE)
			{
				$params['frm_errors']	= $this->showMsgs(2,preg_replace("[\n|\r|\n\r]", '', validation_errors()));
			}
			else
			{
				$model_resp	= $this->facturacion_model->editSerieFolio($_GET['id']);
				if($model_resp[0])
					$params['frm_errors']	= $this->showMsgs(3);
			}
			
			$params['info_empleado']	= $this->info_empleado['info'];
			$params['opcmenu_active'] 	= 'Facturación'; //activa la opcion del menu
			$params['seo']['titulo']	= 'Modificar Serie y Folio';
			
			$params['serie_info']	= $this->facturacion_model->getInfoSerieFolio($_GET['id']);
			
			if(isset($_GET['msg']{0}))
					$params['frm_errors'] = $this->showMsgs($_GET['msg']);
			
				$this->load->view('panel/header',$params);
				$this->load->view('panel/general/menu',$params);
				$this->load->view('panel/facturacion/series_folios/modificar',$params);
				$this->load->view('panel/footer',$params);
		}
		else
			redirect(base_url('panel/facturacion/index_serie_folios/').String::getVarsLink(array('msg')).'&msg=1');
	}
	
	private function eliminar(){
		if(isset($_GET['id']))
		{
			$result_model = $this->aviones_model->delAvion($_GET['id']);
			if($result_model[0])
				redirect(base_url('panel/aviones/?&msg=5'));
			else
				redirect(base_url('panel/aviones/?&msg=2'));
		}
	}	
	
	private function configAddSerieFolio($tipo='add'){
		$this->load->library('form_validation');
	
		$rules = array(
// 						array('field'	=> 'fserie',
// 								'label'	=> 'Serie',
// 							  	'rules'	=> 'max_lenght[30]|callback_isValidSerie'),
						array('field'	=> 'fno_aprobacion',
								'label'	=> 'No Aprobación',
								'rules'	=> 'required|numeric'),
						array('field'	=> 'ffolio_inicio',
								'label'	=> 'Folio Inicio',
								'rules'	=> 'required|is_natural'),
						array('field'	=> 'ffolio_fin',
								'label'	=> 'Folio Fin',
								'rules'	=> 'required|is_natural'),
						array('field'	=> 'fano_aprobacion',
								'label'	=> 'Año Aprobación',
								'rules'	=> 'required|max_lenght[4]|is_natural|callback_isValidYear'),
// 						array('field'	=> 'durl_img',
// 								'label'	=> 'Imagen',
// 								'rules'	=> 'required'),
						array('field'	=> 'fleyenda',
								'label'	=> 'Leyenda',
								'rules'	=> ''),
						array('field'	=> 'fleyenda1',
								'label'	=> 'Leyenda 1',
								'rules'	=> ''),
						array('field'	=> 'fleyenda2',
								'label'	=> 'Leyenda 2',
								'rules'	=> '')
				);
		
		if($tipo=='add'){
			if(isset($_FILES['durl_img']))
				if($_FILES['durl_img']['name']!='')
				$_POST['durl_img'] = 'ok';

			$rules[] = array('field'	=> 'fserie',
								'label'	=> 'Serie',
							  	'rules'	=> 'max_lenght[30]|callback_isValidSerie[add]');
			$rules[] = array('field'	=> 'durl_img',
					'label'	=> 'Imagen',
					'rules'	=> 'required');
		}
		
		if($tipo=='edit'){
			$rules[] = array('field'	=> 'fserie',
							'label'	=> 'Serie',
							'rules'	=> 'max_lenght[30]|callback_isValidSerie[edit]');
		}
		
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
	 * Form_validation: Valida su una fecha esta en formato correcto
	 */
	public function isValidYear($str){
		if($str != ''){
			$year = intval($str);
			if( $year<1000 || $year>2100){
				$this->form_validation->set_message('isValidYear', 'El campo %s no es un año valido');
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * Form_validation: Valida si la Serie ya existe
	 */
	public function isValidSerie($str, $tipo){
		$str = ($str=='') ? '' : $str;

		if($tipo=='add'){
			if($this->facturacion_model->exist('facturacion_series_folios',array('serie' =>strtoupper($str)))){
				$this->form_validation->set_message('isValidSerie', 'El campo %s ya existe');
				return false;
			}
			return true;
		}
		else{
			$row = $this->facturacion_model->exist('facturacion_series_folios',array('serie' =>strtoupper($str)),true);
			
			if($row!=FALSE){
				if($row->id_serie_folio == $_GET['id'])
					return true;
				else{
					$this->form_validation->set_message('isValidSerie', 'El campo %s ya existe');
					return false;
				}
			}return true;
		}
			
	}
	
	/**
	 * Muestra mensajes cuando se realiza alguna accion
	 * @param unknown_type $tipo
	 * @param unknown_type $msg
	 * @param unknown_type $title
	 */
	private function showMsgs($tipo, $msg='', $title='Aviones!'){
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
				$txt = 'La Factura se modifico correctamente.';
				$icono = 'ok';
				break;
			case 4:
				$txt = 'La Factura se agrego correctamente.';
				$icono = 'ok';
				break;
			case 5:
				$txt = 'La Factura se elimino correctamente.';
				$icono = 'ok';
				break;
			case 6:
				$txt = 'La Serie y Folio se agregaron correctamente.';
				$icono = 'ok';
				break;
			case 7:
				$txt = 'La Serie y Folio se modifico correctamente.';
				$icono = 'ok';
				break;
		}
	
		return array(
				'title' => $title,
				'msg' => $txt,
				'ico' => $icono);
	}	
}
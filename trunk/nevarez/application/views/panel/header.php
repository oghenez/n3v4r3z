<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title><?php echo $seo['titulo'];?></title>
	
<?php
	if(isset($this->carabiner)){
		$this->carabiner->display('css');
		$this->carabiner->display('js');
	}
?>
<script type="text/javascript" charset="UTF-8">
	var base_url = "<?php echo base_url();?>",
	opcmenu_active = '<?php echo isset($opcmenu_active)? $opcmenu_active: 0;?>';
</script>
</head>
<body>

<div id="header">
	<div class="logo f-l"><img alt="logo" src="http://www.redfire.com.mx/lib/themes/gp/images/Logo.jpg" width="150" height="60"></div>
	<div class="titulo f-l"><?php echo $seo['titulo'];?></div>
	<div class="info_user f-l a-r">
<?php
	if($this->empleados_model->checkSession()){
		$info_empleado;
		echo '<div class="info_empleado"><img src="'.base_url($info_empleado->url_img).'" width="30" height="30"> '.$info_empleado->nombre.' <br>
			<a href="'.base_url('panel/home/logout').'">Cerrar sesión</a>
		</div>';
	}
?>
	</div>
	<div class="clear"></div>
</div>
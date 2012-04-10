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
<div>
	<div class="titulo ajus w100 am-c"><?php echo $seo['titulo']; ?></div>
	<form action="<?php echo base_url('panel/productos/agregar'); ?>" method="post" class="frm_addmod">
			<input type="hidden" name="familia" value="<?php echo (isset($familia[0]->id_familia)? $familia[0]->id_familia: ''); ?>">
			<input type="hidden" name="codigo_familia" value="<?php echo (isset($familia[0]->codigo)? $familia[0]->codigo: ''); ?>">
			<p class="w20 f-l">
				<label for="dcodigo">*Código:</label> <span class="ej-info">Ej. 1, 8, 12, 20</span><br>
				<input type="text" name="dcodigo" id="dcodigo" value="<?php echo set_value('dcodigo'); ?>" size="5" maxlength="8" autofocus>
			</p>
			<p class="w50 f-l">
				<label for="dnombre">*Nombre:</label> <br>
				<input type="text" name="dnombre" id="dnombre" value="<?php echo set_value('dnombre'); ?>" size="30" maxlength="70">
			</p>
			<p class="w30 f-l">
				<label for="dunidad">*Unidad:</label> <br>
				<select name="dunidad" id="dunidad">
			<?php foreach($unidades as $itm){
				$sel = set_select('dunidad', $itm->id_unidad);
				echo $sel;
				echo '
					<option value="'.$itm->id_unidad.'"'.$sel.'>'.$itm->nombre.'</option>';
			} ?>
				</select>
			</p>
			<div class="clear"></div>
			
			<p class="w40 f-l m0-all">
				<label for="dddescripcion">Descripción:</label> <br>
				<textarea name="ddescripcion" id="ddescripcion" maxlength="300" rows="5" cols="30"><?php echo set_value('ddescripcion'); ?></textarea>
			</p>
			<p class="w50 f-l m0-all m10-b">
				<label for="dubicacion">Ubicación:</label> <br>
				<input type="text" name="dubicacion" id="dubicacion" value="<?php echo set_value('dubicacion'); ?>" size="30" maxlength="70">
			</p>
			<p class="w30 f-l m0-all">
				<label for="dstock_min">Stock min:</label> <br>
				<input type="text" name="dstock_min" id="dstock_min" value="<?php echo set_value('dstock_min'); ?>" class="vpositive" size="10" maxlength="30">
			</p>
			<p class="w30 f-l m0-all">
				<label for="dstock_max">Stock max:</label> <br>
				<input type="text" name="dstock_max" id="dstock_max" value="<?php echo set_value('dstock_max'); ?>" class="vpositive" size="10" maxlength="30">
			</p>
			<div class="clear"></div>
			
			<fieldset class="w40 f-l">
				<legend>Productos registrados</legend>
				<span style="display:block; text-align:center; margin: 4px 0;">
					<input type="search" id="buscar_pr" class="b-all corner-all" size="30" placeholder="Buscar...">
				</span>
				<table class="tblListados corner-top8 m0-tb">
					<tr class="header btn-gray">
						<td>Nombre</td>
					</tr>
				</table>
				<div id="tbl_productos_r" style="height: 200px;overflow-y:auto;">
				<?php 
				if(isset($tabla_productos_r))
					echo $tabla_productos_r;
				?>
				</div>
			</fieldset>
			
			<fieldset class="w55 f-r">
				<legend>Productos que consume</legend>
				<table class="tblListados corner-top8 m0-tb">
					<tr class="header btn-gray">
						<td class="w80">Nombre</td>
						<td class="w20">Cantidad</td>
					</tr>
				</table>
				<div id="" style="height: 200px;overflow-y:auto;">
					<table id="tbl-pl" class="tblListados corner-bottom8 m0-tb">
					<?php 
					if(is_array($this->input->post('dpcids'))){
						foreach($this->input->post('dpcids') as $key => $id){ ?>
						<tr id="tr-pl<?php echo str_replace('.', '-', $id); ?>" class="tr-produclista">
							<td class="w80"><?php echo $_POST['dpcnombres'][$key]; ?>
								<input type="hidden" name="dpcnombres[]" value="<?php echo $_POST['dpcnombres'][$key]; ?>">
								<input type="hidden" name="dpcids[]" value="<?php echo $id; ?>"></td>
							<td class="w20"><input type="text" name="dpccantidad[]" value="<?php echo $_POST['dpccantidad'][$key]; ?>" size="5"></td>
						</tr>
					<?php } 
					} ?>
					</table>
				</div>
			</fieldset>
			<a class="btnaddpro">>></a>
			<a class="btnquitpro"><<</a>
			
			<input type="submit" name="enviar" value="Guardar" class="btn-blue corner-all f-r">
	</form>
</div>


<!-- Bloque de alertas -->
<?php if(isset($frm_errors)){
	if($frm_errors['msg'] != ''){ 
?>
<div id="container" style="display:none">
	<div id="withIcon">
		<a class="ui-notify-close ui-notify-cross" href="#">x</a>
		<div style="float:left;margin:0 10px 0 0"><img src="#{icon}" alt="warning" width="64" height="64"></div>
		<h1>#{title}</h1>
		<p>#{text}</p>
		<div class="clear"></div>
	</div>
</div>
<script type="text/javascript" charset="UTF-8">
$(function(){
	create("withIcon", {
		title: '<?php echo $frm_errors['title']; ?>', 
		text: '<?php echo $frm_errors['msg']; ?>', 
		icon: '<?php echo base_url('application/images/alertas/'.$frm_errors['ico'].'.png'); ?>' });
	<?php 
		if(isset($load_productos)){
			echo "window.setTimeout(parent.closeBoxPrdutos, 1200, '".str_replace('.', '-', $this->input->get_post('familia'))."');";
		}
	?>
});
</script>
<?php }
}?>
<!-- Bloque de alertas -->

	
	<div class="clear"></div>
</body>
</html>
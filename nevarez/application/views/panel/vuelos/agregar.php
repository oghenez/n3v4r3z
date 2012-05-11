<div id="contentAll" class="f-l">
	<form action="<?php echo base_url('panel/vuelos/agregar')?>" method="post" class="frm_addmod">
		<div class="frmsec-left w75 f-l">
			<p class="f-r">
				<label for="dfecha">*Fecha</label>
				<input type="text" name="dfecha" value="<?php echo set_value('dfecha');?>" size="10" id="dfecha" readonly class="a-c">
			</p>
			<div class="clear"></div>
			
			<div class="frmsec-right w100">
				<div class="frmbox-r p5-tb corner-all">
					<p class="w100">
						<label for="dcliente" class="f-l">*Cliente</label><br>
						<input type="text" name="dcliente" value="<?php echo set_value('dcliente');?>" size="35" id="dcliente" class="f-l" autofocus>
						
						<?php 
						/*<textarea name="dcliente_info" id="dcliente_info" class="m10-l" rows="3" cols="55" readonly><?php echo set_value('dcliente_info'); ?></textarea>*/
						?>
						
						<table class="tblListados corner-all8" id="tbl_clientes" style="width:50% !important;margin-top:-15px !important;border: 1px #79B7E7 solid;">
							<tr class="header btn-gray">
								<td>Cliente</td>
								<td>Datos</td>
							</tr>
							<?php if(isset($infoc)):
									foreach ($infoc as $c):?>
										<tr>
											<td><?= $c['info']->nombre_fiscal?></td>
											<td><?= $c['info']->calle.', '.$c['info']->colonia.', '.$c['info']->municipio.', '.$c['info']->estado?></td>
										</tr>
							<?php endforeach;endif;?>
							
						</table>
						<p id="hidde-ids">
							<?php /*<input type="hidden" name="hcliente" value="<?php echo set_value('hcliente');?>" id="hcliente">*/?>
							
							<?php if(isset($_POST['hids'])):
									foreach ($_POST['hids'] as $id):?>
										<input type="hidden" name="hids[]" value="<?= $id?>">
							<?php
									endforeach;
								  endif;
							?>
						</p>
						
					</p>
					<div class="clear"></div>
				</div>
			</div>
			
			<div class="frmsec-right w100">
				<div class="frmbox-r p5-tb corner-all">
					<p class="w100">
						<label for="davion" class="f-l">*Avión</label><br>
						<input type="text" name="davion" value="<?php echo set_value('davion');?>" size="35" id="davion"  class="f-l">
						<input type="hidden" name="havion" value="<?php echo set_value('havion');?>" id="havion">
						
						<textarea name="davion_info" id="davion_info" class="m10-l" rows="3" cols="55" readonly><?php echo set_value('davion_info'); ?></textarea>
					</p>
					<div class="clear"></div>
				</div>
			</div>
			
			<div class="frmsec-right w100">
				<div class="frmbox-r p5-tb corner-all">
					<p class="w100">
						<label for="dpiloto" class="f-l">*Piloto</label><br>
						<input type="text" name="dpiloto" value="<?php echo set_value('dpiloto');?>" size="35" id="dpiloto"  class="f-l">
						<input type="hidden" name="hpiloto" value="<?php echo set_value('hpiloto');?>" id="hpiloto">
						
						<textarea name="dpiloto_info" id="dpiloto_info" class="m10-l" rows="3" cols="55" readonly><?php echo set_value('dpiloto_info'); ?></textarea>
					</p>
					<div class="clear"></div>
				</div>
			</div>
			
			<input type="submit" name="enviar" value="Guardar" class="btn-blue corner-all f-r">
		</div>
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
});
</script>
<?php }
}?>
<!-- Bloque de alertas -->


<div id="contentAll" class="f-l corner-all">
	<form action="<?php echo base_url('panel/tickets/agregar'); ?>" method="post" class="frm_addmod">
		<div class="frmsec-left w80 f-l b-r">
				<div class="corner-all">
					<p class="w40 f-l">
						<label for="dfolio">Folio:</label> <br>
						<input type="text" name="dfolio" value="<?= isset($ticket[0][0]->folio) ? $ticket[0][0]->folio:'';?>"readonly>
					</p>
					<div class="clear"></div>
					<p class="w100">
						<label for="dcliente" class="f-l">*Cliente</label><br>
						<input type="text" name="dcliente" value="<?php echo set_value('dcliente');?>" size="35" id="dcliente" class="f-l" autofocus>
						<input type="hidden" name="hcliente" value="<?php echo set_value('hcliente');?>" id="hcliente">
						
						<textarea name="dcliente_info" id="dcliente_info" class="m10-l" rows="3" cols="66" readonly><?php echo set_value('dcliente_info'); ?></textarea>
						
						<div class="addv">
							<a href="javascript:alerta();" id="btnAddVuelo" class="linksm f-r" style="margin: 10px 0 20px 0;">
							<img src="<?php echo base_url('application/images/privilegios/add.png'); ?>" width="16" height="16"> Agregar vuelos</a>
						</div>
					</p>
					
					<div class="clear"></div>
					
					<table class="tblListados corner-all8" id="tbl_productos">
						<tr class="header btn-gray">
							<td>Cantidad</td>
							<td>Código</td>
							<td>Descripción</td>
							<td>Precio Unitario</td>
							<td>Importe</td>
							<td>Opc</td>
						</tr>
							
						<?php
						if(isset($_POST['dpid_producto'])){
							foreach($_POST['dpid_producto'] as $key => $itm){
								echo '<tr id="trp-'.str_replace('.', '_', $itm).'">
										<td>
											<input type="hidden" name="dpid_producto[]" value="'.$itm.'">
											<input type="hidden" name="dpcantidad[]" value="'.$_POST['dpcantidad'][$key].'">
											<input type="hidden" name="dpprecio_unitario[]" value="'.$_POST['dpprecio_unitario'][$key].'">
											<input type="hidden" name="dpimporte[]" value="'.$_POST['dpimporte'][$key].'" class="dpimporte">
											<input type="hidden" name="dptaza_iva[]" value="'.$_POST['dptaza_iva'][$key].'">
											<input type="hidden" name="dpimporte_iva[]" value="'.$_POST['dpimporte_iva'][$key].'" class="dpimporte_iva">
											
											<input type="hidden" name="dpcodigo[]" value="'.$_POST['dpcodigo'][$key].'">
											<input type="hidden" name="dpnombre[]" value="'.$_POST['dpnombre'][$key].'">
											'.$_POST['dpcantidad'][$key].'</td>
										<td>'.$_POST['dpcodigo'][$key].'</td>
										<td>'.$_POST['dpnombre'][$key].'</td>
										<td>'.String::formatoNumero($_POST['dpprecio_unitario'][$key]).'</td>
										<td>'.String::formatoNumero($_POST['dpimporte'][$key]).'</td>
										<td class="tdsmenu a-c" style="width: 90px;">
											<a href="javascript:void(0);" class="linksm" 
												onclick="quitarProducto(\''.$itm.'\');return false;">
												<img src="'.base_url().'application/images/privilegios/delete.png" width="10" height="10"> Quitar</a>
										</td>
									</tr>';
							}
						} 
						?>
					</table>
					
					<table class="tblListados corner-all8 f-r" style="width:24% !important;margin-right:1%;text-align:center;">
						<tr>
							<td style="text-align:right;">SubTotal</td>
							<td id="ta_subtotal" class="w20 a-r" style="background-color:#ccc;"><?php echo String::formatoNumero(set_value('dtsubtotal', 0)); ?></td>
						</tr>
						<tr>
							<td style="text-align:right;">IVA</td>
							<td id="ta_iva" class="a-r" style="background-color:#ccc;"><?php echo String::formatoNumero(set_value('dtiva', 0)); ?></td>
						</tr>
						<tr>
							<td style="text-align:right;">Total</td>
							<td id="ta_total" class="a-r" style="background-color:#ccc;"><?php echo String::formatoNumero(set_value('dttotal', 0)); ?></td>
						</tr>
					</table>
					
					
					<div class="clear"></div>
				</div>			
		</div>
		
		<div class="frmsec-right w20 f-l">
			<div class="frmbox-r p5-tb corner-right8">
				<label for="dfecha">*Fecha</label> <br>
				<input type="text" name="dfecha" id="dfecha" value="<?php echo set_value('dfecha'); ?>" class="a-c" size="15" readonly>
				
				<p class="w100 f-l">
						<label for="dtipo_pago">*Tipo de Pago</label> <br>
						<select name="dtipo_pago" id="dtipo_pago">
							<option value="credito" <?php echo set_select('dtipo_pago', 'credito'); ?>>Crédito</option>
							<option value="contado" <?php echo set_select('dtipo_pago', 'contado'); ?>>Contado</option>
						</select>
				</p>
				<div class="clear"></div>
			</div>
			<input type="submit" name="enviar" value="Guardar" class="btn-blue corner-all">
		</div>
	</form>
</div>

<div id="container" style="display:none">
	<div id="withIcon">
		<a class="ui-notify-close ui-notify-cross" href="#">x</a>
		<div style="float:left;margin:0 10px 0 0"><img src="#{icon}" alt="warning" width="64" height="64"></div>
		<h1>#{title}</h1>
		<p>#{text}</p>
		<div class="clear"></div>
	</div>
</div>
<!-- Bloque de alertas -->
<?php if(isset($frm_errors)){
	if($frm_errors['msg'] != ''){ 
?>
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

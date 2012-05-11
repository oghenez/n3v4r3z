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
<body class="f-9em" style="font-size:0.9em;">
<div class="w100">
	<div style="width:100%;">
		<a href="javascript:void(0);" style="float:right;" onclick="printt()">
			<img class="hidde" alt="Imprimir" title="Imprimir" src="<?= base_url('application/images/print.png')?>" width="16" height="16">
		</a>
	</div>
	<div class="clear"></div>
	<div class="a-c">
		<img src="<?php echo base_url('application/images/logo.png')?>" width="150" height="60"/><br>
		<span>Fumigaciones Aereas Nevarez</span>
	</div>
	<br><br>
	<div>
		<span class="f-r">Folio:  <?= $info[1]['cliente_info'][0]->folio?></span><br>
		<span class="f-r">Fecha:  <?= $info[1]['cliente_info'][0]->fecha?></span>
		<div class="clear"></div>
		<br>
		<span class="f-b" style="font-weight:bold;">Datos Cliente</span><br>
		<span>Nombre: <?= $info[1]['cliente_info'][0]->nombre_fiscal?></span><br>
		<span>RFC: <?= $info[1]['cliente_info'][0]->rfc?></span><br>
		<span><?= $info[1]['cliente_info'][0]->domicilio?></span>
		<?php if($info[1]['cliente_info'][0]->otros_clientes !== null){?>
				<br><br><span class="f-b" style="font-weight:bold;">Otros Clientes: <?= str_replace('<br>', ', ', $info[1]['cliente_info'][0]->otros_clientes)?></span><br>
		<?php }?>
	</div>
	<br><br>
	<div>
		<div class="w100 a-c f-b" style="font-weight: bold;">D E T A L L E</div><br>
		
		<table class="header w100">
			<tr class="a-c">
				<td>CANT</td>
				<td>DESC</td>
				<td>AVION</td>
				<td>FCHA</td>
				<td>P/U</td>
				<td>IMPORTE</td>
			</tr>
			<?php if(isset($info[1]['vuelos_info'])):
					foreach ($info[1]['vuelos_info'] as $vuelo):?>
						<tr class="a-c f-7em" style="font-size:0.7em;">
							<td><?= $vuelo->vuelos?></td>
							<td><?= $vuelo->nombre?></td>
							<td><?= $vuelo->matricula?></td>
							<td><?= $vuelo->fecha?></td>
							<td><?= String::formatoNumero($vuelo->precio,2)?></td>
							<td><?= String::formatoNumero($vuelo->importe,2)?></td>
						</tr>
			<?php endforeach;endif;?>
		</table>
		<table class="f-r w-24i m-r3 a-c f-7em" style="width: 24% !important; margin-right: 3%; text-align: center; font-size: 0.7em;">
						<tr>
							<td></td>
							<td id="ta_subtotal" class="w20 a-r">----------------</td>
						</tr>
						<tr>
							<td class="a-r" style="text-align:right;">SubTotal</td>
							<td id="ta_subtotal" class="w20 a-r bg-ddd" style="background-color:#ccc;"><?php echo String::formatoNumero($info[1]['cliente_info'][0]->subtotal); ?></td>
						</tr>
						<tr>
							<td class="a-r" style="text-align:right;">IVA</td>
							<td id="ta_iva" class="a-r bg-ddd" style="background-color:#ccc;"><?php echo String::formatoNumero($info[1]['cliente_info'][0]->iva); ?></td>
						</tr>
						<tr>
							<td class="a-r" style="text-align:right;">Total</td>
							<td id="ta_total" class="a-r bg-ddd" style="background-color:#ccc;"><?php echo String::formatoNumero($info[1]['cliente_info'][0]->total); ?></td>
						</tr>
					</table>
	</div>
</div>

</body>
</html>
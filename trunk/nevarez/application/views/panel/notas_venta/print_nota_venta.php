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
			<?php if(isset($info[1]['tickets_info'])):
					foreach ($info[1]['tickets_info'] as $ticket):?>
						<tr class="a-c f-7em" style="font-size:0.7em;">
							<td><?= $ticket->vuelos?></td>
							<td><?= $ticket->nombre?></td>
							<td><?= $ticket->matricula?></td>
							<td><?= $ticket->fecha?></td>
							<td><?= String::formatoNumero($ticket->precio,2)?></td>
							<td><?= String::formatoNumero($ticket->importe,2)?></td>
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
	</div><div class="clear"></div>
	<br><br>
	<div class="f-7em" style="font-size: 0.6em;">
		Debemos y Pagaré incondicionalmente a la orden de __________________ de este lugar de ____________________ Cantidad de _____________________ (______________________) 
		m.n., valor de la mercancía recibida a mi entera satisfacción. Este pagaré es mercantil y está regido por la Ley General de Títulos Y Operaciones de Crédito 
		en su artículo 173 parte final y artículos correlativos por no ser pagaré domiciliado. Si no es pagado antes de su vencimiento causara un interés del ____% mensual.
	</div>
</div>

</body>
</html>
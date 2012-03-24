<?php

class String{
	
	/**
	 * Obtiene las variables get y las prepara para los links
	 * @param unknown_type $quit
	 */
	public static function getVarsLink($quit=array()){
		$vars = '';
		foreach($_GET as $key => $val){
			if(array_search($key, $quit) === false)
				$vars .= '&'.$key.'='.$val;
		}
		
		return substr($vars, 1);
	}
	
	/**
	 * Valida si una cadena es una fecha valida 
	 * y regresa en formato correcto
	 */
	public static function isValidDate($str_fecha, $format='Y-m-d'){
		$fecha = explode('-', $str_fecha);
		if(count($fecha) != 3 && strlen($str_fecha) != 10)
			return false;
		return true;
	}
	
	/**
	 * Limpia una cadena
	 * @param $txt. Texto a ser limpiado
	 * @return String. Texto limpio
	 */
	public static function limpiarTexto($txt, $remove_q=true){
		$ci =& get_instance();
		if(is_array($txt)){
			foreach($txt as $key => $item){ 
				$txt[$key] = addslashes(self::quitComillas(strip_tags(stripslashes(trim($item)))));
				$txt[$key] = $ci->security->xss_clean(preg_replace("/select (.+) from|update (.+) set|delete from|drop table|where (.+)=(.+)/","", $txt[$key]));
			}
			return $txt;
		}else{
			$txt = addslashes(self::quitComillas(strip_tags(stripslashes(trim($txt)))));
			$txt = $ci->security->xss_clean(preg_replace("/select (.+) from|update (.+) set|delete from|drop table|where (.+)=(.+)/","", $txt));
			return $txt;
		}
	}
	
	
	/**
	 * @param $txt. Texto al que se le eliminarÃ¡n las comillas
	 * @return String. Texto sin comillas
	 */
	public static function quitComillas($txt){
		return str_replace("'","’", str_replace('"','”',$txt));
	}
}
?>
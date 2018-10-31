<meta name="robots" content="noindex">

<?php

set_time_limit(120);

if(isset($_FILES['arquivo'])){
	try {
		$arquivo=$_FILES['arquivo'];
		$lang = strip_tags($_POST['idioma']);
		$ext = explode('.', $arquivo['name']);
		if(strtolower(end($ext))=='php') {
			$name_temp = time().'.php';
			if(!file_exists('./temp/')) mkdir('temp');
			move_uploaded_file($arquivo['tmp_name'], './temp/'.$name_temp);
			if(strpos(file_get_contents('./temp/'.$name_temp), '$_LANG') !== false){
				include './temp/'.$name_temp;
				$lang_orig = $_LANG;
				$file_temp = "<?php\n\n";
				foreach($lang_orig as $k=>$v){
					if(is_array($v)){
						foreach($v as $c=>$t){
							if(is_array($t)) {
								foreach($t as $m=>$n){
									$file_temp.='$_LANG[\''.$k.'\'][\''.$c.'\'][';
									$file_temp.=is_numeric($m)?$m:"['{$m}']";
									$file_temp.='] = \''.translate($lang,$n).'\';'."\n";
								}
							} else {
								$file_temp.='$_LANG[\''.$k.'\'][\''.$c.'\'] = \''.translate($lang,$t).'\';'."\n";
							}
						}
					} else {
						$file_temp.='$_LANG[\''.$k.'\'] = \''.translate($lang,$v).'\';'."\n";
					}
				}
				$file_temp.= "\n?>";
				$new_file_name = './temp/'.$lang.'.php';
				file_put_contents($new_file_name, $file_temp);
				unlink('./temp/'.$name_temp);
				header('Content-Type: application/octet-stream');
				header("Content-Transfer-Encoding: Binary"); 
				header("Content-disposition: attachment; filename=\"" . basename($new_file_name) . "\""); 
				readfile($new_file_name);
				unlink($new_file_name);
				unset($_POST);
				unset($_FILES);
				die;
			} else {
				unlink('./temp/'.$name_temp);
				throw new Exception('Conteúdo do arquivo inválido.');
			}
		} else {
			throw new Exception('Extensão do arquivo inválida.');
		}
	} catch (Exception $e){
		echo 'Erro: '.$e->getMessage();
	}
}

function translate($lang='en',$text){
	$return='';
	$url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=pt&tl={$lang}&dt=t&q=".urlencode($text)."";
	$content = file_get_contents($url);
	$json = json_decode($content,true);
	$translates = $json[0];
	for($i=0;$i<count($translates);$i++) $return.=$translates[$i][0];
	return $return;
}

?>

<?php if(!isset($_FILES['arquivo'])){ ?>
<form action="" method="post" enctype="multipart/form-data">
	<input type="file" name="arquivo">
	->
	<select name="idioma">
		<option value="pt">Português</option>
		<option value="en">Inglês</option>
		<option value="es">Espanhol</option>
	</select>
	<button>Traduzir</button>

	<fieldset style="width: 460px; margin-top: 40px;">
		<legend>Conselho para vida:</legend>
		Não use repetidas vezes em um curto período de tempo, senão estraga.
	</fieldset>
</form>
<?php } ?>


<?php

include_once( "config.php" );

/*
*+北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北
*+
*+    Class arquivoTxt
*+
*+北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北北
*/
class arquivoTxt {
	var $filename;
	var $tipo;
	var $handle;
	//
	// construtor
	function arquivoTxt() {
		$this->filename = "";
		$this->tipo = "";
		$this->handle = "";
	}
	//
	// cria arquivo
	function cria( $filename,$tipo="w+" ) {
		$this->filename = $filename;
		$this->tipo = $tipo;
		if (!$this->handle = fopen($this->filename, $this->tipo)) {
			$this->erro( "Erro criando arquivo" );
		}
	  fclose($this->handle);
	}
	//
	// abre o arquivo
	function abre( $tipo="a" ) {
		$this->tipo = $tipo;
		if (!$this->handle = fopen($this->filename, $this->tipo)) {
			$this->erro( "Erro abrindo arquivo" );
		}
  }
	//
  // escrevendo.
	function grava( $texto="" ) {
	  if (!fwrite($this->handle, $texto)) {
			$this->erro( "Erro escrevendo arquivo" );
		}
	}
	//
  // fechando.
	function fecha() {
	  fclose($this->handle);
	}
	//
  // erro.
	function erro($erro) {
	  mensagem( $erro );
		die();
	}
}

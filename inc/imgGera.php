<?php

/* * ****************************************************
 * *
 * *	Nome do Arquivo: imgGera.php
 * *	Data de Criação: 01/05/2007
 * *	Autor: Thiago Felipe Festa - thiagofesta@gmail.com
 * *	Última alteração:
 * *	Modificado por:
 * *
 * **************************************************** */

// Incluo a classe que gera a imagem.
require_once ("imagem.class.php");

// Instâncio a imagem
$imagem = new Imagem;
$imagem->geraImagem();

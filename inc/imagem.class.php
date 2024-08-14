<?php

/******************************************************
**
**	Nome do Arquivo: imagem.class.php
**	Data de Criação: 01/05/2007
**	Autor: Thiago Felipe Festa - thiagofesta@gmail.com
**	Última alteração:
**	Modificado por:
**
******************************************************/

/*****************************************
**	Classe		-	Imagem
**	Descrição	-	Cria a imagem
******************************************/


// Inicio a sessão, pois estamos trabalhando com sessões.
session_start();

class Imagem
{
    var $carac;

    function geraImagem()
    {
        // Seleciona uma imagem que está na pasta bg/ com o nome 0.jpg à 9.jpg,
        // está imagem que vai ser o fundo da nossa imagem de segurança.
        $fundo = "bg/";
        $fundo .= rand(0,9);
        $fundo .= ".jpg";

        // Cria a imagem.
        $imagem = imagecreatefromjpeg($fundo);

        // seta o $this->carac que é a sessão carac.
        $this->carac = $_SESSION["carac"];

        // percorre o array carac, e traz os valores.
        foreach($this->carac as $linha) 
        {
            // Aqui crio a cor de cada caractere, RGB.
            $cor = imagecolorallocate($imagem, $linha["corR"], $linha["corG"], $linha["corB"]);
            
            // desenho o lugar dos caracteres de acordo com as posições x e y.
            //$font = imageloadfont('bg/fonts/arial.gdf'); 
            //imagestring($imagem, $font, $linha["x"], $linha["y"], $linha["c"], $cor);
            imagestring($imagem, $linha["tam"], $linha["x"], $linha["y"], $linha["c"], $cor);
        }

        // ele informa que isso é um arquivo PNG
        header("Content-type: image/png");

        // cria a imagem PNG
        imagepng($imagem);
    }
}

<?php

/* * ****************************************************
 * *
 * *	Nome do Arquivo: imgSet.php
 * *	Data de Criação: 01/05/2007
 * *	Autor: Thiago Felipe Festa - thiagofesta@gmail.com
 * *	Última alteração:
 * *	Modificado por:
 * *
 * **************************************************** */

// Destruo o $carac, para não ficar sobrepondo varias vezes.
unset($carac);

// Crio uma imagem com 3 caracteres
for ($i = 0; $i < 3; $i++)
{
    // Seleciona de 0 à 2, onde 0 = letra maiúscula, 1 = minúscula e 2 = número.
    $tipo = rand(0, 4);
    switch ($tipo)
    {
        // se pegou 0 ele cria uma letra maiúscula de A à Z.
        // com um porém, o chr(rand(65,90)), a função chr retorna um caractere específico, e o rand seleciona aleatório.
        // o motivo de estar rand(56,90), pois temos que usar de acordo com a tabela ASCII e o A = 65 e o Z = 90.
        case 0 :
        case 1 :
        case 2 : $str = chr(rand(65, 78));
            break;
        case 3 : $str = chr(rand(80, 90));
            break;
        // mesma coisa que antes, mais aqui é minúsculo.
        //case 2 : $str = chr(rand(97,110)); break;
        //case 3 : $str = chr(rand(112,122)); break;
        // mesma coisa que antes só que aqui são números.
        case 4 : $str = chr(rand(49, 57));
            break;
        // caso ocorra algun erro no rand tipo ele para por aqui.
        default : break;
    }

    // Gera um tamanho para a fonte de 3 à 5.
    $tamanho = rand(4, 5);

    // Seleciona as cor RGB, menos muito clara, pois o fundo é branco, por isso de 0 à 200.
    $sel_corR = rand(0, 150); //rand(0,200);
    $sel_corG = rand(0, 150); //rand(0,200);
    $sel_corB = rand(0, 150); //rand(0,200);
    // Joga os caracteres em um determinado lugar da imagem, x e y, sendo x sempre ele mais ele, pra não perder a ordem.
    // Nome que começa em 10 e termina em 30, pois temos 6 caracteres, e nossa imagem tem 180px,
    // por isso que vai ser de 30 em 30. e 10 para não ficar um caractere em cima do outro.
    $x        += rand(10, 20);
    // o Y vai de 0 a 30, não a 50, pois pode cortar pois nossa imagem é de 50 px de altura.
    $y        = rand(0, 20);

    // Gera um array carac, com os dados que é o caractere, a cor do caractere, a posição x e y.
    $carac[] = array("c" => $str, "tam" => $tamanho, "corR" => $sel_corR, "corG" => $sel_corG, "corB" => $sel_corB, "x" => $x, "y" => $y);

    // crio a variavel para usar e criar a sessão logo abaixo com os caracteres que forão criados.
    $auth .= $str;
}

// Crio a sessão carac, que é igual ao array dos caracteres, que utiliza para montar a imagem la na classe.
$_SESSION["carac"] = $carac;

// Crio a sessão autenticaIMG com os caracteres que estão na imagem, que utilizo para verificar se o cara digitou certo.
$_SESSION["autenticaIMG"] = $auth;

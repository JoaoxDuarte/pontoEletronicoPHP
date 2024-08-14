<?php
/**
* @info MyCripty - classe para criptografar e reverter a criptografia 
*       de forma fácil, simples e segura utilizando PHP.
*
* @author Hudolf Jorge Hess
* @version 1.0b
* @link www.hudolfhess.com
*/
include_once( '../config.php' );

class MyCripty {

    /**
    * @var int
    */
    public $chave = 127;

    /**
    * @var string
    */
    public $add_text = "SISREF-2009-INSS-GOV-BR";

    /**
    * @param string Palavra
    * @return string
    */
    function enc($word){
        $this->add_text = md5(sha1($this->add_text));
       $word .= $this->add_text;
       $s = strlen($word)+1;
       $nw = "";
       $n = $this->chave;
       for ($x = 1; $x < $s; $x++){
           $m = $x*$n;
           if ($m > $s){
               $nindex = $m % $s;
           }
           else if ($m < $s){
               $nindex = $m;
           }
           if ($m % $s == 0){
               $nindex = $x;
           }
           $nw = $nw.$word[$nindex-1];
       }
       return $nw;
    }

    /**
    * @param string Palavra
    * @return string
    */
    function dec($word){
       $s = strlen($word)+1;
       $nw = "";
       $n = $this->chave;
       for ($y = 1; $y < $s; $y++){
           $m = $y*$n;
           if ($m % $s == 1){
               $n = $y;
               break;
           }
       }
       for ($x = 1; $x < $s; $x++){
           $m = $x*$n;
           if ($m > $s){
               $nindex = $m % $s;
           }
           else if ($m < $s){
               $nindex = $m;
           }
           if ($m % $s == 0){
               $nindex = $x;
           }
           $nw = $nw.$word[$nindex-1];
       }
       $t = strlen($nw) - strlen($this->add_text);
       return substr($nw, 0, $t);
    }

}

/*
//<?php
//require "inc/MyCripty.class.php";

//Instanciando classe
$mc = new MyCripty();

//Definindo um número chave, importante colocar sempre um número PRIMO maior que 3
//$mc->chave = 97;

//Texto chave para dificultar a decriptografia, pois além de precisar da chave, precisa também do texto chave.
##$mc -> add_text = md5(sha1("texto chave aqui"));
$valor = "201130910343";

//Texto a ser criptogrfado
$enc = $mc->enc( $valor );

//Revertendo o processo da criptografia
$dec = $mc->dec($enc);

//Saída HTML
echo "<p>Criptografia: $enc</p>
        <p>Reversa: $dec</p>";
*/
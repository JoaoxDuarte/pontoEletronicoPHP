<?php

include_once("config.php");

$homeLink = "javascript:location.replace('principal_abertura.php');";

if ($_SESSION['sModuloPrincipalAcionado'] == 'sogp')
{
    $html2 = "<ul class='nav navbar-nav navbar-right'>
                      <li><a href=\"javascript:location.replace('finaliza2.php?modulo=sogp');\"><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
                  </ul>";
}
else if ($_SESSION['sModuloPrincipalAcionado'] == 'chefia')
{
    $html2 = "<ul class='nav navbar-nav navbar-right'>
                      <li><a href=\"javascript:location.replace('finaliza2.php?modulo=chefia');\"><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
                  </ul>";
}

$html3 = "<ul class='nav navbar-nav navbar-right'>
                <li><a href='./finaliza.php'><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
            </ul>";

$fonte_origem = pathinfo($_SERVER['PHP_SELF']);

$sgop = true;

if ($_SESSION['sModuloPrincipalAcionado'] == 'entrada')
{
    $homeLink = "entrada.php";
    $sgop     = false;
}

if ($sgop)
{
    $html_header = $html2;
}
else if (!$sgop && $_SESSION['logado'] == 'SIM')
{
    if ((strpos($_SERVER['PHP_SELF'], 'entrada.php') === false) || ($_SESSION['sModuloPrincipalAcionado'] == 'entrada'))
    {
        $html_header = $html3;
    }
}
else
{
    $html_header = '';
}
?>

<header>
    <nav class="navbar-app navbar-default navbar-fixed-top" style="background-color:<?= ($_SESSION['sHOrigem_1'] === 'entrada.php' || $_SESSION['sModuloPrincipalAcionado'] === 'entrada' ? '#004080' : '#0c691c' ); ?>">
        <div class="row">
            <div class="col-sm-2">
                <!-- <div id="tempo_decorridoX" class="text-left" style="color:white;padding-top:30px;"></div> -->
            </div>
            <div class="col-sm-2">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="<?= $homeLink; ?>">
                            <span class="texto-sistema">SISREF</span>
                            <span class="subtitulo-texto">Sistema de Registro Eletrônico<br>de Frequência</span>
                        </a>
                    </div>

                    <?php

                    if ($fonte_origem['basename'] === 'entrada1.php' || ($fonte_origem['basename'] !== 'entrada.php' && $_SESSION['sHOrigem_1'] === 'entrada.php'))
                    {
                        echo $html3;
                    }
                    else if ($_SESSION['sModuloPrincipalAcionado'] != 'sogp' && $_SESSION['sModuloPrincipalAcionado'] != 'chefia')
                    {
                        echo $html_header;
                    }
                    
                    ?>

                    <?= $setHTMLCaminho; ?>

                </div>
            </div>
        </div>

        <?php
        if ($_SERVER['PHP_SELF'] != '/sisref/trocasenha_rh.php')
        {
            $height = 100;

            if (($_SESSION['sModuloPrincipalAcionado'] != 'entrada' && $_SESSION['sHOrigem_1'] != 'entrada.php' && $_SESSION['sRH'] == 'S') || $_SESSION['sSenhaI'] == 'S')
            {
                include_once("html/menu-sogp.php");
            }
            else if ($_SESSION['sModuloPrincipalAcionado'] != 'entrada' && $_SESSION['sHOrigem_1'] != 'entrada.php' && $_SESSION['sAPS'] == 'S')
            {
                include_once("html/menu-chefia.php");
            }
            else if ($_SESSION['sModuloPrincipalAcionado'] == 'entrada' && $_SESSION['sHOrigem_1'] == 'entrada.php')
            {
                $height = 50;
            }
        }
        else
        {
            $height = 50;
        }
        ?>

    </nav>

    <div style="margin-top:<?= $height; ?>px;"></div>
    
</header>

<div id="mensagem_do_sistema" style="width:950px;padding:40px 0px 0px 0px;margin:0 auto;display:none;"></div>

<?php

include_once( "config.php" );

/**
 * @class menu_app
 * @info Monta o menu do aplicativo
 *
 * @Arquivo   - menu_app_class.php
 *
 */

class menu_app
{
    /*
    * Atributos
    */
    public $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
    }

    public function showMenu()
    {
        //$this->PaginaInicio();
        $this->menuInicio();
        $this->menuExibir( $this->menu );
        $this->menuFim();
        //$this->PaginaFim();
    }

    /*
     * @info Opçao principais do Menu
     *
     * @param array $opcao Opções do menu
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function menuExibir( $opcao )
    {
        if (count($opcao) >= 1)
        {
            foreach ($opcao as $key => $subopcao)
            {
                if (substr($key,0,10) == "<glyphicon")
                {
                    $itens = explode('>',$key);
                    $key = $itens[1];
                    $glyphicon = strtr($itens[0],array('<' => '','>' => ''));
                    $icone = "<span class='glyphicon " . $glyphicon . "'></span>&nbsp;&nbsp; ";
                }

                ?>
                <!--
                    <?= mb_convert_case($key, MB_CASE_UPPER, "UTF-8"); ?>
                -->
                <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= $icone . $key; ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                    <?php

                    foreach($subopcao as $subkey => $link)
                    {
                        if (is_array($link))
                        {
                            $this->menuOpcao( $subkey, $link );
                        }
                        else if ($link == '-')
                        {
                            $this->menuSeparador();
                        }
                        else
                        {
                            $this->menuSubOpcao( [$subkey, $link] );
                        }
                    }

                    ?>
                    </ul>
                </i>
                <?php

                $icone ="";
            }
        }
    }


    /*
     * @info Opçao do Menu
     *
     * @param string $subkey Opção grupo
     * @param array $opcao Opções do menu
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function menuOpcao( $subkey, $opcao )
    {
        if (substr($subkey,0,10) == "<glyphicon")
        {
            $itens = explode('>',$subkey);
            $subkey = $itens[1];
            $glyphicon = strtr($itens[0],array('<' => '','>' => ''));
            $icone = "<span class='glyphicon " . $glyphicon . "'></span>&nbsp;&nbsp; ";
        }

        ?>
        <li class="dropdown dropdown-submenu"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= $icone . $subkey; ?></a>
            <ul class="dropdown-menu">
            <?php

            foreach($opcao as $subkey => $link)
            {
                if (is_array($link))
                {
                    $this->menuOpcao( $subkey, $link );
                }
                else if ($link == '-')
                {
                    $this->menuSeparador();
                }
                else
                {
                    $this->menuSubOpcao( [$subkey, $link] );
                }
            }

            ?>
            </ul>
        </i>
        <?php
    }


    /*
     * @info Sub opção do Menu
     *
     * @param void
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function menuSubOpcao( $opcao )
    {
        if (substr($opcao[0],0,10) == "<glyphicon")
        {
            $itens = explode('>',$opcao[0]);
            $opcao[0] = $itens[1];
            $glyphicon = strtr($itens[0],array('<' => '','>' => ''));
            $icone = "<span class='glyphicon " . $glyphicon . "'></span>&nbsp;&nbsp; ";
        }

        ?>
        <li><a href="#" data-load-remote2="<?= $opcao[1]; ?>"><?= $icone . $opcao[0]; ?></a></li>
        <?php
    }


    /*
     * @info Separador do Menu
     *
     * @param void
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function menuSeparador()
    {
        ?>
        <li class="divider"></li>
        <?php
    }


    /*
     * @info Início da página
     *
     * @param void
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function PaginaInicio()
    {
        $bgcolor = ($_SESSION["sRH"] == "S" || $_SESSION["sAPS"] == "S" ? "#0f4098" : "#0c691c");

        include_once( 'html/html-base.php');

        ?>
        <div class="container">
            <div class="row align-vertical" id="login2">

        <?php
    }


    /*
     * @info Rodape da página
     *
     * @param void
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function PaginaFim()
    {
        $bgcolor = ($_SESSION["sRH"] == "S" || $_SESSION["sAPS"] == "S" ? "#0f4098" : "#0c691c");

        ?>
                </div>
            </div>
        </div>
        <?php

        include_once( 'html/footer.php');
    }


    /*
     * @info Abre NAV (menu)
     *
     * @param void
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function menuInicio()
    {
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

        $bgcolor =  ($_SESSION['sHOrigem_1'] === 'entrada.php' || $_SESSION['sModuloPrincipalAcionado'] === 'entrada' ? '#004080' : '#0c691c' );

        ?>
        <!--
        <header>
            <nav class="navbar-app navbar-default navbar-fixed-top" style="background-color:<?= $bgcolor; ?>">
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
                            <span class="subtitulo-texto">Sistema de Registro Eletrônico<br>de Frequência</span></a>
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
        -->
                <script>

                $(document).ready(function ()
                {
                    $('[data-load-remote2]').on('click',function(e) {
                        var oForm = $("#form_menu_nav1");
                        var $this = $(this);
                        var remote = "javascript:window.location.replace('"+$this.data('load-remote2')+"');";

                        e.preventDefault();

                        console.log(remote);

                        oForm.attr("onSubmit", "javascript:return true;");
                        oForm.attr("action", remote);
                        oForm.submit();
                    });
                });
                </script>

                <!--
                      MENU PRINCIPAL - SOGP
                -->
                <form method="POST" id="form_menu_nav1" name="form_menu_nav1" action="#" onSubmit="javascript:return false;">
                    <div class="collapse navbar-collapse" id="navbar-collapse-1">
                        <div class="row">
                            <!-- incluir as tags abaixo -->
                            <div class="col-sm-2"></div>
                            <div class="col-sm-8" style="background-color: black;">
                                <!-- fim do incluir as tags -->

                                <ul class="nav navbar-nav">
        <?php
    }


    /*
     * @info Fecha NAV (menu)
     *
     * @param void
     * @return HTML
     *
     * @authpr Edinalvo Rosa
     */
    public function menuFim()
    {
        ?>
                                </ul>

                                <ul class='nav navbar-nav navbar-right'>
                                    <li><a href="#" data-load-remote2="finaliza2.php?modulo=sogp"><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
                                </ul>

                            </div>

                            <!-- incluir as tags abaixo -->
                            <div class="col-sm-2"></div>
                        </div>
                        <!-- fim do incluir as tags -->
                    </div>
                </form>
        </header>

        <div id="mensagem_do_sistema" style="width:950px;padding:40px 0px 0px 0px;margin:0 auto;display:none;"></div>
        <?php
    }
}

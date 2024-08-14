<?php

include_once("config.php");

?>
<script>
$(document).ready(function ()
{
    $('[data-load-remote3]').on('click',function(e) {
        var oForm = $("#form_menu_nav3");
        var $this = $(this);
        var remote = "javascript:window.location.replace('"+$this.data('load-remote3')+"');";

        e.preventDefault();

        console.log(remote);

        oForm.attr("onSubmit", "javascript:return true;");
        oForm.attr("action", remote);
        oForm.submit();
    });
});
</script>

<!--
      MENU PRINCIPAL - CHEFIA
-->
<form method="POST" id="form_menu_nav3" name="form_menu_nav3" action="#" onSubmit="javascript:return false;">
    <div class="collapse navbar-collapse" id="navbar-collapse-1">
        <div class="row">
            <!-- incluir as tags abaixo -->
            <div class="col-sm-2"></div>
            <!-- <div class="col-sm-7" style="background-color: #04310e;"> -->
            <div class="col-sm-7" style="background-color: black;">
                <!-- fim do incluir as tags -->

                <ul class="nav navbar-nav">
                    <!--
                          FREQUÊNCIA
                    -->
                    <li class="dropdown">
                        <a href="javascript:void(0);"
                           class="dropdown-toggle"
                           data-toggle="dropdown">Frequência <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="frequencia_acompanhar_entra.php">
                                    Acompanhar
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="frequencia_homologar_entra.php">
                                    Homologar
                                </a>
                            </li>
                            <li class="divider"></li>
                            <!--
                            <li class="dropdown dropdown-submenu">
                                <a href="javascript:void(0);"
                                   class="dropdown-toggle"
                                   data-toggle="dropdown">Plantões
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="plantoes_configurar.php">
                                            Configurar
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="plantoes_servidores.php">
                                            Plantonistas
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            -->
                            <li class="dropdown dropdown-submenu">
                                <a href="javascript:void(0);"
                                   class="dropdown-toggle"
                                   data-toggle="dropdown">Banco de Horas
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="autorizacoes_acumulos.php">
                                            Acúmulo
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="autorizacoes_usufruto.php">
                                            Usufruto
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="autorizacao_trabalho_dia_nao_util_entra.php">
                                    Autorização de Trabalho
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="subsfuncinf.php">
                                    Efetivar Substituição
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu">
                                <a href="javascript:void(0);"
                                   class="dropdown-toggle"
                                   data-toggle="dropdown">Visualizar
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="pontoser.php?cmd=1">
                                            Sua Folha de Frequência
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="veponto.php">
                                            Consulta Frequência
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"
                                           data-load-remote3="entrada9_individual.php?saldo=1">
                                            Consulta Extrato Frequência
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>


                    <!--
                          TABELAS
                    -->
                    <li class="dropdown">
                        <a href="javascript:void(0);"
                           class="dropdown-toggle"
                           data-toggle="dropdown">Tabelas <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="tabferiados.php">
                                    Feriados
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!--
                          UTILITÁRIOS
                    -->
                    <li class="dropdown">
                        <a href="javascript:void(0);"
                           class="dropdown-toggle"
                           data-toggle="dropdown">Utilitários <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="autorizacao_ip_servidor.php">
                                    Autorização de IP por servidor
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="reiniciar_senhas.php">
                                    Reiniciar Senhas
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="trocasenha.php">
                                    Trocar Sua Senha
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="javascript:void(0);"
                                   data-load-remote3="pesquisa_ip.php">
                                    Identificar IP
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>

                <ul class='nav navbar-nav navbar-right'>
                    <li>
                        <a href="javascript:void(0);"
                           data-load-remote3="finaliza2.php?modulo=chefia">
                            <span class='glyphicon glyphicon-log-out'></span> Sair
                        </a>
                    </li>
                </ul>
            </div>
            <!-- incluir as tags abaixo -->
            <div class="col-sm-2"></div>
        </div>
        <!-- fim do incluir as tags -->
    </div>
</form>

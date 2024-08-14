<?php

include_once("config.php");

?>
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
                    <!--
                          CADASTRO
                    -->
                    <li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Cadastro <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <!--
                                  CADASTRO - FUNCIONAL
                            -->
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Funcional</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="cadastro_consulta.php">Consultar</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="cadastro_inclusao.php">Incluir</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="cadastro_alteracao.php">Alterar</a></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Excluir</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="cadastro_exclusao.php">Efetivar Exclusão</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="cadastro_exclusao_cancela.php">Cancelar Exclusão</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <!--
                                  CADASTRO - GERENCIAL
                            -->
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Gerencial</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="incfuncserv.php">Ocupar Funções</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="excfuncserv.php">Vagar Funções</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="subsfuncinf.php">Efetivar Substituição</a></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Delegar Atribuição</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="delegacao.php?modo=10">Registrar Delegação</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="delegacao.php?modo=9">Cancelar Delegação</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Movimentação</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="movserv.php">Alterar Lotação</a></li>
                                    <!-- <li class="disabled"><a href="javascript:void(0);" data-load-remote2="localserv.php">Alterar Localização</a></li> -->
                                    <li class="divider"></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Mudar UPAG</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="liberupag.php">Liberar</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="canliberupag.php">Cancelar Liberação</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="recupag.php">Receber</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!--
                          FREQUÃŠNCIA
                    -->
                    <li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Frequência <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <!--
                            <li><a href="javascript:void(0);" data-load-remote2="regfreqger_entra.php">Gerencial</a></li>
                            <li class="divider"></li>
                            -->
                            <li><a href="javascript:void(0);" data-load-remote2="frequencia_acompanhar_entra.php">Acompanhar</a></li>
                            <li><a href="javascript:void(0);" data-load-remote2="frequencia_homologar_entra.php">Homologar</a></li>
                            <!--
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Plantões</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="plantoes_configurar.php">Configurar</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="plantoes_servidores.php">Plantonistas</a></li>
                                </ul>
                            </li>
                            -->
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Banco de Horas</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="autorizacoes_acumulos.php">Acúmulo</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="autorizacoes_usufruto.php">Usufruto</a></li>
                                </ul>
                            </li>
                            <li><a href="javascript:void(0);" data-load-remote2="autorizacao_trabalho_dia_nao_util_entra.php">Autorização de Trabalho</a></li>
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Visualizar</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="pontoser.php?cmd=1">Sua Folha de Frequência</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="ficha_de_frequencia_resumo_anual.php">Ficha de Frequência - Resumo Anual</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="veponto.php">Consulta Frequência</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="entrada9_individual.php?saldo=1">Consulta Extrato Frequência</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="entrada9_individual.php?saldo=2">Consulta Compensações - Matrícula (Recessos)</a></li>
                                    <!--
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="entrada9_individual.php?saldo=2">Consulta Compensações - Matrícula (Recessos /<br>Copa do Mundo 2014 / Olimpíadas Rio 2016)</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_copa2014.php">Copa do Mundo 2014 - Consulta Compensações de Todos</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_olimpiadas2016_nao_compensado.php">Olimpíadas Rio 2016 - Consulta Compensações de Todos</a></li>
                                    <li class="disabled"><a href="javascript:void(0);" data-load-remote2="vesolhor.php">Alteração de horário</a></li>
                                    -->
                                </ul>
                            </li>
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">RH Atualizar</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="frequencia_rh_mes_corrente.php"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> Mês Corrente</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="frequencia_rh_mes_homologacao.php"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> Mês em Homologação</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="hora_extra_autorizacao.php">Serviços Extraordinários (autorização)</a></li>
                                    <!--
                                    <li><a href="javascript:void(0);" data-load-remote2="envio_siape.php">Envio para o SIAPE</a></li>
                                    -->
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="historico_frequencia.php"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> Histórico</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="frequencia_verificar_homologados.php">Verificar Homologações</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="gestao_liberar_homologacao.php">Liberar para Homologação</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="tabciclos_banco_horas.php">Ciclos de Banco de Horas</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!--
                          RELATÓRIOS
                    -->
                    <li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Relatórios <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Frequência</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_por_ocorrencia.php">Por Ocorrências</a></li>
                                    <!-- <li><a href="javascript:void(0);" data-load-remote2="relfrqsetorp2.php">Setores Pendentes</a></li> -->
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_frequencia_homologacoes.php">Consultar Homologações</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_cedidos_descentralizados.php">Cedidos e Descentralizados</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_requisitados_descentralizados.php">Requisitados e Descentralizados</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Para Comando</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_ocorrencia_nao_compensada_competencia.php">Cobrança</a></li>
                                            <!-- <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_comando_siapecad_competencia.php">Comando SIAPECAD</a></li> -->
                                            <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_recesso_nao_compensado_competencia.php">Recesso</a></li>
                                            <!--
                                            <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_copa2014_nao_compensado.php">Copa do Mundo 2014</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_olimpiadas2016_nao_compensado.php">Olimpíadas Rio 2016</a></li>
                                            -->
                                            <li class="divider"></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_comando_gerar_txt.php">Gerar TXT (COVID-19) para SIAPENET</a></li>
                                        </ul>
                                    </li>
                                    <!--
                                    <li class="divider"></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Insconsistências</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="rlsdiazero.php">Dias Zerados</a></li>
                                        </ul>
                                    </li>
                                    -->
                                </ul>
                            </li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Gerencial</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_perfil_rh_usuarios.php">Perfis de Usuários RH</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="ocupfuncserv.php">Ocupantes de Funções</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="histfuncserv.php">Histórico de Funções</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="histfuncserv2.php">Histórico de Ocupantes</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Substituições</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_substituicoes.php">Substituições na UPAG</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="pessubs.php">Substituições do Servidor</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Movimentação</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="sisref_relatorio_consulta_movimentacao_servidor.php">Consulta Histórico</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="pesmov.php">Consulta Movimentação</a></li>
                                </ul>
                            </li>
                            <li class="divider"></li>
                            <li><a href="javascript:void(0);" data-load-remote2="hora_extra_relatorio_execucao.php">Serviços Extraordinários - Execução</a></li>
                            <!--
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Saldo Paralisações</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_greve_saldo_horas_todos.php?modo=0">Todas as Carreiras</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_greve_saldo_horas_todos.php?modo=1">Seguro Social, e Outras (Exceto Perícia Médica)</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_greve_saldo_horas_todos.php?modo=4">Perícia Médica</a></li>
                                </ul>
                            </li>
                                    -->
                            <!--
                            <li class="divider"></li>
                            <li><a href="javascript:void(0);" data-load-remote2="javascript:replaceLink('relatorio_situacao_reat.php')">Situação Unidades REAT</a></li>
                            -->
                        </ul>
                    </li>

                    <!--
                          TABELAS
                    -->
                    <li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Tabelas <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:void(0);" data-load-remote2="tabcargo.php">Cargos</a></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Escalas</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="tabescalas.php">Criar Escalas</a></li>
                                </ul>
                            </li>
                            <li><a href="javascript:void(0);" data-load-remote2="tabfuncao.php">Funções</a></li>
                            <li><a href="javascript:void(0);" data-load-remote2="tabferiados.php">Feriados</a></li>
                            <li><a href="javascript:void(0);" data-load-remote2="tabisencao_ponto.php">Isenção de Ponto</a></li>
                            <li><a href="javascript:void(0);" data-load-remote2="tablota.php">Setores</a></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Ocorrências</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="tabela_ocorrencia_de_frequencia_visualizar.php">Consultar / Alterar</a></li>
                                </ul>
                            </li>
                            <li><a href="#" data-load-remote2="ponto_facultativo.php">Ponto Facultativo</a></li>
                            <!--
                            <li><a href="javascript:void(0);" data-load-remote2="tabocorrencia.php">Ocorrências</a></li>
                            -->
                        </ul>
                    </li>

                    <!--
                          UTILITÃ?RIOS
                    -->
                    <li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Utilitários <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            
                            <?php if ($_SESSION['sAPS'] === 'S'): ?>
                            
                            <li>
                                <a href="javascript:void(0);" data-load-remote2="autorizacao_ip_servidor.php">Autorização de IP por servidor</a>
                            </li>
                            
                            <?php endif; ?>
                            
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Usuários</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="usuario_lista.php">Alterar Permissões de Usuário</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="reiniciar1.php">Reiniciar Senha de Usuário</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="trocasenha.php">Trocar Sua Senha</a></li>
                                </ul>
                            </li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Auditoria</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="perrellog.php">Operações de Usuário</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="pesquisa_ip.php">Identificar IP</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="gestao_veponto.php">Visualizar Frequência (Quem Registrou)</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Históricos (Log&CloseCurlyQuote;s)</a>
                                        <ul class="dropdown-menu">
                                            <!-- <li><a href="javascript:void(0);" data-load-remote2="tabhisthomologados_logs.php">Homologados</a></li> -->
                                            <li><a href="javascript:void(0);" data-load-remote2="tabhistponto_logs.php">Frequência (ponto)</a></li>
                                            <!-- <li><a href="javascript:void(0);" data-load-remote2="tabhistcadastro_logs.php">Cadastro</a></li> -->
                                            <!-- <li><a href="javascript:void(0);" data-load-remote2="tabhistfuncoes_logs.php">Funções</a></li> -->
                                        </ul>
                                    </li>
                                    <!--
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="pesilegal.php">Operações Não Autorizadas</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown dropdown-submenu">
                                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Registros Alterados</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0);" data-load-remote2="veregalterado.php">Alterações de Frequência</a></li>
                                            <li><a href="javascript:void(0);" data-load-remote2="vecadalt.php">Alterações de Cadastro</a></li>
                                        </ul>
                                    </li>
                                    -->
                                </ul>
                            </li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Gestores</a>
                                <ul class="dropdown-menu">
                                    <!--
                                    <li><a href="javascript:void(0);" data-load-remote2="gestao_libera_homologacao.php">Liberar Homologação</a></li>
                                    -->
                                    <li><a href="javascript:void(0);" data-load-remote2="tabvalida.php">Prazos</a></li>
                                </ul>
                            </li>
                            <li class="divider"></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Manutenção</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="autorizacao_ip.php">Autorização por IP</a></li>
                                    <!--
                                    <li><a href="javascript:void(0);" data-load-remote2="utilitarios_acesso_usuario.php">Corrigir Acesso do Usuário</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="utilitarios_trocasenha_usuario.php">Reinicializar Senha do Usuário</a></li>
                                    -->
                                    <li class="divider"></li>
                                    <!-- <li><a href="javascript:void(0);" data-load-remote2="utilitarios_correcao_matricula.php">Corrigir Matrícula</a></li> -->
                                    <!-- <li><a href="javascript:void(0);" data-load-remote2="utilitarios_ajuste_jornada_historico.php">Ajustar Histórico de Jornada</a></li> -->
                                    <li><a href="javascript:void(0);" data-load-remote2="utilitarios_corrigir_substituicao.php">Corrigir Substituição</a></li>
                                </ul>
                            </li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Configurações</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="gerais_lista.php">Gerais</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="host_autorizado_lista.php">Host Autorizado</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="configuracoes_sigac.php">SIGAC</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="configuracoes_limites_hora_extra.php">Serviços Extraordinários (limites)</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="configuracao_suporte_lista.php">Suporte</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!--
                          GESTÃO ESTRATÉGICA
                    -->
                    <li class="dropdown"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Gestão Estratégica <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:void(0);" data-load-remote2="veponto.php">Consulta Frequência</a></li>
                            <li class="dropdown dropdown-submenu"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">Paralisações/Faltas</a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_paralisacoes_quadro.php?modo=1">Por Dia</a></li>
                                    <li><a href="javascript:void(0);" data-load-remote2="relatorio_paralisacoes_quadro.php?modo=2">Por Mês</a></li>
                                </ul>
                            </li>
                            <li class="divider"></li>
                            <li><a href="javascript:void(0);" data-load-remote2="relatorio_registro_grade_horario.php?modo=1">Quadro de Horário</a></li>
                            <?php if((isset($_SESSION['sGestaoUPAG']) && $_SESSION['sGestaoUPAG'] == 'S') || (isset($_SESSION['sAdmCentral']) && $_SESSION['sAdmCentral'] == 'S')): ?>
                                <li><a href="javascript:void(0);" data-load-remote2="troca_contexto_upag.php">Trocar Contexto de UPAG</a></li>
                            <?php endif; ?>

                        </ul>
                    </li>

                </ul>

                <ul class='nav navbar-nav navbar-right'>
                    <li><a href="javascript:void(0);" data-load-remote2="finaliza2.php?modulo=sogp"><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
                </ul>

            </div>
            <!-- incluir as tags abaixo -->
            <div class="col-sm-2"></div>
        </div>
        <!-- fim do incluir as tags -->
</div>
</form>
<?php



/* ********************************************************
 *                                                        *
 *                 FUNÇÕES COMPLEMENTARES                 *
 *                                                        *
 **********************************************************/

function menuSubOpcao( $opcao )
{
    if (count($opcao) >= 3 && $opcao[2] != "")
    {
        $icone = "<span class='glyphicon " . $opcao[2] . "'></span> ";
    }

    ?>
    <li><a href="javascript:void(0);" data-load-remote2="<?= $opcao[1]; ?>"><?= $icone . $opcao[0]; ?></a></li>
    <?php
}

function menuSeparador()
{
    ?>
    <li class="divider"></li>
    <?php
}

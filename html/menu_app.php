<?php

include_once( "config.php" );
include_once( "menu_app_class.php" );

/*
 * // Exibe um icone ao lado da opcao
 * $menu["<glyphicon-folder-open>Cadastro"] = array(
 *     "<glyphicon-folder-open>Funcional" => array(
 *         '<glyphicon-folder-open> Consultar' => 'cadastro_consulta.php',
 */

//$_SESSION["sRH"]  = "N";
//$_SESSION["sAPS"] = "S";

$menu = array();

if ($_SESSION["sRH"] == "S" || $_SESSION["sSenhaI"] == "S")
{
        ##########################################################################
        #                                                                        #
        #                          PRINCIPAL - NÍVEL 01                          #
        #                                                                        #
        ##########################################################################
        $menu["Cadastro"]           = array();
        $menu["Frequência"]         = array();
        $menu["Relatórios"]         = array();
        $menu["Tabelas"]            = array();
        $menu["Utilitários"]        = array();
        $menu["Gestão Estratégica"] = array();



    if ($_SESSION["sRH"] == "S" || $_SESSION["sSenhaI"] == "S")
    {
        ##########################################################################
        #                                                                        #
        #                          CADASTRO - NÍVEL 02                           #
        #                                                                        #
        ##########################################################################
        $menu["Cadastro"]["Funcional"]    = array();
        $menu["Cadastro"]["Gerencial"]    = array();
        $menu["Cadastro"]['separador1']   = '-';
        $menu["Cadastro"]["Movimentação"] = array();


        /* ********************************************************************* *
         *                 Cadastro - Funcional - Níveis 03 e 04                 *
         * ********************************************************************* */
        $menu["Cadastro"]["Funcional"]['Consultar']  = 'cadastro_consulta.php';
        $menu["Cadastro"]["Funcional"]['separador1'] = '-';
        $menu["Cadastro"]["Funcional"]["Incluir"]    = 'cadastro_inclusao.php';
        $menu["Cadastro"]["Funcional"]["Alterar"]    = 'cadastro_alteracao.php';
        $menu["Cadastro"]["Funcional"]["Excluir"]    = array();
            $menu["Cadastro"]["Funcional"]["Excluir"]['Efetivar Exclusão'] = 'cadastro_exclusao.php';
            $menu["Cadastro"]["Funcional"]["Excluir"]['Cancelar Exclusão'] = 'cadastro_exclusao_cancela.php';


        /* ********************************************************************* *
         *                 Cadastro - Gerencial - Níveis 03 e 04                 *
         * ********************************************************************* */
        $menu["Cadastro"]["Gerencial"]['Ocupar Funções']        = 'incfuncserv.php';
        $menu["Cadastro"]["Gerencial"]['Vagar Funções']         = 'excfuncserv.php';
        $menu["Cadastro"]["Gerencial"]['separador1']            = '-';
        $menu["Cadastro"]["Gerencial"]['Efetivar Substituição'] = 'subsfuncinf.php';
        $menu["Cadastro"]["Gerencial"]['Delegar Atribuição']    = array();
            $menu["Cadastro"]["Gerencial"]['Delegar Atribuição']['Registrar Delegação'] = 'delegacao.php?modo=10';
            $menu["Cadastro"]["Gerencial"]['Delegar Atribuição']['Cancelar Delegação']  = 'delegacao.php?modo=9';
        //$menu["Cadastro"]["Gerencial"]['separador2']            = '-';
        //$menu["Cadastro"]["Gerencial"]['Alterar Registro']      = 'altfuncserv.php';
        //$menu["Cadastro"]["Gerencial"]['Manter Histórico']      = 'manutencao_historico_funcao.php';


        /* ********************************************************************* *
         *               Cadastro - Movimentação - Níveis 03 e 04                *
         * ********************************************************************* */
        $menu["Cadastro"]["Movimentação"]['Alterar Lotação']     = 'movserv.php';
        //$menu["Cadastro"]["Movimentação"]['Alterar Localização'] = 'localserv.php';
        $menu["Cadastro"]["Movimentação"]['separador1']          = '-';
        $menu["Cadastro"]["Movimentação"]['Mudar UPAG']          = array();
            $menu["Cadastro"]["Movimentação"]['Mudar UPAG']['Liberar']            = 'liberupag.php';
            $menu["Cadastro"]["Movimentação"]['Mudar UPAG']['Cancelar Liberação'] = 'canliberupag.php';
            $menu["Cadastro"]["Movimentação"]['Mudar UPAG']['separador1']         = '-';
            $menu["Cadastro"]["Movimentação"]['Mudar UPAG']['Receber']            = 'recupag.php';
        //$menu["Cadastro"]["Movimentação"]['Manter Histórico']   = 'peshistlot.php';



        ##########################################################################
        #                                                                        #
        #                         FREQUÊNCIA - NÍVEL 02                          #
        #                                                                        #
        ##########################################################################
        //$menu["Frequência"]["Gerencial"]               = "regfreqger_entra.php";
        //$menu["Frequência"]['separador1']              = '-';
        $menu["Frequência"]["Acompanhar"]              = "frequencia_acompanhar_entra.php";
        $menu["Frequência"]["Homologar"]               = "frequencia_homologar_entra.php";
        $menu["Frequência"]['separador2']              = '-';

        //$menu["Frequência"]["Plantões"]                = array();
        //    $menu["Frequência"]["Plantões"]["Configurar"] = 'plantoes_configurar.php';
        //    $menu["Frequência"]["Plantões"]["Plantonistas"] = 'plantoes_servidores.php';

        $menu["Frequência"]["Banco de Horas"]          = array();
            $menu["Frequência"]["Banco de Horas"]["Acúmulo"]  = 'autorizacoes_acumulos.php';
            $menu["Frequência"]["Banco de Horas"]["Usufruto"] = 'autorizacoes_usufruto.php';
        $menu["Frequência"]["Autorização de Trabalho"] = "autorizacao_trabalho_dia_nao_util_entra.php";
        $menu["Frequência"]['separador3']              = '-';

        $menu["Frequência"]["Visualizar"]              = array();
            $menu["Frequência"]["Visualizar"]["Sua Folha de Frequência"]                      = 'pontoser.php?cmd=1';
            $menu["Frequência"]["Visualizar"]["Ficha de Frequência - Resumo Anual"]           = 'ficha_de_frequencia_resumo_anual.php';
            $menu["Frequência"]["Visualizar"]['separador1']                                   = '-';
            $menu["Frequência"]["Visualizar"]["Consulta Frequência"]                          = 'veponto.php';
            $menu["Frequência"]["Visualizar"]["Consulta Extrato Frequência"]                  = 'entrada9_individual.php?saldo=1';
            $menu["Frequência"]["Visualizar"]['separador2']                                   = '-';
            $menu["Frequência"]["Visualizar"]["Consulta Compensações - Matrícula (Recessos)"] = 'entrada9_individual.php?saldo=2';
            //$menu["Frequência"]["Visualizar"]['separador3']                                   = '-';
            //$menu["Frequência"]["Visualizar"]["Consulta Compensações - Matrícula (Recessos /<br>Copa do Mundo 2014 / Olimpíadas Rio 2016)"]= 'entrada9_individual.php?saldo=2';
            //$menu["Frequência"]["Visualizar"]["Copa do Mundo 2014 - Consulta Compensações de Todos"]  = 'sisref_relatorio_copa2014.php';
            //$menu["Frequência"]["Visualizar"]["Olimpíadas Rio 2016 - Consulta Compensações de Todos"] = 'sisref_relatorio_olimpiadas2016_nao_compensado.php';
            //$menu["Frequência"]["Visualizar"]["Alteração de horário"]                                 = 'vesolhor.php';
        $menu["Frequência"]['separador4']               = '-';

        $menu["Frequência"]["RH Atualizar"]              = array();
            $menu["Frequência"]["RH Atualizar"]['<span class="glyphicon glyphicon-time" aria-hidden="true"></span> Mês Corrente']       = 'frequencia_rh_mes_corrente.php';
            $menu["Frequência"]["RH Atualizar"]['<span class="glyphicon glyphicon-time" aria-hidden="true"></span> Mês em Homologação'] = 'frequencia_rh_mes_homologacao.php';
            $menu["Frequência"]["RH Atualizar"]["Serviços Extraordinários (autorização)"] = 'hora_extra_autorizacao.php';
            //$menu["Frequência"]["RH Atualizar"]['separador1']                             = '-';
            //$menu["Frequência"]["RH Atualizar"]["Envio para o SIAPE"]                     = 'envio_siape.php';
            $menu["Frequência"]["RH Atualizar"]['separador2']                             = '-';
            $menu["Frequência"]["RH Atualizar"]["Histórico"]                              = 'historico_frequencia.php';
            $menu["Frequência"]["RH Atualizar"]['separador3']                             = '-';
            $menu["Frequência"]["RH Atualizar"]["Verificar Homologações"]                 = 'frequencia_verificar_homologados.php';
            $menu["Frequência"]["RH Atualizar"]["Liberar para Homologação"]               = 'gestao_liberar_homologacao.php';
            $menu["Frequência"]["RH Atualizar"]['separador4']                             = '-';
            $menu["Frequência"]["RH Atualizar"]["Ciclos deBanco de Horas"]                = 'tabciclos_banco_horas.php';



        ##########################################################################
        #                                                                        #
        #                         RELATÓRIOS - NÍVEL 02                          #
        #                                                                        #
        ##########################################################################
        $menu["Relatórios"]["Frequência"]             = array();
        $menu["Relatórios"]["Gerencial"]              = array();
        $menu["Relatórios"]['separador1']             = '-';
        $menu["Relatórios"]["Movimentação"]           = array();
        $menu["Relatórios"]['separador2']             = '-';
        $menu["Relatórios"]["Serviços Extraordinários - Execução"] = 'hora_extra_relatorio_execucao.php';
        //$menu["Relatórios"]['separador3']             = '-';
        //$menu["Relatórios"]["Saldo Paralisações"]     = array();
        //$menu["Relatórios"]['separador4']             = '-';
        //$menu["Relatórios"]["Situação Unidades REAT"] = 'javascript:replaceLink("relatorio_situacao_reat.php")';


        /* ********************************************************************* *
         *               RELATÓRIOS - Frequência - Níveis 03 e 04                *
         * ********************************************************************* */
        $menu["Relatórios"]["Frequência"]['Por Ocorrências']            = 'sisref_relatorio_por_ocorrencia.php';
        //$menu["Relatórios"]["Frequência"]['Setores Pendentes']          = 'relfrqsetorp2.php';
        $menu["Relatórios"]["Frequência"]["Sem Homologação"]            = 'sisref_relatorio_frequencia_nao_homologados.php';
        $menu["Relatórios"]["Frequência"]["Homologados"]                = 'relfrqhomol.php';
        $menu["Relatórios"]["Frequência"]["Cedidos e Descentralizados"] = 'relatorio_cedidos_descentralizados.php';
        $menu["Relatórios"]["Frequência"]['separador1']                 = '-';
        $menu["Relatórios"]["Frequência"]["Para Comando"]               = array();
            $menu["Relatórios"]["Frequência"]["Para Comando"]['Cobrança']            = 'sisref_relatorio_ocorrencia_nao_compensada_competencia.php';
            //$menu["Relatórios"]["Frequência"]["Para Comando"]['Comando SIAPECAD']    = 'sisref_relatorio_comando_siapecad_competencia.php';
            $menu["Relatórios"]["Frequência"]["Para Comando"]['Recesso']             = 'sisref_relatorio_recesso_nao_compensado_competencia.php';
            //$menu["Relatórios"]["Frequência"]["Para Comando"]['Copa do Mundo 2014']  = 'sisref_relatorio_copa2014_nao_compensado.php';
            //$menu["Relatórios"]["Frequência"]["Para Comando"]['Olimpíadas Rio 2016'] = 'sisref_relatorio_olimpiadas2016_nao_compensado.php';
            //$menu["Relatórios"]["Frequência"]["Para Comando"]['separador1']          = '-';
            //$menu["Relatórios"]["Frequência"]["Para Comando"]['Inconsistências']     = array();
                //$menu["Relatórios"]["Frequência"]["Para Comando"]['Inconsistências']['Dias Zerados'] = 'rlsdiazero.php';


        /* ********************************************************************* *
         *               RELATÓRIOS - Gerencial - Níveis 03 e 04                 *
         * ********************************************************************* */
        $menu["Relatórios"]["Gerencial"]['Ocupantes de Funções']   = 'ocupfuncserv.php';
        $menu["Relatórios"]["Gerencial"]['Histórico de Funções']   = 'histfuncserv.php';
        $menu["Relatórios"]["Gerencial"]["Histórico de Ocupantes"] = 'histfuncserv2.php';
        $menu["Relatórios"]["Gerencial"]['separador1']             = '-';
        $menu["Relatórios"]["Gerencial"]["Substituições"]          = array();
            $menu["Relatórios"]["Gerencial"]["Substituições"]['Substituições na UPAG']     = 'sisref_relatorio_substituicoes.php';
            $menu["Relatórios"]["Gerencial"]["Substituições"]["Substituições do Servidor"] = 'pessubs.php';


        /* ********************************************************************* *
         *              RELATÓRIOS - Movimentação - Níveis 03 e 04               *
         * ********************************************************************* */
        $menu["Relatórios"]["Movimentação"]['Consulta Histórico']    = 'sisref_relatorio_consulta_movimentacao_servidor.php';
        $menu["Relatórios"]["Movimentação"]['Consulta Movimentação'] = 'pesmov.php';


        /* ********************************************************************* *
         *           RELATÓRIOS - Saldo Paralisações - Níveis 03 e 04            *
         * ********************************************************************* */
        //$menu["Relatórios"]["Saldo Paralisações"]['Todas as Carreiras']                              = 'relatorio_greve_saldo_horas_todos.php?modo=0';
        //$menu["Relatórios"]["Saldo Paralisações"]['Seguro Social, e Outras (Exceto Perícia Médica)'] = 'relatorio_greve_saldo_horas_todos.php?modo=1';
        //$menu["Relatórios"]["Saldo Paralisações"]['Perícia Médica']                                  = 'relatorio_greve_saldo_horas_todos.php?modo=4';



        ##########################################################################
        #                                                                        #
        #                          TABELAS - NÍVEL 02                            #
        #                                                                        #
        ##########################################################################
        $menu["Tabelas"]["Escalas"]          = array();
            $menu["Tabelas"]["Escalas"]["Criar Escalas"] = 'tabescalas.php';
        $menu["Tabelas"]["Isenção de Ponto"] = "tabisencao_ponto.php";
        $menu["Tabelas"]['separador1']       = '-';
        $menu["Tabelas"]["Cargos"]           = "tabcargo.php";
        $menu["Tabelas"]["Funções"]          = "tabfuncao.php";
        $menu["Tabelas"]["Feriados"]         = "tabferiados.php";
        //$menu["Tabelas"]["Ocorrências"]      = array();
        //    $menu["Tabelas"]["Ocorrências"]["Consultar / Alterar"] = 'tabela_ocorrencia_de_frequencia_visualizar.php';
        $menu["Tabelas"]["Setores"]          = "tablota.php";
        $menu["Tabelas"]["Ocorrências"]      = 'tabocorrencia.php';



        ##########################################################################
        #                                                                        #
        #                         UTILITÁRIOS - NÍVEL 02                         #
        #                                                                        #
        ##########################################################################
        $menu["Utilitários"]["Usuários"]      = array();
        $menu["Utilitários"]["Auditoria"]     = array();
        $menu["Utilitários"]['separador1']    = '-';
        $menu["Utilitários"]["Gestores"]      = array();
        $menu["Utilitários"]['separador2']    = '-';
        $menu["Utilitários"]["Manutenção"]    = array();
        $menu["Utilitários"]["Configurações"] = array();


        /* ********************************************************************* *
         *               UTILITÁRIOS - Usuários - Níveis 03 e 04                 *
         * ********************************************************************* */
        $menu["Utilitários"]["Usuários"]['Alterar Permissões de Usuário'] = 'usuario_lista.php';
        $menu["Utilitários"]["Usuários"]['Reiniciar Senha de Usuário']    = 'reiniciar1.php';
        $menu["Utilitários"]["Usuários"]["Trocar Sua Senha"]              = 'trocasenha.php';


        /* ********************************************************************* *
         *              UTILITÁRIOS - Auditoria - Níveis 03 e 04                 *
         * ********************************************************************* */
        $menu["Utilitários"]["Auditoria"]['Operações de Usuário']                   = 'perrellog.php';
        $menu["Utilitários"]["Auditoria"]['Identificar IP']                         = 'pesquisa_ip.php';
        $menu["Utilitários"]["Auditoria"]["Visualizar Frequência (Quem Registrou)"] = 'gestao_veponto.php';
        //$menu["Utilitários"]["Auditoria"]['separador1']                             = '-';
        //$menu["Utilitários"]["Auditoria"]["Operações Não Autorizadas"]              = 'pesilegal.php';
        //$menu["Utilitários"]["Auditoria"]['separador2']                             = '-';
        //$menu["Utilitários"]["Auditoria"]["Registros Alterados"]                    = array();
        //$menu["Utilitários"]["Auditoria"]["Registros Alterados"]["Alterações de Frequência"] = 'veregalterado.php';
        //$menu["Utilitários"]["Auditoria"]["Registros Alterados"]["Alterações de Cadastro"]   = 'vecadalt.php';


        /* ********************************************************************* *
         *               UTILITÁRIOS - Gestores - Níveis 03 e 04                 *
         * ********************************************************************* */
        //$menu["Utilitários"]["Gestores"]['Liberar Homologação'] = 'gestao_libera_homologacao.php';
        $menu["Utilitários"]["Gestores"]["Prazos"]              = 'tabvalida.php';


        /* ********************************************************************* *
         *              UTILITÁRIOS - Manutenção - Níveis 03 e 04                *
         * ********************************************************************* */
        $menu["Utilitários"]["Manutenção"]['Autorização por IP']             = 'autorizacao_ip.php';
        //$menu["Utilitários"]["Manutenção"]['Corrigir Acesso do Usuário']     = 'utilitarios_acesso_usuario.php';
        //$menu["Utilitários"]["Manutenção"]["Reinicializar Senha do Usuário"] = 'utilitarios_trocasenha_usuario.php';
        $menu["Utilitários"]["Manutenção"]['separador1']                     = '-';
        //$menu["Utilitários"]["Manutenção"]['Corrigir Matrícula']             = 'utilitarios_correcao_matricula.php';
        //$menu["Utilitários"]["Manutenção"]["Ajustar Histórico de Jornada"]   = 'utilitarios_ajuste_jornada_historico.php';
        $menu["Utilitários"]["Manutenção"]["Corrigir Substituição"]          = 'utilitarios_corrigir_substituicao.php';


        /* ********************************************************************* *
         *             UTILITÁRIOS - Configurações - Níveis 03 e 04              *
         * ********************************************************************* */
        $menu["Utilitários"]["Configurações"]['Gerais']                             = 'gerais_lista.php';
        $menu["Utilitários"]["Configurações"]['Host Autorizado']                    = 'host_autorizado_lista.php';
        $menu["Utilitários"]["Configurações"]['SIGAC']                              = 'configuracoes_sigac.php';
        $menu["Utilitários"]["Configurações"]['Serviços Extraordinários (limites)'] = 'configuracoes_limites_hora_extra.php';
        $menu["Utilitários"]["Configurações"]['Suporte']                            = 'configuracao_suporte_lista.php';



        ##########################################################################
        #                                                                        #
        #                     GESTÃO ESTRATÉGICA - NÍVEL 02                      #
        #                                                                        #
        ##########################################################################
        $menu["Gestão Estratégica"]["Consulta Frequência"] = 'veponto.php';
        $menu["Gestão Estratégica"]["Paralisações/Faltas"] = array();
        $menu["Gestão Estratégica"]['separador1']          = '-';
        $menu["Gestão Estratégica"]["Quadro de Horário"]   = 'relatorio_registro_grade_horario.php?modo=1';


        /* ********************************************************************* *
         *      GESTÃO ESTRATÉGICA - Paralisações/Faltas - Níveis 03 e 04        *
         * ********************************************************************* */
        $menu["Gestão Estratégica"]["Paralisações/Faltas"]['Por Dia'] = 'relatorio_paralisacoes_quadro.php?modo=1';
        $menu["Gestão Estratégica"]["Paralisações/Faltas"]["Por Mês"] = 'relatorio_paralisacoes_quadro.php?modo=2';
    }
    else if ($_SESSION["sAPS"] == "S")
    {
        ##########################################################################
        #                                                                        #
        #                          PRINCIPAL - NÍVEL 01                          #
        #                                                                        #
        ##########################################################################
        $menu["Frequência"]         = array();
        $menu["Tabelas"]            = array();
        $menu["Utilitários"]        = array();



        ##########################################################################
        #                                                                        #
        #                         FREQUÊNCIA - NÍVEL 02                          #
        #                                                                        #
        ##########################################################################
        $menu["Frequência"]["Acompanhar"]              = "frequencia_acompanhar_entra.php";
        $menu["Frequência"]["Homologar"]               = "frequencia_homologar_entra.php";
        $menu["Frequência"]['separador2']              = '-';

        //$menu["Frequência"]["Plantões"]                = array();
        //    $menu["Frequência"]["Plantões"]["Configurar"] = 'plantoes_configurar.php';
        //    $menu["Frequência"]["Plantões"]["Plantonistas"] = 'plantoes_servidores.php';

        $menu["Frequência"]["Banco de Horas"]          = array();
            $menu["Frequência"]["Banco de Horas"]["Acúmulo"]  = 'autorizacoes_acumulos.php';
            $menu["Frequência"]["Banco de Horas"]["Usufruto"] = 'autorizacoes_usufruto.php';
        $menu["Frequência"]["Autorização de Trabalho"] = "autorizacao_trabalho_dia_nao_util_entra.php";
        $menu["Frequência"]["Efetivar Substituição"]   = "subsfuncinf.php";
        $menu["Frequência"]['separador3']              = '-';

        $menu["Frequência"]["Visualizar"]              = array();
            $menu["Frequência"]["Visualizar"]["Sua Folha de Frequência"]                      = 'pontoser.php?cmd=1';
            $menu["Frequência"]["Visualizar"]['separador1']                                   = '-';
            $menu["Frequência"]["Visualizar"]["Consulta Frequência"]                          = 'veponto.php';
            $menu["Frequência"]["Visualizar"]["Consulta Extrato Frequência"]                  = 'entrada9_individual.php?saldo=1';



        ##########################################################################
        #                                                                        #
        #                          TABELAS - NÍVEL 02                            #
        #                                                                        #
        ##########################################################################
        $menu["Tabelas"]["Feriados"] = "tabferiados.php";



        ##########################################################################
        #                                                                        #
        #                         UTILITÁRIOS - NÍVEL 02                         #
        #                                                                        #
        ##########################################################################
        $menu["Utilitários"]['Autorização de IP por servidor'] = 'autorizacao_ip_servidor.php';
        $menu["Utilitários"]['Identificar IP']                 = 'pesquisa_ip.php';
        $menu["Utilitários"]['Reiniciar Senhas']               = 'reiniciar_senhas.php';
        $menu["Utilitários"]['separador1']                     = '-';
        $menu["Utilitários"]['Trocar Sua Senha']               = 'trocasenha.php';
    }

    $oMenu = new menu_app($menu);
    $oMenu->showMenu();
}

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
        #                          PRINCIPAL - N�VEL 01                          #
        #                                                                        #
        ##########################################################################
        $menu["Cadastro"]           = array();
        $menu["Frequ�ncia"]         = array();
        $menu["Relat�rios"]         = array();
        $menu["Tabelas"]            = array();
        $menu["Utilit�rios"]        = array();
        $menu["Gest�o Estrat�gica"] = array();



    if ($_SESSION["sRH"] == "S" || $_SESSION["sSenhaI"] == "S")
    {
        ##########################################################################
        #                                                                        #
        #                          CADASTRO - N�VEL 02                           #
        #                                                                        #
        ##########################################################################
        $menu["Cadastro"]["Funcional"]    = array();
        $menu["Cadastro"]["Gerencial"]    = array();
        $menu["Cadastro"]['separador1']   = '-';
        $menu["Cadastro"]["Movimenta��o"] = array();


        /* ********************************************************************* *
         *                 Cadastro - Funcional - N�veis 03 e 04                 *
         * ********************************************************************* */
        $menu["Cadastro"]["Funcional"]['Consultar']  = 'cadastro_consulta.php';
        $menu["Cadastro"]["Funcional"]['separador1'] = '-';
        $menu["Cadastro"]["Funcional"]["Incluir"]    = 'cadastro_inclusao.php';
        $menu["Cadastro"]["Funcional"]["Alterar"]    = 'cadastro_alteracao.php';
        $menu["Cadastro"]["Funcional"]["Excluir"]    = array();
            $menu["Cadastro"]["Funcional"]["Excluir"]['Efetivar Exclus�o'] = 'cadastro_exclusao.php';
            $menu["Cadastro"]["Funcional"]["Excluir"]['Cancelar Exclus�o'] = 'cadastro_exclusao_cancela.php';


        /* ********************************************************************* *
         *                 Cadastro - Gerencial - N�veis 03 e 04                 *
         * ********************************************************************* */
        $menu["Cadastro"]["Gerencial"]['Ocupar Fun��es']        = 'incfuncserv.php';
        $menu["Cadastro"]["Gerencial"]['Vagar Fun��es']         = 'excfuncserv.php';
        $menu["Cadastro"]["Gerencial"]['separador1']            = '-';
        $menu["Cadastro"]["Gerencial"]['Efetivar Substitui��o'] = 'subsfuncinf.php';
        $menu["Cadastro"]["Gerencial"]['Delegar Atribui��o']    = array();
            $menu["Cadastro"]["Gerencial"]['Delegar Atribui��o']['Registrar Delega��o'] = 'delegacao.php?modo=10';
            $menu["Cadastro"]["Gerencial"]['Delegar Atribui��o']['Cancelar Delega��o']  = 'delegacao.php?modo=9';
        //$menu["Cadastro"]["Gerencial"]['separador2']            = '-';
        //$menu["Cadastro"]["Gerencial"]['Alterar Registro']      = 'altfuncserv.php';
        //$menu["Cadastro"]["Gerencial"]['Manter Hist�rico']      = 'manutencao_historico_funcao.php';


        /* ********************************************************************* *
         *               Cadastro - Movimenta��o - N�veis 03 e 04                *
         * ********************************************************************* */
        $menu["Cadastro"]["Movimenta��o"]['Alterar Lota��o']     = 'movserv.php';
        //$menu["Cadastro"]["Movimenta��o"]['Alterar Localiza��o'] = 'localserv.php';
        $menu["Cadastro"]["Movimenta��o"]['separador1']          = '-';
        $menu["Cadastro"]["Movimenta��o"]['Mudar UPAG']          = array();
            $menu["Cadastro"]["Movimenta��o"]['Mudar UPAG']['Liberar']            = 'liberupag.php';
            $menu["Cadastro"]["Movimenta��o"]['Mudar UPAG']['Cancelar Libera��o'] = 'canliberupag.php';
            $menu["Cadastro"]["Movimenta��o"]['Mudar UPAG']['separador1']         = '-';
            $menu["Cadastro"]["Movimenta��o"]['Mudar UPAG']['Receber']            = 'recupag.php';
        //$menu["Cadastro"]["Movimenta��o"]['Manter Hist�rico']   = 'peshistlot.php';



        ##########################################################################
        #                                                                        #
        #                         FREQU�NCIA - N�VEL 02                          #
        #                                                                        #
        ##########################################################################
        //$menu["Frequ�ncia"]["Gerencial"]               = "regfreqger_entra.php";
        //$menu["Frequ�ncia"]['separador1']              = '-';
        $menu["Frequ�ncia"]["Acompanhar"]              = "frequencia_acompanhar_entra.php";
        $menu["Frequ�ncia"]["Homologar"]               = "frequencia_homologar_entra.php";
        $menu["Frequ�ncia"]['separador2']              = '-';

        //$menu["Frequ�ncia"]["Plant�es"]                = array();
        //    $menu["Frequ�ncia"]["Plant�es"]["Configurar"] = 'plantoes_configurar.php';
        //    $menu["Frequ�ncia"]["Plant�es"]["Plantonistas"] = 'plantoes_servidores.php';

        $menu["Frequ�ncia"]["Banco de Horas"]          = array();
            $menu["Frequ�ncia"]["Banco de Horas"]["Ac�mulo"]  = 'autorizacoes_acumulos.php';
            $menu["Frequ�ncia"]["Banco de Horas"]["Usufruto"] = 'autorizacoes_usufruto.php';
        $menu["Frequ�ncia"]["Autoriza��o de Trabalho"] = "autorizacao_trabalho_dia_nao_util_entra.php";
        $menu["Frequ�ncia"]['separador3']              = '-';

        $menu["Frequ�ncia"]["Visualizar"]              = array();
            $menu["Frequ�ncia"]["Visualizar"]["Sua Folha de Frequ�ncia"]                      = 'pontoser.php?cmd=1';
            $menu["Frequ�ncia"]["Visualizar"]["Ficha de Frequ�ncia - Resumo Anual"]           = 'ficha_de_frequencia_resumo_anual.php';
            $menu["Frequ�ncia"]["Visualizar"]['separador1']                                   = '-';
            $menu["Frequ�ncia"]["Visualizar"]["Consulta Frequ�ncia"]                          = 'veponto.php';
            $menu["Frequ�ncia"]["Visualizar"]["Consulta Extrato Frequ�ncia"]                  = 'entrada9_individual.php?saldo=1';
            $menu["Frequ�ncia"]["Visualizar"]['separador2']                                   = '-';
            $menu["Frequ�ncia"]["Visualizar"]["Consulta Compensa��es - Matr�cula (Recessos)"] = 'entrada9_individual.php?saldo=2';
            //$menu["Frequ�ncia"]["Visualizar"]['separador3']                                   = '-';
            //$menu["Frequ�ncia"]["Visualizar"]["Consulta Compensa��es - Matr�cula (Recessos /<br>Copa do Mundo 2014 / Olimp�adas Rio 2016)"]= 'entrada9_individual.php?saldo=2';
            //$menu["Frequ�ncia"]["Visualizar"]["Copa do Mundo 2014 - Consulta Compensa��es de Todos"]  = 'sisref_relatorio_copa2014.php';
            //$menu["Frequ�ncia"]["Visualizar"]["Olimp�adas Rio 2016 - Consulta Compensa��es de Todos"] = 'sisref_relatorio_olimpiadas2016_nao_compensado.php';
            //$menu["Frequ�ncia"]["Visualizar"]["Altera��o de hor�rio"]                                 = 'vesolhor.php';
        $menu["Frequ�ncia"]['separador4']               = '-';

        $menu["Frequ�ncia"]["RH Atualizar"]              = array();
            $menu["Frequ�ncia"]["RH Atualizar"]['<span class="glyphicon glyphicon-time" aria-hidden="true"></span> M�s Corrente']       = 'frequencia_rh_mes_corrente.php';
            $menu["Frequ�ncia"]["RH Atualizar"]['<span class="glyphicon glyphicon-time" aria-hidden="true"></span> M�s em Homologa��o'] = 'frequencia_rh_mes_homologacao.php';
            $menu["Frequ�ncia"]["RH Atualizar"]["Servi�os Extraordin�rios (autoriza��o)"] = 'hora_extra_autorizacao.php';
            //$menu["Frequ�ncia"]["RH Atualizar"]['separador1']                             = '-';
            //$menu["Frequ�ncia"]["RH Atualizar"]["Envio para o SIAPE"]                     = 'envio_siape.php';
            $menu["Frequ�ncia"]["RH Atualizar"]['separador2']                             = '-';
            $menu["Frequ�ncia"]["RH Atualizar"]["Hist�rico"]                              = 'historico_frequencia.php';
            $menu["Frequ�ncia"]["RH Atualizar"]['separador3']                             = '-';
            $menu["Frequ�ncia"]["RH Atualizar"]["Verificar Homologa��es"]                 = 'frequencia_verificar_homologados.php';
            $menu["Frequ�ncia"]["RH Atualizar"]["Liberar para Homologa��o"]               = 'gestao_liberar_homologacao.php';
            $menu["Frequ�ncia"]["RH Atualizar"]['separador4']                             = '-';
            $menu["Frequ�ncia"]["RH Atualizar"]["Ciclos deBanco de Horas"]                = 'tabciclos_banco_horas.php';



        ##########################################################################
        #                                                                        #
        #                         RELAT�RIOS - N�VEL 02                          #
        #                                                                        #
        ##########################################################################
        $menu["Relat�rios"]["Frequ�ncia"]             = array();
        $menu["Relat�rios"]["Gerencial"]              = array();
        $menu["Relat�rios"]['separador1']             = '-';
        $menu["Relat�rios"]["Movimenta��o"]           = array();
        $menu["Relat�rios"]['separador2']             = '-';
        $menu["Relat�rios"]["Servi�os Extraordin�rios - Execu��o"] = 'hora_extra_relatorio_execucao.php';
        //$menu["Relat�rios"]['separador3']             = '-';
        //$menu["Relat�rios"]["Saldo Paralisa��es"]     = array();
        //$menu["Relat�rios"]['separador4']             = '-';
        //$menu["Relat�rios"]["Situa��o Unidades REAT"] = 'javascript:replaceLink("relatorio_situacao_reat.php")';


        /* ********************************************************************* *
         *               RELAT�RIOS - Frequ�ncia - N�veis 03 e 04                *
         * ********************************************************************* */
        $menu["Relat�rios"]["Frequ�ncia"]['Por Ocorr�ncias']            = 'sisref_relatorio_por_ocorrencia.php';
        //$menu["Relat�rios"]["Frequ�ncia"]['Setores Pendentes']          = 'relfrqsetorp2.php';
        $menu["Relat�rios"]["Frequ�ncia"]["Sem Homologa��o"]            = 'sisref_relatorio_frequencia_nao_homologados.php';
        $menu["Relat�rios"]["Frequ�ncia"]["Homologados"]                = 'relfrqhomol.php';
        $menu["Relat�rios"]["Frequ�ncia"]["Cedidos e Descentralizados"] = 'relatorio_cedidos_descentralizados.php';
        $menu["Relat�rios"]["Frequ�ncia"]['separador1']                 = '-';
        $menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]               = array();
            $menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Cobran�a']            = 'sisref_relatorio_ocorrencia_nao_compensada_competencia.php';
            //$menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Comando SIAPECAD']    = 'sisref_relatorio_comando_siapecad_competencia.php';
            $menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Recesso']             = 'sisref_relatorio_recesso_nao_compensado_competencia.php';
            //$menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Copa do Mundo 2014']  = 'sisref_relatorio_copa2014_nao_compensado.php';
            //$menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Olimp�adas Rio 2016'] = 'sisref_relatorio_olimpiadas2016_nao_compensado.php';
            //$menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['separador1']          = '-';
            //$menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Inconsist�ncias']     = array();
                //$menu["Relat�rios"]["Frequ�ncia"]["Para Comando"]['Inconsist�ncias']['Dias Zerados'] = 'rlsdiazero.php';


        /* ********************************************************************* *
         *               RELAT�RIOS - Gerencial - N�veis 03 e 04                 *
         * ********************************************************************* */
        $menu["Relat�rios"]["Gerencial"]['Ocupantes de Fun��es']   = 'ocupfuncserv.php';
        $menu["Relat�rios"]["Gerencial"]['Hist�rico de Fun��es']   = 'histfuncserv.php';
        $menu["Relat�rios"]["Gerencial"]["Hist�rico de Ocupantes"] = 'histfuncserv2.php';
        $menu["Relat�rios"]["Gerencial"]['separador1']             = '-';
        $menu["Relat�rios"]["Gerencial"]["Substitui��es"]          = array();
            $menu["Relat�rios"]["Gerencial"]["Substitui��es"]['Substitui��es na UPAG']     = 'sisref_relatorio_substituicoes.php';
            $menu["Relat�rios"]["Gerencial"]["Substitui��es"]["Substitui��es do Servidor"] = 'pessubs.php';


        /* ********************************************************************* *
         *              RELAT�RIOS - Movimenta��o - N�veis 03 e 04               *
         * ********************************************************************* */
        $menu["Relat�rios"]["Movimenta��o"]['Consulta Hist�rico']    = 'sisref_relatorio_consulta_movimentacao_servidor.php';
        $menu["Relat�rios"]["Movimenta��o"]['Consulta Movimenta��o'] = 'pesmov.php';


        /* ********************************************************************* *
         *           RELAT�RIOS - Saldo Paralisa��es - N�veis 03 e 04            *
         * ********************************************************************* */
        //$menu["Relat�rios"]["Saldo Paralisa��es"]['Todas as Carreiras']                              = 'relatorio_greve_saldo_horas_todos.php?modo=0';
        //$menu["Relat�rios"]["Saldo Paralisa��es"]['Seguro Social, e Outras (Exceto Per�cia M�dica)'] = 'relatorio_greve_saldo_horas_todos.php?modo=1';
        //$menu["Relat�rios"]["Saldo Paralisa��es"]['Per�cia M�dica']                                  = 'relatorio_greve_saldo_horas_todos.php?modo=4';



        ##########################################################################
        #                                                                        #
        #                          TABELAS - N�VEL 02                            #
        #                                                                        #
        ##########################################################################
        $menu["Tabelas"]["Escalas"]          = array();
            $menu["Tabelas"]["Escalas"]["Criar Escalas"] = 'tabescalas.php';
        $menu["Tabelas"]["Isen��o de Ponto"] = "tabisencao_ponto.php";
        $menu["Tabelas"]['separador1']       = '-';
        $menu["Tabelas"]["Cargos"]           = "tabcargo.php";
        $menu["Tabelas"]["Fun��es"]          = "tabfuncao.php";
        $menu["Tabelas"]["Feriados"]         = "tabferiados.php";
        //$menu["Tabelas"]["Ocorr�ncias"]      = array();
        //    $menu["Tabelas"]["Ocorr�ncias"]["Consultar / Alterar"] = 'tabela_ocorrencia_de_frequencia_visualizar.php';
        $menu["Tabelas"]["Setores"]          = "tablota.php";
        $menu["Tabelas"]["Ocorr�ncias"]      = 'tabocorrencia.php';



        ##########################################################################
        #                                                                        #
        #                         UTILIT�RIOS - N�VEL 02                         #
        #                                                                        #
        ##########################################################################
        $menu["Utilit�rios"]["Usu�rios"]      = array();
        $menu["Utilit�rios"]["Auditoria"]     = array();
        $menu["Utilit�rios"]['separador1']    = '-';
        $menu["Utilit�rios"]["Gestores"]      = array();
        $menu["Utilit�rios"]['separador2']    = '-';
        $menu["Utilit�rios"]["Manuten��o"]    = array();
        $menu["Utilit�rios"]["Configura��es"] = array();


        /* ********************************************************************* *
         *               UTILIT�RIOS - Usu�rios - N�veis 03 e 04                 *
         * ********************************************************************* */
        $menu["Utilit�rios"]["Usu�rios"]['Alterar Permiss�es de Usu�rio'] = 'usuario_lista.php';
        $menu["Utilit�rios"]["Usu�rios"]['Reiniciar Senha de Usu�rio']    = 'reiniciar1.php';
        $menu["Utilit�rios"]["Usu�rios"]["Trocar Sua Senha"]              = 'trocasenha.php';


        /* ********************************************************************* *
         *              UTILIT�RIOS - Auditoria - N�veis 03 e 04                 *
         * ********************************************************************* */
        $menu["Utilit�rios"]["Auditoria"]['Opera��es de Usu�rio']                   = 'perrellog.php';
        $menu["Utilit�rios"]["Auditoria"]['Identificar IP']                         = 'pesquisa_ip.php';
        $menu["Utilit�rios"]["Auditoria"]["Visualizar Frequ�ncia (Quem Registrou)"] = 'gestao_veponto.php';
        //$menu["Utilit�rios"]["Auditoria"]['separador1']                             = '-';
        //$menu["Utilit�rios"]["Auditoria"]["Opera��es N�o Autorizadas"]              = 'pesilegal.php';
        //$menu["Utilit�rios"]["Auditoria"]['separador2']                             = '-';
        //$menu["Utilit�rios"]["Auditoria"]["Registros Alterados"]                    = array();
        //$menu["Utilit�rios"]["Auditoria"]["Registros Alterados"]["Altera��es de Frequ�ncia"] = 'veregalterado.php';
        //$menu["Utilit�rios"]["Auditoria"]["Registros Alterados"]["Altera��es de Cadastro"]   = 'vecadalt.php';


        /* ********************************************************************* *
         *               UTILIT�RIOS - Gestores - N�veis 03 e 04                 *
         * ********************************************************************* */
        //$menu["Utilit�rios"]["Gestores"]['Liberar Homologa��o'] = 'gestao_libera_homologacao.php';
        $menu["Utilit�rios"]["Gestores"]["Prazos"]              = 'tabvalida.php';


        /* ********************************************************************* *
         *              UTILIT�RIOS - Manuten��o - N�veis 03 e 04                *
         * ********************************************************************* */
        $menu["Utilit�rios"]["Manuten��o"]['Autoriza��o por IP']             = 'autorizacao_ip.php';
        //$menu["Utilit�rios"]["Manuten��o"]['Corrigir Acesso do Usu�rio']     = 'utilitarios_acesso_usuario.php';
        //$menu["Utilit�rios"]["Manuten��o"]["Reinicializar Senha do Usu�rio"] = 'utilitarios_trocasenha_usuario.php';
        $menu["Utilit�rios"]["Manuten��o"]['separador1']                     = '-';
        //$menu["Utilit�rios"]["Manuten��o"]['Corrigir Matr�cula']             = 'utilitarios_correcao_matricula.php';
        //$menu["Utilit�rios"]["Manuten��o"]["Ajustar Hist�rico de Jornada"]   = 'utilitarios_ajuste_jornada_historico.php';
        $menu["Utilit�rios"]["Manuten��o"]["Corrigir Substitui��o"]          = 'utilitarios_corrigir_substituicao.php';


        /* ********************************************************************* *
         *             UTILIT�RIOS - Configura��es - N�veis 03 e 04              *
         * ********************************************************************* */
        $menu["Utilit�rios"]["Configura��es"]['Gerais']                             = 'gerais_lista.php';
        $menu["Utilit�rios"]["Configura��es"]['Host Autorizado']                    = 'host_autorizado_lista.php';
        $menu["Utilit�rios"]["Configura��es"]['SIGAC']                              = 'configuracoes_sigac.php';
        $menu["Utilit�rios"]["Configura��es"]['Servi�os Extraordin�rios (limites)'] = 'configuracoes_limites_hora_extra.php';
        $menu["Utilit�rios"]["Configura��es"]['Suporte']                            = 'configuracao_suporte_lista.php';



        ##########################################################################
        #                                                                        #
        #                     GEST�O ESTRAT�GICA - N�VEL 02                      #
        #                                                                        #
        ##########################################################################
        $menu["Gest�o Estrat�gica"]["Consulta Frequ�ncia"] = 'veponto.php';
        $menu["Gest�o Estrat�gica"]["Paralisa��es/Faltas"] = array();
        $menu["Gest�o Estrat�gica"]['separador1']          = '-';
        $menu["Gest�o Estrat�gica"]["Quadro de Hor�rio"]   = 'relatorio_registro_grade_horario.php?modo=1';


        /* ********************************************************************* *
         *      GEST�O ESTRAT�GICA - Paralisa��es/Faltas - N�veis 03 e 04        *
         * ********************************************************************* */
        $menu["Gest�o Estrat�gica"]["Paralisa��es/Faltas"]['Por Dia'] = 'relatorio_paralisacoes_quadro.php?modo=1';
        $menu["Gest�o Estrat�gica"]["Paralisa��es/Faltas"]["Por M�s"] = 'relatorio_paralisacoes_quadro.php?modo=2';
    }
    else if ($_SESSION["sAPS"] == "S")
    {
        ##########################################################################
        #                                                                        #
        #                          PRINCIPAL - N�VEL 01                          #
        #                                                                        #
        ##########################################################################
        $menu["Frequ�ncia"]         = array();
        $menu["Tabelas"]            = array();
        $menu["Utilit�rios"]        = array();



        ##########################################################################
        #                                                                        #
        #                         FREQU�NCIA - N�VEL 02                          #
        #                                                                        #
        ##########################################################################
        $menu["Frequ�ncia"]["Acompanhar"]              = "frequencia_acompanhar_entra.php";
        $menu["Frequ�ncia"]["Homologar"]               = "frequencia_homologar_entra.php";
        $menu["Frequ�ncia"]['separador2']              = '-';

        //$menu["Frequ�ncia"]["Plant�es"]                = array();
        //    $menu["Frequ�ncia"]["Plant�es"]["Configurar"] = 'plantoes_configurar.php';
        //    $menu["Frequ�ncia"]["Plant�es"]["Plantonistas"] = 'plantoes_servidores.php';

        $menu["Frequ�ncia"]["Banco de Horas"]          = array();
            $menu["Frequ�ncia"]["Banco de Horas"]["Ac�mulo"]  = 'autorizacoes_acumulos.php';
            $menu["Frequ�ncia"]["Banco de Horas"]["Usufruto"] = 'autorizacoes_usufruto.php';
        $menu["Frequ�ncia"]["Autoriza��o de Trabalho"] = "autorizacao_trabalho_dia_nao_util_entra.php";
        $menu["Frequ�ncia"]["Efetivar Substitui��o"]   = "subsfuncinf.php";
        $menu["Frequ�ncia"]['separador3']              = '-';

        $menu["Frequ�ncia"]["Visualizar"]              = array();
            $menu["Frequ�ncia"]["Visualizar"]["Sua Folha de Frequ�ncia"]                      = 'pontoser.php?cmd=1';
            $menu["Frequ�ncia"]["Visualizar"]['separador1']                                   = '-';
            $menu["Frequ�ncia"]["Visualizar"]["Consulta Frequ�ncia"]                          = 'veponto.php';
            $menu["Frequ�ncia"]["Visualizar"]["Consulta Extrato Frequ�ncia"]                  = 'entrada9_individual.php?saldo=1';



        ##########################################################################
        #                                                                        #
        #                          TABELAS - N�VEL 02                            #
        #                                                                        #
        ##########################################################################
        $menu["Tabelas"]["Feriados"] = "tabferiados.php";



        ##########################################################################
        #                                                                        #
        #                         UTILIT�RIOS - N�VEL 02                         #
        #                                                                        #
        ##########################################################################
        $menu["Utilit�rios"]['Autoriza��o de IP por servidor'] = 'autorizacao_ip_servidor.php';
        $menu["Utilit�rios"]['Identificar IP']                 = 'pesquisa_ip.php';
        $menu["Utilit�rios"]['Reiniciar Senhas']               = 'reiniciar_senhas.php';
        $menu["Utilit�rios"]['separador1']                     = '-';
        $menu["Utilit�rios"]['Trocar Sua Senha']               = 'trocasenha.php';
    }

    $oMenu = new menu_app($menu);
    $oMenu->showMenu();
}

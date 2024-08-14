<?php
set_time_limit(28800); // 8 horas

$excessao = 'sim';

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
//verifica_permissao( 'sRH e sTabServidor' );
// dados por formulario
$dia = $_REQUEST['dia'];
$dia = "13/02/2013";

// dados dos ajustes realizados
$oDBase = PesquisaAjustesViaSistema($dia);

$nRegistrosProcessados = 0;
$nRegistros            = $oDBase->num_rows();

if ($nRegistros == 0)
{
    mensagem("Não foram realizados ajustes para esta Gerência Executiva", null, 1);
}
else
{
    ## cabecalho do html
    #
    htmlCabecalho();

    // grupos
    $quebra_sr  = ""; // quebra por superintendência
    $quebra_gex = ""; // quebra por gerência executiva
    $quebra_und = ""; // quebra por unidade
    $quebra_mat = ""; // quebra por servidor

    $quebra_limite = 0;

    ## AJUSTES
    #
    while ($dados = $oDBase->fetch_object())
    {
        if ($quebra_limite >= 15)
        {
            $quebra_gex    = "x"; // quebra por gerência executiva
            $quebra_und    = "x"; // quebra por unidade
            $quebra_limite = 0;
        }

        $quebra_limite++;

        $nRegistrosProcessados++;

        if (($quebra_gex == "" || $quebra_gex != $dados->cod_gex) || ($quebra_und == "" || $quebra_und != $dados->cod_lot))
        {
            if ($dados->cod_gex != "" || $quebra_und != $dados->cod_lot)
            {
                if ($quebra_gex != "" || $quebra_und != "")
                {
                    htmlDivFim(); // fim do documento
                }
                htmlDivInicio($dados); // inicio do documento
            }
            $quebra_gex = $dados->cod_gex;
        }

        if ($quebra_und == "" || $quebra_und != $dados->cod_lot)
        {
            htmlUnidade($dados);
            $quebra_und = $dados->cod_lot;
        }

        htmlDadosServidorFrequencia($dados);

        $quebra_mat = $dados->siape; // quebra por servidor
    }

    htmlDivFim();
    htmlRodape();

    $objBar->hide();
}


/*
 * cabecalho - configuração do html
 *
 */

function htmlCabecalho()
{
    ?>
    <!doctype html public "-//w3c//dtd html 4.01 transitional//pt">
    <html lang='pt-br'>
        <head>
            <title></title>
            <meta http-equiv='Content-Language' content='pt-br'>
            <meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
            <meta http-equiv='X-UA-Compatible' content='IE=Edge'/>
            <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>estiloIE.css' media='screen'>
            <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>class_formpadrao.css' media='screen'>
            <!-- Cinza granulado //-->
            <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>smoothness/jquery-ui-custom-px.min.css' media='screen'>
            <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>print3.css' media='print'>
            <style>
                .border { border: 1px solid; } .margin { margin: 0px; } .padding { padding: 0px; } .center { text-align: center; } .left { text-align: left; } .font-black-N { font-weight: bold; } .linha1 { border-bottom: 1px solid #e5e5e5; background-color: #eeeeee; height: 20px; } .linha2 { border-bottom: 1px solid #e5e5e5; background-color: #f7f7f7; height: 20px; }  .linha0 { border-bottom: 1px solid #e5e5e5; background-color: #f8f8f8; height: 20px; } .width { width: 10px; } .azul1 { color: #00699b; } .bgcolor { background-color: #efefef; } td {}
            </style>
            <script type='text/javascript' src='<?= _DIR_JS_; ?>jquery.js'></script>
            <script type='text/javascript' src='<?= _DIR_JS_; ?>jquery.blockUI.js?v2.38'></script>
            <script type='text/javascript' src='<?= _DIR_JS_; ?>plugins/jquery.dlg.min.js'></script>
            <script type='text/javascript' src='<?= _DIR_JS_; ?>plugins/jquery.easing.js'></script>
            <script type='text/javascript' src='<?= _DIR_JS_; ?>jquery.ui.min.js'></script>
        </head>
        <body id='imprimirPagina' style='margin: 0px; background-color: #FFFFFF; vertical-align: top; text-align: center;'>
    <?php
}

/*

        /*
         * inicio da pagina
         *
         */

        function htmlDivInicio($dados)
        {
            static $quebraDiv = '';

            if ($quebraDiv != $dados->siape)
            {
                ?>
                <div style='page-break-before: always; text-align: center; width: 900px;'>
                    <table class='margin padding center' width='900px' border='0' cellspacing='0' cellpadding='0' valign='top' style='word-spacing: 0; margin-top: 0; margin-bottom: 0;'>
                        <tr>
                            <td style='background: url(<?= _DIR_IMAGEM_; ?>top_centro.png); width: 900px; height: 80px; text-align: right; vertical-align: middle; color: white;' class='ft_18_001' colspan='16'>SISREF - Sistema de Registro Eletrônico de Frequência&nbsp;</td>
                            <td width='20%'></td>
                        </tr>
                        <tr>
                            <td colspan='16'><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' height='5' width='9px' border='0'></td>
                        </tr>
                        <tr>
                            <td class='left font-black-N linha1' colspan='16'>&nbsp;<?=tratarHTML($dados->nome_ger); ?>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='left font-black-N linha2' colspan='15'>&nbsp;<?= tratarHTML($dados->nome_gex); ?>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class='left width' colspan='1'>&nbsp;</td><td class='left width' colspan='1'>&nbsp;</td>
                            <td class='left font-black-N linha2' colspan='14'>&nbsp;<?= mascaraOl($dados->cod_lot) . " - " . tratarHTML($dados->descricao); ?>&nbsp;</td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <?php
                    }
                    $quebraDiv = $dados->siape;

                }

                /*
                 * código e descrição da unidade
                 *
                 */

                function htmlUnidade($dados)
                {
                    static $quebraUnd = '';

                    if ($quebraUnd != $dados->siape)
                    {
                        /*
                          ?>
                          <?php
                         */
                    }
                    $quebraUnd = $dados->siape;

                }

                function htmlDadosServidorFrequencia($dados)
                {
                    static $quebraMat = '';

                    switch ($dados->registrado_por)
                    {
                        case 'R': $dados_idreg = 'RH';
                            break;
                        case 'A':
                        case 'C': $dados_idreg = 'Chefia';
                            break;
                        case 'X': $dados_idreg = 'SISREF';
                            break;
                        case 'S': $dados_idreg = ($dados->cod_sitcad == '66' ? 'Estagiario' : 'Servidor');
                            break;
                    }

                    if ($quebraMat != $dados->siape)
                    {
                        ?>
                        <tr style='height: 20px;'><td colspan='16'>&nbsp;</td></tr>
                        <tr>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='border left azul1 font-black-N' colspan='13'><?= tratarHTML($dados->siape . ' - ' . $dados->nome); ?></td>
                        </tr>
                        <tr>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='left width' colspan='1'>&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Situacao&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Dia Útil&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Data&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Entrada&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Saída p/&nbsp;<br>&nbsp;o Almoço&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Retorno&nbsp;<br>&nbsp;do Almoço&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Saída&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Jornada&nbsp;<br>&nbsp;Realizada&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Jornada&nbsp;<br>&nbsp;Prevista&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Compensar&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Jornada&nbsp;<br>&nbsp;Diferença&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Ocorrência&nbsp;</td>
                            <td class='border ui-widget-header' nowrap>&nbsp;Registrado<br>por&nbsp;</td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr style='height: 20px;'>
                        <td class='left width' colspan='1'>&nbsp;</td>
                        <td class='left width' colspan='1'>&nbsp;</td>
                        <td class='left width' colspan='1'>&nbsp;</td>
                        <?php
                        if ($dados->grupo == 'Cadastro/TE')
                        {
                            ?>
                            <td class='border center' colspan='3'><?= tratarHTML($dados->grupo); ?></td>
                            <?php
                        }
                        else
                        {
                            ?>
                            <td class='border center' nowrap><?= tratarHTML($dados->grupo); ?></td>
                            <td class='border center' nowrap><?= tratarHTML($dados->dia_util); ?></td>
                            <td class='border center' nowrap>&nbsp;<?= tratarHTML($dados->dia); ?>&nbsp;</td>
                            <?php
                        }
                        ?>
                        <td class='border center' nowrap><?= tratarHTML($dados->entrada); ?></td>
                        <td class='border center' nowrap><?= tratarHTML($dados->almoco_inicio); ?></td>
                        <td class='border center' nowrap><?= tratarHTML($dados->almoco_fim); ?></td>
                        <td class='border center' nowrap><?= tratarHTML($dados->saida); ?></td>
                        <td class='border center' nowrap><?= tratarHTML($dados->jornada_realizada); ?></td>
                        <td class='border center' nowrap><?= tratarHTML($dados->jornada_prevista); ?></td>
                        <td class='border center' nowrap><?= tratarHTML($dados->compensar); ?></td>
                        <td class='border center' nowrap><?= tratarHTML(($dados->jornada_diferenca == '' ? '&nbsp;' : $dados->jornada_diferenca)); ?></td>
                        <td class='border center' nowrap><?= tratarHTML(($dados->ocorrencia == '' ? '&nbsp;' : $dados->ocorrencia)); ?></td>
                        <td class='border center' nowrap><?= tratarHTML(($dados_idreg == '' ? '&nbsp;' : $dados_idreg)); ?></td>
                    </tr>
                    <?php
                    $quebraMat = $dados->siape;

                }

                /*
                 * fim da pagina
                 *
                 */

                function htmlDivFim()
                {
                    ?>
                    <tr>
                        <td colspan='15' style='height: 3px;'>&nbsp;</td>
                    </tr>
                </table>
            </div><!-- fix bug -->
            <?php

        }

        /*
         * rodape - configuração do html
         *
         */

        function htmlRodape()
        {
            ?>
        </body>
    </html>
    <?php

}

function PesquisaAjustesViaSistema($dia = '')
{
    $oDBase = new DataBase('PDO');

    // informacoes do cadastro/turno estendido
    $grupoUnidade    = $_SESSION['sLotacao'];
    $grupoUnidade[2] = ($grupoUnidade[2] == '0' ? $grupoUnidade[2] : '0');

    $oDBase->query('
		SELECT
			grupo, id_ger, nome_ger, cod_gex, nome_gex, cod_lot, descricao, siape, nome, situacao, dia_util, dia,
			entrada, almoco_inicio, almoco_fim, saida,
			jornada_realizada, jornada_prevista, compensar, jornada_diferenca, ocorrencia, registrado_por
		FROM
			ajustes_via_sistema
		WHERE
			dia = "' . conv_data($dia) . '" AND cod_gex = "' . left($grupoUnidade, 5) . '"
	');
    return $oDBase;

}

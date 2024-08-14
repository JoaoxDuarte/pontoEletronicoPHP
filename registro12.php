<?php

include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sAPS");

$matricula_siape     = anti_injection($_REQUEST["mat"]);
$unidade_de_lotacao  = anti_injection($_REQUEST["lot"]);
$jornada_de_trabalho = anti_injection($_REQUEST["jnd"]);
$nome_do_servidor    = anti_injection($_REQUEST["nome"]);
$dia                 = anti_injection($_REQUEST["dia"]);
$comando             = anti_injection($_REQUEST["cmd"]);

$dia_padrao_mysql = conv_data($dia);


## ocorrências grupos
$obj = new OcorrenciasGrupos();
$CodigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad=null, $exige_horarios=true);
$CodigosCredito      = $obj->CodigosCredito($sitcad=null,$temp=false);


$oTabValida = new DataBase('PDO');
$oTabValida->setMensagem("Erro: ");
$oTabValida->query("SELECT recessoi, recessof FROM tabvalida WHERE ativo = 'S' ");
$oDados         = $oTabValida->fetch_object();
$recesso_inicio = $oDados->recessoi;
$recesso_final  = $oDados->recessof;

// define competencia
$ano  = date(Y);
$hoje = date("d/m/Y");
if (date(n) < "10")
{
    $mes  = "0" . (date(n));
    $year = $ano;
    $comp = $mes . $year;
}
if (date(n) >= "10")
{
    $mes  = date(n);
    $year = $ano;
    $comp = $mes . $year;
}
$_SESSION['compete'] = $comp;

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » ... » Ocorrência em Dias Anteriores');
$oForm->setSubTitulo("Registro de Ocorr&ecirc;ncias em Dias Anteriores");

$oForm->setJS('registro12.js');

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form action="gravaregfreq2.php?modo=1"  method="post" id="form1" name="form1" onSubmit="return validar()" >
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><b><h3>
        <input type='hidden' name='compete' id='compete' value='<?= tratarHTML($comp); ?>' >
        <input type='hidden' name='hoje'    id="hoje"    value='<?= tratarHTML($hoje); ?>' >
        <input type='hidden' name='cmd'     id="cmd"     value='<?= tratarHTML($comando); ?>' >
        <input type='hidden' name='mes'     id="mes"     value='<?= tratarHTML($mes); ?>' >
        <input type='hidden' name='mes2'    id="mes2"    value='<?= tratarHTML($mes); ?>' >
        <input type='hidden' name='ano'     id="ano"     value='<?= tratarHTML($ano); ?>' >
        <input type='hidden' name='inirec'  id="inirec"  value='<?= tratarHTML($recesso_inicio); ?>' >
        <input type='hidden' name='fimrec'  id="fimrec"  value='<?= tratarHTML($recesso_final); ?>' >
        <input type='hidden' name='hoje2'   id="hoje2"   value='<?= tratarHTML($dia_padrao_mysql); ?>' >
        <input type='hidden' name='jnd'     id="jnd"     value='<?= tratarHTML($jornada_de_trabalho); ?>' >

        <input type='hidden' name='debitosCompensaveis' id='debitosCompensaveis' value='<?= tratarHTML(implode(',', $CodigosCompensaveis)); ?>' >
        <input type='hidden' name='codigosCreditos'     id='codigosCreditos'     value='<?= tratarHTML(implode(',', $CodigosCredito)); ?>' >

        <div align="center">

            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="95%" id="AutoNumber1">
                <tr>
                    <td colspan="2" class="ft_13_002">
                        Dados do Servidor:
                    </td>
                </tr>
            </table>

            <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="95%" id="AutoNumber1">
                <tr>
                    <td width="619" height="46" class="tahomaSize_2">
                        Nome:<br><input type="text" id="nome" name="nome" class='caixa' value='<?= tratarHTML($nome_do_servidor); ?>' size="60" readonly>
                        <div align="center"></div>
                        <div align="center"></div>
                    </td>
                    <td width="144" align="center" class="tahomaSize_2">
                        Mat.Siape:<br><input type="text" id="mat" name="mat" class='caixa' value='<?= tratarHTML($matricula_siape); ?>' size="7" readonly>
                    </td>
                </tr>
            </table>

            <table width="95%" border="1" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse; margin-bottom: 0;">
                <tr>
                    <td width="81%" height="39" align="left" class="tahomaSize_2">
                        C&oacute;digo da Ocorr&ecirc;ncia:<br>
                        <select id='ocor' name="ocor" size="1" class="drop" title='Selecione a ocorrência!'>
                            <?php

                            // tabela de ocorrencia
                            $oTbDados = new DataBase('PDO');
                            $oTbDados->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE resp IN ('CH','AB') AND (siapecad != '00195') AND ativo = 'S' ORDER BY desc_ocorr ");

                            while ($campo    = $oTbDados->fetch_object())
                            {
                                echo "<OPTION VALUE=\"" . tratarHTML($campo->siapecad) . "\"";
                                echo " >" . tratarHTML($campo->siapecad) . " - " . tratarHTML(substr($campo->desc_ocorr, 0, 60)) . " -  " . (empty($campo->desc_ocorr) ? "Seleciona uma ocorrência" : "SIRH") . " " . tratarHTML($campo->cod_ocorr) . "</OPTION>";
                            }
                            // Fim da tabela de ocorrencia

                            ?>
                        </select>

                        <a href= "javascript:Abre('tabocfre.php',1060,350)"><img border= '0' src='<?= _DIR_IMAGEM_; ?>pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar detalhes da ocorrência.'></a>
                    </td>
                    <td width="19%" align="center" class="tahomaSize_2">
                        Dia da Ocorr&ecirc;ncia:<br><input type="text" id="dia" name="dia" class='centro' value='<?= tratarHTML($dia); ?>' size="11" readonly>
                    </td>
                </tr>
            </table>

        </div>

        <p align="center" style="word-spacing: 0; margin-left: 0; margin-right: 0; margin-top: 25">
            <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>concluir.gif" name="enviar" alt="Incluir ocorrência"  onclick="return testa()" align="center" >
        </p>
    </p>
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

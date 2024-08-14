<?php
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('sRH ou Chefia');

$sLotacao = $_SESSION["sLotacao"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $pSiape = anti_injection($_REQUEST["mat"]);
    $dia    = $_REQUEST["dia"];
    $comp   = anti_injection($_REQUEST["comp"]);
}
else
{
    // Valores passados - encriptados
    $dados  = explode(":|:", base64_decode($dadosorigem));
    $pSiape = $dados[0];
    $dia    = $dados[1];

    $dadosorigem = base64_encode($pSiape.":|:".$dia);
}

$mes  = dataMes($dia);
$ano  = dataAno($dia);
$comp = $mes . $ano;

$pSiape = getNovaMatriculaBySiape($pSiape);

$_SESSION['sHOrigem_3'] = pagina_de_origem();
$_SESSION['sHOrigem_4'] = "veponto3.php?dados=$dadosorigem";

include_once( "ilegal2.php" );

/* obtem dados dos servidores */
$oDBase    = new DataBase('PDO');
$oDBase->setMensagem('Problemas de acesso ao Cadastro');
$oDBase->setDestino(pagina_de_origem());
$oDBase->query("
SELECT
    servativ.nome_serv,
    servativ.entra_trab,
    servativ.ini_interv,
    servativ.sai_interv,
    servativ.sai_trab,
    servativ.cod_lot,
    servativ.chefia,
    servativ.jornada,
    tabsetor.upag,
    tabsetor.codmun,
    servativ.excluido,
    servativ.dt_adm,
    servativ.oco_exclu_dt,
    servativ.sigregjur
FROM
    servativ
LEFT JOIN
    tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE
    servativ.mat_siape = :siape
",
array(
    array(':siape', $pSiape, PDO::PARAM_STR)
));

$oServidor = $oDBase->fetch_object();
$nome          = $oServidor->nome_serv;
$lot           = $oServidor->cod_lot;
$chefe         = $oServidor->chefia;
$jnd           = $oServidor->jornada;
$codmun        = $oServidor->codmun;
$excluido      = $oServidor->excluido;
$admissao      = $oServidor->dt_adm;
$data_exclusao = $oServidor->oco_exclu_dt;
$sitcad        = $oServidor->sigregjur;

if ($excluido == 'N')
{
    $dia_limite_para_inserir = ($ano == date('Y') && $mes == date('m') ? date('d') : '');
    inserir_dias_sem_frequencia( $pSiape, $dia_limite_para_inserir, $mes, $ano, $jornada, $lot, '', $admissao, $data_exclusao );
}

/* obtem dados da upag para saber se é a mesma do usuario */
$upg = $oServidor->upag;

if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && $lot != $_SESSION['sLotacao'] && $chefe == "N")
{
    mensagem("Não é permitido consultar/alterar servidor de outro setor!", pagina_de_origem());
}
elseif ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
{
    mensagem("Não é permitido consultar ponto de servidor de outra upag!", pagina_de_origem());
}


$matchef = $_SESSION['sMatricula'];
$oDBase->setMensagem('Problemas de acesso ao Cadastro de Ocupantes de Funções');
$oDBase->setDestino(pagina_de_origem());
$oDBase->query("SELECT sit_ocup FROM ocupantes WHERE mat_siape ='$matchef' AND sit_ocup = 'S' ");
$sit     = $oDBase->fetch_object()->sit_ocup;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar » Exclusão de Ocorrências');

$oForm->setSeparador(0);
// Topo do formulário
//
$oForm->setSubTitulo("Exclusão de Ocorrências");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="post" action="gravaregfreq2.php" id="form1" name="form1">
    <input type='hidden' id='modo' name='modo' value='6'>
    <input type='hidden' id='sit'  name='sit'  value='<?= tratarHTML($sit); ?>'>

    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
    <tr>
            <td height="20" bgcolor=""> <p align="center"><b>Siape</b></td>
            <td width="54%" height="20" bgcolor=""><div align="center"><b>Nome</b></div></td>
            <td height="20" bgcolor=""><div align="center"><b>Lota&ccedil;&atilde;o</b></div></td>
        </tr>
        <tr>
            <td width="10%">
                <div align="center">
                    <input name="mat" type="text" class='form-control' id="mat" value="<?= tratarHTML($pSiape); ?>" size="10" readonly>
                </div>
            </td>
            <td>
                <p align="left">
                    <input name="nome" type="text" class='form-control' id="nome" value="<?= tratarHTML($nome); ?>" size="70" readonly>
            </td>
            <td width="16%">
                <p align="center">
                    <input name="lotacao" type="text" class='form-control ' id="lotacao" value="<?= tratarHTML($lot); ?>" size="15" readonly>
                    <input name="comp" type="hidden" class='form-control ' id="comp" value="<?= tratarHTML($comp); ?>" size="10" readonly>
            </td>
        </tr>
        <tr>
            <td height="20" colspan="3" bgcolor="">
                <p align="center"><b>Registro de comparecimento</b> <font face="Tahoma" size="2" color="#333300"></font>
            </td>
        </tr>
        <tr>
            <td colspan="3"><div align="center"><font face="Tahoma" size="2" color="#333300"><b><?= substr($comp, 0, 2) . '/' . substr($comp, 2, 4); ?></b></td>
        </tr>
    </table>
<!--    <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">-->
    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
    <tr>
        <td width="12%" class='' bgcolor="" height="20"><label>Dia</label></td>
            <td width="9%"  class='' bgcolor=""><label>Entrada</label></td>
            <td width="9%"  class='' bgcolor=""><label>Ida intervalo </label></td>
            <td width="9%"  class='' bgcolor=""><label>Volta Intervalo</label></td>
            <td width="9%"  class='' bgcolor=""><label>Saida</label></td>
            <td width="9%"  class='' bgcolor=""><label>Jornada do dia</label></td>
            <td width="10%" class='' bgcolor=""><label>Jornada prevista</label></td>
            <td width="9%"  class='' bgcolor=""><label>Resultado do dia</label></td>
            <td width="15%" class='' bgcolor=""><label>Ocorr&ecirc;ncia</label></td>
        </tr>
        <?php
// grava o LOG
        $vHoras = strftime("%H:%M:%S", time());
        $vDatas = date("Y/m/d");
        $hoje   = date("d/m/Y");

        $oDBase->setMensagem("Problemas de acesso ao Ponto $comp");
        $oDBase->query("
        SELECT
            pto.dia,
            pto.entra,
            pto.intini,
            pto.intsai,
            pto.sai,
            pto.jornd,
            pto.jornp,
            pto.jorndif,
            pto.oco,
            pto.just,
            pto.dia,
            tabsetor.codmun,
            tabsetor.codigo,
            tabocfre.desc_ocorr
        FROM
            ponto$comp AS pto
        LEFT JOIN
            tabocfre ON pto.oco = tabocfre.siapecad
        LEFT JOIN
            servativ ON pto.siape = servativ.mat_siape
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        WHERE
            pto.siape = :siape
            AND pto.dia <> '0000-00-00'
        ORDER BY
            pto.dia
        ",
        array(
            array(':siape', $pSiape, PDO::PARAM_STR)
        ));
        
        $umavez = true;

        while ($pm_partners = $oDBase->fetch_object())
        {
            if ($umavez == true)
            {
                $umavez       = false;
                $dia_nao_util = marca_dias_nao_util(dataMes($pm_partners->dia), dataAno($pm_partners->dia), $pm_partners->codmun, $pm_partners->codigo);


                ## ocorrências grupos
                $obj = new OcorrenciasGrupos();
                $codigosCompensaveis              = $obj->GrupoOcorrenciasNegativasDebitos($pm_partners->sigregjur, $exige_horarios=true);
                $codigosDebito                    = $obj->CodigosDebito($pm_partners->sigregjur);
                $codigosCredito                   = $obj->CodigosCredito($pm_partners->sigregjur, $temp=true);
                $codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($pm_partners->sigregjur);

                $grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($pm_partners->sigregjur);

                $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($pm_partners->sigregjur);
                $codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($pm_partners->sigregjur);
            }

            $xdia       = databarra($pm_partners->dia);
            $background = $dia_nao_util[$xdia][0];
            $color      = $dia_nao_util[$xdia][1];

            $gravaregfreq2 = "gravaregfreq2.php?modo=5&mat=" . $pSiape . "&comp=" . $comp . "&dia=" . $pm_partners->dia . "&oco=" . $pm_partners->oco . "&lot=" . $lot . "&sit=" . $sit;

            $font_i_color = "";
            $sinal        = '&nbsp;';
            $font_f_color = "";

            // elimina "/" e ":", depois define o tipo como inteiro
            // para garantir a resultado do teste a seguir
            $jornada_dif = alltrim(sonumeros($pm_partners->jorndif), '0');
            settype($jornada_dif, 'integer');


            if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCompensaveis))
            {
                $font_i_color = "<font color='red'>";
                $font_f_color = "</font>";
                $sinal        = " - ";
            }
            else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosDebito))
            {
                $font_i_color = "<font color='red'>(";
                $font_f_color = ")</font>";
                $sinal        = "";
            }
            else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCredito))
            {
                $sinal = " + ";
            }

            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' style='<?= $background; ?>'>
                <td width='96px' class='ftFormFreq-cn-1' title="<?= $dia_nao_util[$xdia][4]; ?>"><?= rtrim(ltrim($dia_nao_util[$xdia][2])) . '&nbsp;' . $xdia . $dia_nao_util[$xdia][3]; ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>'><?= tratarHTML($pm_partners->entra); ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>'><?= tratarHTML($pm_partners->intini); ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>'><?= tratarHTML($pm_partners->intsai); ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>'><?= tratarHTML($pm_partners->sai); ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>'><?= tratarHTML($pm_partners->jornd); ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>'><?= tratarHTML($pm_partners->jornp); ?></td>
                <td class='ftFormFreq-cn-1' style='<?= $color; ?>; text-align: center;'>
                    <table border='0' cellpadding='0' cellspacing='0'>
                        <tr>
                            <td class='ftFormFreq-cn-1' style='<?= $color; ?>; text-align: center;' width='13'><?= tratarHTML($font_i_color) . tratarHTML($sinal) . tratarHTML($font_f_color); ?></td>
                            <td class='ftFormFreq-cn-1' style='<?= $color; ?>; text-align: center;' width='37'><?= tratarHTML($font_i_color) . tratarHTML($pm_partners->jorndif) . tratarHTML($font_f_color); ?></td>
                        </tr>
                    </table>
                </td>
                <td class='ftFormFreq-cn-1' style="<?= $color; ?>" title="<?= tratarHTML($pm_partners->desc_ocorr) . "\n" . tratarHTML($pm_partners->just); ?>">
                    <?= tratarHTML($pm_partners->oco); ?> - <a href= "<?= tratarHTML($gravaregfreq2); ?>"><img border="0" src="<?= _DIR_IMAGEM_; ?>lixeira2.jpg" width="16" height="16" align="absmiddle" alt="Excluir ocorrência"></a> - <input type="checkbox" name="c[]" value="<?= tratarHTML($pm_partners->dia); ?>"  title="clique para selecionar" >
                </td>
            </tr>
            <?php
        } // fim do while
        ?>
    </table>

    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse;">
        <tr>
            <td style='font-size: 8px;'><font color='red'><b>D: </b></font>Domingo&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>S: </b></font>Sábado&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>F: </b></font>Feriado/Facultativo (Posicione o mouse sobre o dia para ver a descrição)</td>
        </tr>
    </table>

    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6;">
        <font size="1">Obs: Clique no &iacute;cone <img border="0" src="<?= _DIR_IMAGEM_; ?>lixeira2.jpg" width="16" height="16" align="absmiddle" alt="Excluir ocorrência"></a></font><font size="1">para excluir ocorr&ecirc;ncia ou selecione as ocorr&ecirc;ncias que deseja excluir clicando na caixa de sele&ccedil;&atilde;o e ap&oacute;s no bot&atilde;o excluir.<br>Após excluir ocorrências atualize a página.</font>
    </p>

    <p align="center">

        <input class="btn btn-warning" type='submit' value='&nbsp;Excluir&nbsp;' >&nbsp;&nbsp;
        <a class="btn btn-danger" onClick='javascript:window.history.go(-1);' href="#">
            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
        </a>
    </p>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

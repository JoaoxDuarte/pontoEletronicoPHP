<?php
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH ou Chefia');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];
if (empty($dadosorigem))
{
    // le dados passados por formulario
    $matricula_siape  = anti_injection($_REQUEST["mat"]);
    $lotacao_servidor = '';

    // prepara o arquivo de retorno com os valores passados
    // atraves da sessao $_SESSION['sChaveCriterio']
    // Exemplo: $_SESSION['sChaveCriterio'] = array( "chave" => $var1, "escolha" => $var2 );
    //
		$destino_retorno = valoresParametros("cadastro_consulta.php");
}
else
{
    // Valores passados - encriptados
    $dados            = explode(":|:", base64_decode($dadosorigem));
    $matricula_siape  = $dados[0];
    $lotacao_servidor = $dados[1];
    $pagina           = $dados[2];
    $destino_retorno  = ($pagina == 'acompanhar' ? "frequencia_acompanhar_registros.php?dados=" . $_SESSION['voltar_nivel_1'] : $_SESSION['sPaginaRetorno_sucesso']); //"regfreq4.php?cmd=2&orig=1";
}

// se chefia e não é do rh
$pesquisa = "";
if ($_SESSION["sRH"] != "S" && $_SESSION["sAPS"] == "S")
{
    $pesquisa = " AND cod_lot = '" . ($lotacao_servidor == '' ? $_SESSION['sLotacao'] : $lotacao_servidor) . "' ";
}

$oTbDados        = new DataBase('PDO');
$oTbDados->query("
		SELECT
			a.mat_siape, a.ident_unica, a.mat_dtp, a.mat_Siapecad, a.nome_serv, a.cod_cargo, d.desc_cargo, a.cod_lot, f.descricao AS descricao_lotacao, a.cod_loc, g.descricao, a.cod_sitcad, c.descsitcad, a.cod_classe, a.cod_padrao, a.jornada, a.reg_jur_at, e.desc_rj,
			DATE_FORMAT(a.dt_nasc, '%d/%m/%Y')     AS dtnasc,      DATE_FORMAT(a.dt_adm, '%d/%m/%Y')     AS dt_adm,
			DATE_FORMAT(a.dt_ing_lot, '%d/%m/%Y')  AS dt_ing_lot,  DATE_FORMAT(a.dt_ing_car, '%d/%m/%Y') AS dt_ing_car,
			DATE_FORMAT(a.dt_ing_jorn, '%d/%m/%Y') AS dt_ing_jorn, DATE_FORMAT(a.dt_ing_loc, '%d/%m/%Y') AS dt_ing_loc,
			IFNULL(b.cod_ocorr,'') AS cod_ocorr, IFNULL(h.desc_ocorr,'') AS desc_ocorr, IFNULL(b.dt_ocorr,'') AS dt_ocorr
		FROM
			servativ AS a
		LEFT JOIN
			exclus AS b ON a.mat_siape = b.siape
		LEFT JOIN
			tabsitcad AS c ON a.cod_sitcad = c.codsitcad
		LEFT JOIN
			tabcargo AS d ON a.cod_cargo = d.cod_cargo
		LEFT JOIN
			tabregime AS e ON a.reg_jur_at = e.cod_rj
		LEFT JOIN
			tabsetor AS f ON a.cod_lot = f.codigo
		LEFT JOIN
			tabsetor AS g ON a.cod_loc = g.codigo
		LEFT JOIN
			tabocorr AS h ON b.cod_ocorr = h.cod_ocorr
		WHERE
			mat_siape = '$matricula_siape' " . $pesquisa
);
$oServidor       = $oTbDados->fetch_object();
$Dataprev        = $oServidor->mat_dtp;
$Siapecad        = $oServidor->mat_siapecad;
$wdatinss        = $oServidor->dt_adm;
$dtnasc          = $oServidor->dtnasc;
$dtcarr          = $oServidor->dt_ing_car;
$datlot          = $oServidor->dt_ing_lot;
$matricula       = $oServidor->mat_siape;
$idunica         = $oServidor->ident_unica;
$wnome           = $oServidor->nome_serv;
$wcargo          = $oServidor->cod_cargo;
$wlota           = $oServidor->cod_lot;
$loca            = $oServidor->cod_loc;
$datloca         = $oServidor->dt_ing_loc;
$Codsit          = $oServidor->cod_sitcad;
$Regjur          = $oServidor->reg_jur_at;
$classe          = $oServidor->cod_classe;
$padrao          = $oServidor->cod_padrao;
$Jornada         = $oServidor->jornada;
$dtjorn          = $oServidor->dt_ing_jorn;
$Situacao        = $oServidor->descsitcad;
$cargo_descricao = $oServidor->desc_cargo;
$regime_juridico = $oServidor->desc_rj;
$wnomelota       = $oServidor->descricao_lotacao;
$nomelocal       = $oServidor->descricao;

$motivo = $oServidor->desc_ocorr;

$codoc  = $oServidor->cod_ocorr;
$dtexcl = $oServidor->dt_ocorr;

//convertendo datas para exibir
if ($dtexcl != "")
{
    $dtexcl = databarra($dtexcl);
}

// verifica se existe foto
// gravada em alguma máquina
//
$sFoto = retornaFoto($matricula);

$oTbDados->query("SELECT a.num_funcao, a.sit_ocup, a.dt_inicio, b.desc_func FROM ocupantes AS a INNER JOIN tabfunc AS b ON a.num_funcao = b.num_funcao WHERE a.mat_siape = '$matricula_siape' ORDER BY IF(a.sit_ocup='T',1,2) ");
$nrows_fun = $oTbDados->num_rows();
while ($oFuncao   = $oTbDados->fetch_object())
{
    $numero_da_funcao[]        = $oFuncao->num_funcao;
    $descricao_da_funcao[]     = $oFuncao->desc_func;
    $data_ocupacao_da_funcao[] = databarra($oFuncao->dt_inicio);
    switch ($oFuncao->sit_ocup)
    {
        case 'T': $situacao_ocupacao[] = 'TITULAR';
            break;
        case 'S': $situacao_ocupacao[] = 'SUBSTITUTO';
            break;
        case 'R': $situacao_ocupacao[] = 'INTERINO';
            break;
        default: $situacao_ocupacao[] = "";
            break;
    }
}

switch (substr($wcargo, 0, 3))
{
    case 434: $tipo_carreira = "SEGURO SOCIAL";
        break;
    case 424: $tipo_carreira = "PREVIDENCIARIA";
        break;
    case 810:
    case 811:
    case 812:
    case 435: $tipo_carreira = "PERITO MEDICO PREVIDENCIARIO";
        break;
    case 480:
    case 481:
    case 482: $tipo_carreira = "PGPE";
        break;
    default: $tipo_carreira = "OUTRAS";
        break;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro ');
$oForm->setCSS(_DIR_CSS_ . "print3b.css");
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setCSS(_DIR_CSS_ . "smoothness/jquery-ui-custom-px.min.css");



// Topo do formulário

$oForm->setSubTitulo("Consulta dados Funcionais");

// Topo do formulário
//
//	$oForm->exibeTopoHTML();
//	$oForm->exibeCorpoTopoHTML();
?>
<style>
    <!--
    h5 {
        margin-bottom:.0001pt;
        text-align:justify;
        page-break-after:avoid;
        font-size:8.0pt;
        font-family:"Times New Roman"; margin-left:0cm; margin-right:0cm; margin-top:0cm
    }
    .p1 { margin-top: 0; margin-bottom: 0; }
    .p2 { text-align: center; margin-top: 0; margin-bottom: 0; }
    -->
</style>
<table border="0" cellpadding="0" cellspacing="0" align='center'<?= ($sequencia != 0 ? "style='page-break-before: always'" : ""); ?>>
    <tr>
        <td>
            <fieldset class='fieldsetw' align='center' style='width: 90%; height: 100%;'>
                <table class="thin sortable draggable" border="1" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
                    <tr>
                        <td width="15%">
                            <p class='tahomaSize_1'>Nome:</p>
                            <p class="p1"><?= tratarHTML($wnome); ?></p>
                        </td>
                        <td width="15%">
                            <p align="center" class='tahomaSize_2'>Mat.Siape:</p>
                            <p class="p2"><?= tratarHTML($matricula); ?></p>
                        </td>
                        <td width="15%">
                            <p align="center" class='tahomaSize_2'>Mat.Siapecad:</p>
                            <p class='p2'><?= ($_SESSION["sRH"] == "S" ? tratarHTML($Siapecad) : "*******"); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p align="center" class='tahomaSize_2' nowrap>Cod. Cargo:</p>
                            <p class='p2'><?= tratarHTML($wcargo); ?></p>
                        </td>
                        <td>
                            <p align="center" class='tahomaSize_2'>Descri&ccedil;&atilde;o do Cargo:</p>
                            <p class='p2'><?= tratarHTML($cargo_descricao); ?></p>
                        </td>
                        <td><p class='p2'><img src="<?= tratarHTML($sFoto); ?>" width="60" height="100"></p></td>

                    </tr>

                    <tr>
                        <td>
                            <p align="center" class='tahomaSize_2'>Classe :</p>
                            <p class='p2'><?= tratarHTML($classe); ?></p>
                        </td>
                        <td>
                            <p align="center" class='tahomaSize_2'>Padrao :</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($padrao); ?>
                        </td>
                        <td>
                            <p align="center" class='tahomaSize_2'>Mat.Dataprev:</p>
                            <p class='p2'><?= tratarHTML($Dataprev); ?>'</p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p align="center" class='tahomaSize_2'>Cod. sit. :</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($Codsit); ?>
                        </td>
                        <td>
                            <p align="center" class='tahomaSize_2'>Situação Funcional:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($Situacao); ?>
                        </td>
                        <td >
                            <p align="center" class='tahomaSize_2'>Data de Nascimento:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= ($_SESSION["sRH"] == "S" ? tratarHTML($dtnasc) : "***********"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p align="center" class='tahomaSize_2'>Identifica&ccedil;&atilde;o Unica.:</font></p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= ($_SESSION["sRH"] == "S" ? tratarHTML($idunica) : "*********"); ?>
                        </td>

                        <td>
                            <p align="center" class='tahomaSize_2'>Regime Juridico.:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0;">
                                <?= tratarHTML($regime_juridico); ?>
                        </td>
                        <td align="center" >
                            <p align="center" class='tahomaSize_2'>Descri&ccedil;&atilde;o da fun&ccedil;&atilde;o:&nbsp;<?= $situacao_ocupacao[0]; ?></p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($numero_da_funcao[0]) . "-" . tratarHTML($descricao_da_funcao[0]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=3 align="center">
                            <p align="center" class='tahomaSize_2'>Ingresso na Fun&ccedil;&atilde;o.:</font></p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($data_ocupacao_da_funcao[0]); ?>
                        </td>
                    </tr>
                    <?php
                    if ($nrows_fun > 1)
                    {
                        ?>
                        <tr>
                            <td align="center" colspan="2">
                                <p align="center" class='tahomaSize_2'>Descri&ccedil;&atilde;o da fun&ccedil;&atilde;o:&nbsp;<?= tratarHTML($situacao_ocupacao[1]); ?></p>
                                <p align="center" style="margin-top: 0; margin-bottom: 0">
                                    <?= tratarHTML($numero_da_funcao[1]) . "-" . tratarHTML($descricao_da_funcao[1]); ?>
                            </td>
                            <td align="center">
                                <p align="center" class='tahomaSize_2'>Ingresso na Fun&ccedil;&atilde;o.:</p>
                                <p align="center" style="margin-top: 0; margin-bottom: 0">
                                    <?= tratarHTML($data_ocupacao_da_funcao[1]); ?>
                            </td>
                        </tr>
                        <?php
                    }

                    ?>
                    <tr>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>Ingresso no Órgão:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($wdatinss); ?>
                        </td>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>Ingresso na Carreira.:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($dtcarr); ?>
                        </td>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>Descri&ccedil;&atilde;o da carreira:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($tipo_carreira); ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>Jornada.:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0;">
                                <?= tratarHTML($Jornada); ?>
                        </td>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>Ingresso na Jornada.:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($dtjorn); ?>
                        </td>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>lota&ccedil;&atilde;o:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($wlota); ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" width="15%">
                            <p align="center" class='tahomaSize_2' nowrap>Descrição da lotação:</p>
                            <p style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML(chunk_split($wnomelota)); ?>
                        </td>
                        <td align="center" width="15%">
                            <p align="center" class='tahomaSize_2'>Ingresso na Lota&ccedil;&atilde;o.:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($datlot); ?>
                        </td>
                        <td width="15%">
                            <p align="center" class='tahomaSize_2'>localiza&ccedil;&atilde;o:</p>
                            <p align="center" style="margin-top: 0">
                                <?= tratarHTML($loca); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p align="center" class='tahomaSize_2'>Descrição da localização:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($nomelocal); ?>
                        </td>
                        <td>
                            <p align="center" class='tahomaSize_2'>Ingresso na Localiza&ccedil;&atilde;o.:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($datloca); ?>
                        </td>
                        <td align="center">
                            <p align="center" class='tahomaSize_2'>Motivo da exclus&atilde;o:</p>
                            <p align="center" style="margin-top: 0">
                                <?= tratarHTML($motivo); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=3 align="center">
                            <p align="center" class='tahomaSize_2'>Data da exclus&atilde;o:</p>
                            <p align="center" style="margin-top: 0; margin-bottom: 0">
                                <?= tratarHTML($dtexcl); ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>

<script>
    window.print();
</script>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

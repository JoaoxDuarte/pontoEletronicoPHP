<?php
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// numero da funcao - formulario
$nfuncao = anti_injection($_REQUEST['nfuncao']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// verifica quem é o titular e/ou substituto atual
$oDBase->query("SELECT a.sit_ocup AS situacao, a.mat_siape AS siape FROM ocupantes AS a WHERE a.num_funcao='$nfuncao' ORDER BY IF(a.sit_ocup='T',0,IF(a.sit_ocup='R',1,IF(a.sit_ocup='S',2,3))) ");
while ($oFuncao = $oDBase->fetch_object())
{
    switch ($oFuncao->situacao)
    {
        case 'T': $sTitular    = $oFuncao->siape;
            break;
        case 'S': $sSubstituto = $oFuncao->siape;
            break;
        case 'R':
        case 'I': $sRespondedo = $oFuncao->siape;
            break;
    }
}

// imagens indicando ocupante ativo e inativo
$imgAtivo   = _DIR_IMAGEM_ . 'ativar_on.gif';
$imgInativo = _DIR_IMAGEM_ . 'ativar_off.gif';

// seleciona historico de função
$oDBase->query("SELECT a.mat_siape, a.nome_serv, a.sit_ocup, a.num_funcao, DATE_FORMAT(a.dt_inicio, '%d/%m/%Y') AS dt_inicio, DATE_FORMAT(a.dt_fim, '%d/%m/%Y') AS dt_fim, b.cod_lot, b.cod_funcao, b.num_funcao, b.desc_func, b.cod_lot, c.descricao FROM historic AS a LEFT JOIN tabfunc AS b ON a.num_funcao=b.num_funcao LEFT JOIN tabsetor AS c ON b.cod_lot=c.codigo WHERE a.num_funcao='$nfuncao' ORDER BY IF(a.sit_ocup='T',0,IF(a.sit_ocup='R',1,IF(a.sit_ocup='S',2,3))), IF((a.dt_inicio<>'0000-00-00' AND a.dt_fim<>'0000-00-00') OR (a.dt_inicio<>'0000-00-00' AND a.dt_fim='0000-00-00'),a.dt_inicio,a.dt_fim) DESC ");

// verifica se existem registros
if ($oDBase->num_rows() == 0)
{
    //header("Location: mensagem.php?modo=18");
    replaceLink( $pagina_de_origem);
}

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Relatório » Gerencial » Histórico de Ocupantes' . $form_caminho);
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setSubtitulo("Hist&oacute;rico de Ocupantes de Funções");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<center>
    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
        <?php
        $quebra      = '';
        $cabecalho   = true;
        while ($pm_partners = $oDBase->fetch_object())
        {
            if ($cabecalho == true)
            {
                $numero    = $pm_partners->num_funcao;
                $descricao = $pm_partners->desc_func;
                $codlot    = $pm_partners->cod_lot;
                $descsetor = $pm_partners->descricao;
                $cabecalho = false;
                ?>
                <tr>
                    <td colspan="5" style='height: 50px; vertical-align: middle;'>
                        <p style='text-align: left; vertical-align: middle;'>
                            &nbsp;<strong> Setor: <?= tratarHTML(getUorgMaisDescricao( $codlot )); ?></strong><br>
                            &nbsp;&nbsp;<strong>Fun&ccedil;&atilde;o</strong>:&nbsp;<input type="text" id="siape" name="siape" size="7" class='alinhadoAEsquerda form-control' value="<?= tratarHTML($numero); ?>" readonly>
                            &nbsp;<strong>Descri&ccedil;&atilde;o</strong>:&nbsp;<input type="text" id="nome" name="nome" size="4" class='alinhadoAEsquerda form-control' value="<?= tratarHTML($descricao); ?>" readonly>
                        </p>
                        <table border='0'>
                            <tr>
                                <td>&nbsp;</td>
                                <td colspan='4' style='font-size: 10px; vertical-align: middle;'><img src='<?= tratarHTML($imgAtivo); ?>'>Ocupante atual</td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td style='font-size: 10px; vertical-align: middle;'><img src='<?= tratarHTML($imgInativo); ?>'>Ocupante anterior</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
            }

            switch ($pm_partners->sit_ocup)
            {
                case "T": $s = "TITULAR";
                    break;
                case "S": $s = "SUBSTITUTO";
                    break;
                case "R": $s = "INTERINO";
                    break;
            }

            if ($quebra == '' || $quebra != $s)
            {
                $quebra = $s;
                ?>
                <tr><td valign='bottom' colspan='6' style='text-indent: 20px; height: 35px;'>&nbsp;<i><b><?= tratarHTML($s); ?></b></i>&nbsp;</td></tr>
                <tr bgcolor="#DBDBB7" height='16px' valign='middle'>
                    <td width="54%" style='font-size: 11px;'>&nbsp;<b>OCUPANTES</b></td>
                    <td width="9%" align="center" style='font-size: 11px;'>&nbsp;<b>SIAPE</b></div></td>
                    <td width="9%" align="center" style='font-size: 11px;'>&nbsp;<b>SITUA&Ccedil;&Atilde;O</b></div></td>
                    <td width="14%" align="center" style='font-size: 11px;'>&nbsp;<b>DATA DE INGRESSO</b></td>
                    <td width="14%" align="center" style='font-size: 11px;'>&nbsp;<b>DATA DE SA&Iacute;DA</b></td>
                </tr>
                <?php
            }

            $imgExibir = $imgAtivo;
            if ($sTitular == $pm_partners->mat_siape && $pm_partners->sit_ocup == 'T')
            {
                $sTitular = '';
            }
            elseif ($sSubstituto == $pm_partners->mat_siape && $pm_partners->sit_ocup == 'S')
            {
                $sSubstituto = '';
            }
            elseif ($sRespondedo == $pm_partners->mat_siape && substr_count('TS', $pm_partners->sit_ocup) == 0)
            {
                $sRespondedo = '';
            }
            else
            {
                $imgExibir = $imgInativo;
            }
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='14px' valign='middle'>
                <td valign='' width="54%">&nbsp;<img src='<?= tratarHTML($imgExibir); ?>'>&nbsp;<font size="1" face="Tahoma"><?= tratarHTML($pm_partners->nome_serv); ?></font></td>
                <td valign='middle' width="9%" align="center"><font size="1" face="Tahoma"><?= tratarHTML(removeOrgaoMatricula($pm_partners->mat_siape)); ?></font></td>
                <td valign='middle' width="9%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($s); ?></font></td>
                <td valign='middle' width="14%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->dt_inicio); ?></font></td>
                <td valign='middle' width="14%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->dt_fim); ?></font></td>
            </tr>
            <?php
        } // fim do while
        ?>
    </table>
    <br>
    <table border='0' align='center' style='border: 0 solid white;'>
        <tr style='border: 0 solid white;'>
            <td style='border: 0 solid white;'>
                <div class="col-md-12 text-center">
                    <a class="btn btn-primary btn-primary" id="btn-continuar" href="/sisref/histfuncserv2.php">
                    <span class="glyphicon glyphicon-ok"></span> &nbsp;Voltar&nbsp;</a>
                </div>
            </td>
        </tr>
    </table>
</center>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

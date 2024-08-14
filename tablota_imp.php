<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao('tabela_feriados');

// instancia o banco de dados
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Tabelas » Lotações » Pesquisa de Unidades de Lotação');
$oForm->setCSS(_DIR_CSS_ . 'print3b.css');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Relação das Unidades de Lotação");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


$pesquisa = $_SESSION['sSQLPesquisa'];

if ($pesquisa == "")
{
    mensagem("É obrigatório informar o critério de pesquisa!");
    close();
}
else
{
    $pesquisa .= ($_SESSION['sColunaSortTable'] == "" ? "" : "ORDER BY " . $_SESSION['sColunaSortTable']);

    $sequencia = 0;

    $oDBase->query($pesquisa);
    $nRows    = $oDBase->num_rows();
    $nPaginas = ($nRows > 77 ? round($nRows / 77 + 1) : 1);
    $nPagina  = 1;

    while ($pm_partners = $oDBase->fetch_object())
    {
        if ($sequencia == 0 || ($sequencia % 77) == 0)
        {
            if ($sequencia > 0)
            {
                ?>
                </table>
                </fieldset>
                </td>
                </tr>
                </table>
                <?php
            }
            ?>
            <table border="0" cellpadding="0" cellspacing="0" align='center'<?= ($sequencia != 0 ? "style='page-break-before: always'" : ""); ?>>
                <tr>
                    <td>
                        <fieldset class='fieldsetw' align='center' style='width: 100%; height: 100%;'>
                            <table class="thin sortable draggable" border="1" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
                                <tr>
                                    <td colspan='7'>
                                        <table border='0' width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td><img  src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' height='40' width='105' border='0'></td>
                                                <td style='height: 60px; vertical-align: middle;'>
                                                    <p align="center" class='tahomaSISREF_4'>SISREF - Sistema de Registro Eletrônico de Frequência</p>
                                                </td>
                                                <td><img  src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' height='40' width='105' border='0'><font style='font-size: 10px; font-weight: bold; text-align: right;'>&nbsp;<?= number_format($nPagina++, 0, ',', '.') . ' / ' . number_format($nPaginas, 0, ',', '.'); ?>&nbsp;</font></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr class='ui-widget-header'>
                                    <td width="7%"  align="center">&nbsp;</td>
                                    <td width="10%" align="center" class='tahomaSize_1'><b>&nbsp;CÓDIGO&nbsp;</b></td>
                                    <td width="50%" align="center" class='tahomaSize_1'><b>&nbsp;DESCRIÇÃO&nbsp;</b></td>
                                    <td width="10%" align="center" class='tahomaSize_1_B'><b>&nbsp;UORG&nbsp;</b></td>
                                    <td width="10%" align="center" class='tahomaSize_1'><b>&nbsp;UPAG&nbsp;</b></td>
                                    <td width="10%" align="center" class='tahomaSize_1'><b>&nbsp;UG&nbsp;</b></td>
                                    <td width="10%" align="center" class='tahomaSize_1'><b>&nbsp;ATIVO&nbsp;</b></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                                <td width="7%" align="center" class='tahomaSize_1'>&nbsp;<?= ($sequencia + 1); ?>&nbsp;</td>
                                <td width="10%" align="center" height='16px'><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->codigo); ?></font></td>
                                <td width="50%" align="left"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->descricao); ?></font></td>
                                <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->cod_uorg); ?></font></td>
                                <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->upag); ?></font></td>
                                <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->ug); ?></font></td>
                                <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->ativo); ?></font></td>
                            </tr>
                            <?php
                            $sequencia++;
                        } // fim do while
                        ?>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
    <script>
        window.print();
    </script>
    <?php
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

<?php
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH ou Chefia');

$pesquisa = $_SESSION['sSQLPesquisa'];

if ($pesquisa == "")
{
    mensagem("É obrigatório informar o critério de pesquisa!");
    close();
}
else
{
    $pesquisa  .= ($_SESSION['sColunaSortTable'] == "" ? "" : "ORDER BY " . $_SESSION['sColunaSortTable']);
    $sequencia = 0;
    $oTbDados  = new DataBase('PDO');
    $oTbDados->query($pesquisa);
    $nRows     = $oTbDados->num_rows();
    $nPaginas  = ($nRows > 77 ? round($nRows / 77 + 1) : 1);
    $nPagina   = 1;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro » Funcional » Consultar » Impressão');
$oForm->setCSS(_DIR_CSS_ . "print3b.css");
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setCSS(_DIR_CSS_ . "smoothness/jquery-ui-custom-px.min.css");

$oForm->setJS(_DIR_JS_ . "funcoes.js");

$oForm->setSeparador(0);


while ($pm_partners = $oTbDados->fetch_object())
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
                                <td width="7%" align="center">&nbsp;</td>
                                <td width="9%" align="center" class='tahomaSize_1'><b>&nbsp;SIAPE&nbsp;</b></td>
                                <td width="50%" class='tahomaSize_1'><b>&nbsp;NOME&nbsp;</b></td>
                                <td width="10%" align="center" class='tahomaSize_1'><b>&nbsp;CPF&nbsp;</b></td>
                                <td width="10%" align="center" class='tahomaSize_1'><b>&nbsp;CARGO&nbsp;</b></td>
                                <td width="10%"  align="center" class='tahomaSize_1'><b>&nbsp;LOTAÇÃO&nbsp;</b></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                            <td width="7%" align="center" class='tahomaSize_1'>&nbsp;<?= ($sequencia + 1); ?>&nbsp;</td>
                            <td width="9%" align="center" class='tahomaSize_1'>&nbsp;<?= tratarHTML($pm_partners->mat_siape); ?>&nbsp;</td>
                            <td width="50%" class='tahomaSize_1'>&nbsp;<?= tratarHTML($pm_partners->nome_serv); ?>&nbsp;</td>
                            <td width="10%" align="center" class='tahomaSize_1'>&nbsp;<?= tratarHTML($pm_partners->cpf); ?>&nbsp;</td>
                            <td width="10%" align="center" class='tahomaSize_1'>&nbsp;<?= tratarHTML($pm_partners->cod_cargo); ?>&nbsp;</td>
                            <td width="10%" align="center" class='tahomaSize_1'>&nbsp;<?= tratarHTML($pm_partners->cod_lot); ?>&nbsp;</td>
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

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

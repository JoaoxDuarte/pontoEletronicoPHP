<?php
// conexao ao banco de dados
// funcoes diversas
include_once("config.php");

verifica_permissao("sRH e sTabServidor");
//$_SESSION['sLotacao'] = '04001000';
// informar tipo da origem
// se rh.php, chefia.php ou entrada.php
$_SESSION['sHOrigem_1'] = "principal_abertura.php";

// instancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setTransitional(false);
$oForm->setCaminho('Relatório de Ajustes');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setDialogModal();

$oForm->setLargura('950px');

$oForm->setSeparador(20);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// caixa de mensagens - dialog
defineArea();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


##
#
#  Define local de exibicao dos dados
#
##

function defineArea()
{
    ?>
    <script>
        var w = window.screen.availWidth;
        var h = window.screen.availHeight;
        var popup = $('<div id="popup123" class="dialog" style="vertical-align: middle; text-align: center;"><img id="load" src="<?= _DIR_IMAGEM_; ?>large-loading.gif" alt="lendo..."/></div>').prependTo('body');
        popup.prepend('<iframe id="feature" style="display:none; width: ' + 930 + '; height: ' + (h - 325) + '; text-align: center;" class="dialogIFrame"></iframe>');
        var $iFrame = $('iframe');

        $iFrame.load(function ()
        {
            $('.dialogIFrame').show();
            $('#load').hide();
        });

        $('.dialogIFrame').attr("src", 'ajustes_via_sistema_relatorio.php');

        popup.dialog({
            modal: true,
            title: 'Relatório Ajustes',
            width: 970,
            height: (h - 210),
            closeOnEscape: false,
            close: function ()
            {
                $(this).dialog("close");
                window.history.back();
            },
            buttons: {
                "Imprimir": function ()
                {
                    window.frames['feature'].focus();
                    window.frames['feature'].print();
                }/*,
                 "Fechar": function() { $(this).dialog("close"); window.history.back(); }*/
            }
        });
    </script>
    <?php

}

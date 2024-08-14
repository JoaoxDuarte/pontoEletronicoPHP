<?php
include_once("config.php");

verifica_permissao("tabela_prazos");

// parametros enviados por formulario
$id = anti_injection($_REQUEST['id']);

// instancia o BD
$oDBase = new DataBase('PDO');

//	seleciona os dados
$oDBase->query("
SELECT
    id,
    compi,
    DATE_FORMAT(rhi,'%d/%m/%Y')      AS rhi,
    DATE_FORMAT(rhf,'%d/%m/%Y')      AS rhf,
    DATE_FORMAT(apsi,'%d/%m/%Y')     AS apsi,
    DATE_FORMAT(apsf,'%d/%m/%Y')     AS apsf,
    DATE_FORMAT(gbnini,'%d/%m/%Y')   AS gbnini,
    DATE_FORMAT(gbninf,'%d/%m/%Y')   AS gbninf,
    DATE_FORMAT(outchei,'%d/%m/%Y')  AS outchei,
    DATE_FORMAT(outchef,'%d/%m/%Y')  AS outchef,
    DATE_FORMAT(rmi,'%d/%m/%Y')      AS rmi,
    DATE_FORMAT(rmf,'%d/%m/%Y')      AS rmf,
    DATE_FORMAT(cadi,'%d/%m/%Y')     AS cadi,
    DATE_FORMAT(cadf,'%d/%m/%Y')     AS cadf,
    DATE_FORMAT(hveraoi,'%d/%m/%Y')  AS hvi,
    DATE_FORMAT(hveraof,'%d/%m/%Y')  AS hvf,
    DATE_FORMAT(recessoi,'%d/%m/%Y') AS recessoi,
    DATE_FORMAT(recessof,'%d/%m/%Y') AS recessof,
    DATE_FORMAT(qcinzas,'%d/%m/%Y')  AS qcinzas,
    DATE_FORMAT(recesso_inicio_compensacao,'%d/%m/%Y') AS recesso_inicio_compensacao,
    DATE_FORMAT(recesso_fim_compensacao,'%d/%m/%Y')    AS recesso_fim_compensacao,
    ativo
FROM
    tabvalida
WHERE
    id= :id
" ,
array(
    array( ':id', $id, PDO::PARAM_INT )
));
$oPeriodo                   = $oDBase->fetch_object();
$mesi                       = substr($oPeriodo->compi, 0, 2) . "/" . substr($oPeriodo->compi, 2, 4);
$rhi                        = $oPeriodo->rhi;
$rhf                        = $oPeriodo->rhf;
$apsi                       = $oPeriodo->apsi;
$apsf                       = $oPeriodo->apsf;
$gbnini                     = $oPeriodo->gbnini;
$gbninf                     = $oPeriodo->gbninf;
$outchei                    = $oPeriodo->outchei;
$outchef                    = $oPeriodo->outchef;
$rmi                        = $oPeriodo->rmi;
$rmf                        = $oPeriodo->rmf;
$cadi                       = $oPeriodo->cadi;
$cadf                       = $oPeriodo->cadf;
$hveraoi                    = $oPeriodo->hvi;
$hveraof                    = $oPeriodo->hvf;
$recessoi                   = $oPeriodo->recessoi;
$recessof                   = $oPeriodo->recessof;
$qcinzas                    = $oPeriodo->qcinzas;
$recesso_inicio_compensacao = $oPeriodo->recesso_inicio_compensacao;
$recesso_fim_compensacao    = $oPeriodo->recesso_fim_compensacao;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Utilitários » Gestores » Prazos");
$oForm->setLargura('650');

// Topo do formulário
//
$oForm->setSubTitulo("Permissão de Acesso (Prazos/Períodos)");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script language="javascript">
    $(document).ready(function ()
    {
        $('#btn-enviar').on('click', function (e)
        {
            testa();
        });
    });

    function testa()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var comp1 = $('#comp1');
        var rhi   = $('#rhi');
        var rhf   = $('#rhf');
        var apsi  = $('#apsi');
        var apsf  = $('#apsf');

        if (data_valida('01/' + comp1.val()) == false)
        {
            oTeste.setMsg("Competência inválida!", comp1);
        }
        if (data_valida(rhi.val()) == false)
        {
            oTeste.setMsg("RH: Data início inválida!", rhi);
        }
        if (data_valida(rhf.val()) == false)
        {
            oTeste.setMsg("RH: Data término inválida!", rhf);
        }
        if (data_valida(apsi.val()) == false)
        {
            oTeste.setMsg("Chefia: Data início inválida!", apsi);
        }
        if (data_valida(apsf.val()) == false)
        {
            oTeste.setMsg("Chefia: Data término inválida!", apsf);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        if (bResultado)
        {
            // dados
            var form_dados = $('#form1').serialize();
            var destino    = "grava.php";

            // mensagem processando
            showProcessandoAguarde();

            //create the ajax request
            $.ajax({
                url: destino,
                type: "POST",
                data: form_dados,
                dataType: "json"

            }).done(function(resultado) {
                console.log(resultado.mensagem);
                hideProcessandoAguarde();
                mostraMensagem(resultado.mensagem, resultado.tipo);

            }).fail(function(jqXHR, textStatus ) {
                console.log("Houve um problema interno: " + textStatus);
                hideProcessandoAguarde();
                mostraMensagem("Houve um problema interno: " + textStatus, "danger");

            }).always(function() {
                console.log("completou");
                hideProcessandoAguarde();

            });
        }

        return bResultado;
    }
</script>

<center>
    <form action="#" method="post" id="form1" name="form1" onsubmit="return false;">
        <input type='hidden' id='id'   name='id' value='<?= tratarHTML($id); ?>'>
        <input type='hidden' id='modo' name='modo' value='5'>
        <table class="table table-striped table-condensed table-bordered text-center" style="width:70%">
            <tr>
                <td colspan="3" height="30px" bgcolor="#DFDFBF" align="center"><label style="padding-top:8px;background-color:#DFDFBF;">COMPETÊNCIA</label></td>
            </tr>
            <tr>
                <td colspan="3" bgcolor="#DFDFBF" align="center">
                    <input type="text" id="comp1" name="comp1"class='form-control' OnKeyPress="formatar(this, '##/####')" value="<?= tratarHTML($mesi); ?>" size="7" maxlength="7" style="width:90px;" readonly>
                </td>
            </tr>
            <tr>
                <td width="35%"><label style="padding-top:8px;">Recursos Humanos - Verificação</label></td>
                <td width="28%" align="center">
                    <input type="text" id="rhi" name="rhi" class='form-control' OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($rhi); ?>" size="10" maxlength='10' style="width:110px;">
                </td>
                <td width="29%" align="center">
                    <input type="text" id="rhf" name="rhf" size="10" maxlength='10' class='form-control' OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($rhf); ?>" size="10" maxlength='10' style="width:110px;">
                </td>
            </tr>
            <tr>
                <td width="35%"><label style="padding-top:8px;">Homologação (Chefias)</label></td>
                <td width="28%" align="center">
                    <input type="text" id="apsi" name="apsi" class='form-control' OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($apsi); ?>" size="10" maxlength='10' style="width:110px;">
                </td>
                <td width="29%" align="center">
                    <input type="text" id="apsf" name="apsf" class='form-control' OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($apsf); ?>" size="10" maxlength='10' style="width:110px;">
                </td>
            </tr>
        </table>

        <div class="row col-md-12">
            <div class="col-md-5"></div>
            <div class="col-md-2">
                <label class="control-label" for="dnu">&nbsp;</label>
                <button type="button" id="btn-enviar" name="enviar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Gravar
                </button>
            </div>
            <div class="col-md-5"></div>
        </div>

        </center>
    </form>
    <?php

    // Base do formulário
    //
    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();

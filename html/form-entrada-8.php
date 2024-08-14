<div class="container">
    <div class="row" id="login">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <div class="col-md-12 subtitle">
            <h6 class="lettering-tittle uppercase"><strong>Consulta Frequência de Meses Anteriores</strong></h6>
        </div>
        <div class="col-md-12 margin-bottom-25"></div>

        <form id="form1" name="form1" method="POST">

            <!-- Inputs Hidden usados pelo form -->
            <input type='hidden' id='pSiape' name='pSiape' value="<?= tratarHTML($pSiape); ?>">
            <input type='hidden' id='cmd'    name='cmd'    value="<?= tratarHTML($cmd); ?>">
            <input type='hidden' id='orig'   name='orig'   value="<?= tratarHTML($orig); ?>">
            <input type='hidden' id='an'     name='an'     value="<?= date('Y'); ?>">

            <div class="col-md-3 col-md-offset-4">
                <table class="table table-striped table-condensed table-bordered text-center">
                    <tbody>
                        <tr height='25'>
                            <td class='text-center'>
                                Mês<br>
                                <input type="text" id="mes" name="mes" class="form-control text-center" size="2" maxlength="2">
                            </td>
                            <td class='text-center'>
                                Ano<br>
                                <input type="text" id="ano" name="ano" class="form-control text-center" size="4" maxlength="4">
                            </td>
                        </tr>
                        <tr height='25'>
                            <td colspan="2" class='text-center'>
                                <p style="font-size:9px;padding:0px;margin:0px;">
                                    Utilizar apenas competências a partir de 12/2017.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-group col-md-8 text-center">
                    <div class="col-md-7 col-md-offset-3 margin-10">
                        <div class="col-md-6 text-right">
                            <a class="btn btn-success btn-primary" id="btn-enviar">
                                <span class="glyphicon glyphicon-ok"></span> Continuar 
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div style="text-align:right;width:95%;margin:25px;font-size:10px;border:0px;">
                    <fieldset style="border:1px solid white;text-align:left;">
                        <legend style="font-size:13px;padding:0px;margin:0px;"><b>&nbsp;Informações&nbsp;</b></legend>
                        <p style="padding:1px;margin:0px;">
                            <b>Mês&nbsp;:&nbsp;</b>Mês da frequência desejada;
                        </p>
                        <p style="padding:1px;margin:0px;">
                            <b>Ano&nbsp;:&nbsp;</b>Ano da frequência desejada.
                        </p>
                    </fieldset>
                </div>
            </div>

        </form>

    </div>
</div>

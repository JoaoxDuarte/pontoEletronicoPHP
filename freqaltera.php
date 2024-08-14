<?php
include_once("config.php");

verifica_permissao("sRH e sTabServidor");

$sMatricula = $_SESSION['sMatricula'];

$ano = date('Y');
$mes = date('m');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Atualizar » Mes corrente » Alterar ocorrência');
$oForm->setOnLoad("javascript: if($('#mat')) { $('#mat').focus() };");

$oForm->setSubTitulo("Alteração de Ocorr&ecirc;ncia");
$oForm->setObservacaoTopo("Utilize essa op&ccedil;&atilde;o para alterar ocorr&ecirc;ncias no m&ecirc;s corrente");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script language="javascript">
    function validar()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var mat = $('#mat');
        var usu = $('#usu');

        var mensagem = '';

        // validacao do campo siape
        // testa o tamanho
        mensagem = validaSiape(mat.val());
        if (mensagem != '')
        {
            oTeste.setMsg(mensagem, mat);
        }

        if (mat.val() == usu.val())
        {
            oTeste.setMsg('Você não pode alterar sua própria frequência!', mat);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }
</script>
    <form id="form1" name="form1" method="post" action="regfreq6.php" onsubmit="return validar()">
        <div class="text-center ">
            <input name="usu" type="hidden" id="usu" value="<?= tratarHTML($sMatricula); ?>">
            <input type="hidden" name="an"  id='an'   value="<?= tratarHTML($ano); ?>">
            <input type="hidden" name="mes" id="mes"  value="<?= tratarHTML($mes); ?>">
            <input type="hidden" name="ano" id="ano"  value="<?= tratarHTML($ano); ?>">
            <input type="hidden" name="ano" id="anot" value="<?= tratarHTML($ano); ?>">
            <input type="hidden" name="cmd" id="cmd"  value="1">

            <!-- Text input-->
            <div class="row">
                <label class="control-label" for="name">
                    Matrícula          :                          </label>
            </div>
            <div class="row col-md-3 col-md-offset-5" style="padding:0px 0px 0px 43px;">
                <input type="text" id="mat" name="mat" class="form-control" size="7" maxlength="7" value="" onkeyup="javascript:ve(this.value);" style="width:85px">



                <div class="col-md-12 margin-25">
                    <div class="text-left">

                        <button type="submit" class="btn btn-sucess  btn-primary" id="btn-continuar">
                            <span class="glyphicon glyphicon-ok"></span> OK
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </form>

<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

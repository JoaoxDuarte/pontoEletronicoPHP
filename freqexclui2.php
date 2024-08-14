<?php
include_once("config.php");

verifica_permissao("sRH e sTabServidor");

$sMatricula = $_SESSION['sMatricula'];

if (isset($dia))
{
    $dia = anti_injection($_REQUEST['dia']);
}
else
{
    $data = new trata_datasys();
    $mes  = $data->getMesHomologacao();
    $ano  = $data->getAnoHomologacao();
    $dia  = '01/' . $mes . '/' . $ano;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Atualizar » Mes em Homologação » Excluir Ocorrência');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setJS("
    <script>
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
                oTeste.setMsg( 'É obrigatório informar a matrícula com no mínimo 7 caracteres!', mat);
            }

            if (mat.val() == usu.val()) { oTeste.setMsg( 'Você não pode excluir sua própria frequência!', mat); }

            // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
            var bResultado = oTeste.show();

            return bResultado;
        }
    </script>
");
$oForm->setOnLoad("javascript: if($('#mat')) { $('#mat').focus() };");

$oForm->setSubTitulo("Exclus&atilde;o de registro err&ocirc;neo");
$oForm->setObservacaoTopo("Informe o siape");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form  action="veponto3.php" method="POST" id="form1" name="form1" onSubmit="return validar()">
    <input type="hidden" id="dia"   name="dia"   value="<?= tratarHTML($dia); ?>">
    <input type="hidden" id="usu"   name="usu"   value="<?= tratarHTML($sMatricula); ?>">
    <input type="hidden" id="dados" name="dados" value="<?= base64_encode($sMatricula . ":|:" . $dia); ?>">

    <p align="center"><h3>
        <div align="center">


                <input type="text" id="mat" name="mat" class="form-control margin-bottom-10" size="7" maxlength="7" value="" onkeyup="javascript:ve(this.value);" style="width:85px">




                    <div class="text-center">

                        <button type="image" class="btn btn-sucess  btn-primary" name="enviar" alt="Submeter os valores"  id="btn-continuar">
                            <span class="glyphicon glyphicon-ok"></span> OK
                        </button>
                    </div>







        </div>

</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

<?php

include_once( "config.php");

// verifica_permissao("sAPS");

$upagsContexto = array();
$oDBaseContexto = new DataBase('PDO');

if($_SESSION['sAdmCentral'] == 'S'){
    $oDBaseContexto->query("SELECT upag, descricao FROM tabsetor WHERE codigo = upag GROUP BY upag");
}else{
    $oDBaseContexto->query("SELECT upag, descricao FROM tabsetor WHERE SUBSTRING(upag, 1, 5) = '".$_SESSION['orgao']."' AND codigo = upag GROUP BY upag");
}

while($linhaContexto = $oDBaseContexto->fetch_array()){
    $arr = array( 
        'codigo' => $linhaContexto['upag'],
        'descricao' => $linhaContexto['descricao']
    );
    $upagsContexto[] = $arr;
}


// Monta as upags para serem selecionadas
$options = "<option>--Selecione a UPAG--</option>";
foreach($upagsContexto as $upagContexto){
    $options .= "<option value='".$upagContexto['codigo']."'>".$upagContexto['codigo'] .' - ' .$upagContexto['descricao']."</option>";
}

$html = "<div class='modal fade' id='troca-contexto-upag' role='dialog'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <form action='trocar_contexto_upag.php' method='post'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                            <h4 class='modal-title'>Confirmação</h4>
                        </div>
                        <div class='modal-body'>
                            <select class='form-control' name='upagContexto'>
                                {$options}
                            </select>
                        </div>
                        <div class='modal-footer'>
                            <button type='submit' class='btn btn-default save' >Trocar Upag</button>
                            <button type='button' class='btn btn-default cancel' data-dismiss='modal'>Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>";


$oForm = new formPadrao;
$oForm->setCaminho("Gestão Estratégica");
$oForm->setJSSelect2();
$oForm->setSubTitulo("Trocar Contexto de UPAG");
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
  var id = "";
$(document).ready(function ()
{
    var dados_visualizar = "";

    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma unidade";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });
});
</script>

<div class="container">
    <form method="post" action="trocar_contexto_upag.php">
        <div class="row">
            <div class="col-md-8">
                <select class='form-control select2-single' name='upagContexto'>
                    <?php echo $options; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type='submit' class='btn btn-default save' >Trocar Upag</button>
            </div>
        </div>
    </form>
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

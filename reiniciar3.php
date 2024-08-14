<?php

// pega o conteudo dos campos para alterar
include ("config.php");

// parametros
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $cpf = anti_injection($_REQUEST['cpf']);
}
else
{
    $dados = explode(':|:',base64_decode($dadosorigem));
    $cpf   = limpaCPF_CNPJ($dados[0]);
}

// dados do usuário
$oServativ = selecionaDadosDoUsuarioReiniciar($cpf);

$siape   = $oServativ->mat_siape;
$nome    = $oServativ->nome_serv;
$cpf     = $oServativ->cpf;
$idunica = $oServativ->ident_unica;
$dt_nasc = $oServativ->dt_nasc;
$email   = $oServativ->email;
$lotacao = $oServativ->cod_lot;
$upag    = $oServativ->upag;

// dados para reinicializar a senha
$_SESSION['sReiniciaSenha'] = $siape . '|' . $cpf . '|' . $idunica . '|' . $dt_nasc . '|' . $email . '|' . $lotacao . '|' . $upag;



// formulário
$title = _SISTEMA_SIGLA_ . ' | Reinicar Senha';

$oForm = new formPadrao();
$oForm->setSubTitulo( "Reiniciar Senha" );
$oForm->setJSDialogProcessando();
$oForm->setJS(" 
<script>
    var voltarOrigem = 'entrada.php';
</script>
");
$oForm->setJS("js/phpjs.js");
//$oForm->setJS("reiniciar3.js");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script>
$(document).ready(function (){

    $('#btn-enviar').on('click', function ()
    {
        return enviarDados();
    });

    $('input[type="text"]').keyup(function (e)
    {
        e.preventDefault();

        var $this    = $(this);
        var mensagem = "";

        // verifica matrícula siape
        verificaMatriculaSiape($this);

        // verifica identificação única
        verificaIdentificacaoUnica($this);
    });

    $('#lSiape').focus();
    
    $('[name="enviar"]').on('click', function (e)
    {
        if (enviarDados())
        {
            return true;
        }

        return false;
    });

    $('input').keypress(function (e)
    {
        if (e.which === 13 || e.keyCode === 13)
        {
            if (enviarDados())
            {
                return true;
            }
            return false;
        }
    });

    $('#cpf').focus();
});


// verifica matrícula siape
function verificaMatriculaSiape($this)
{
    if ($this.attr("id") === 'siape' && $this.val().length >= 7)
    {
        var msg = validaSiape($this.val()); 

        if (msg !== "")
        {
            mostraMensagem(msg, 'danger');
            return false;
        }
        else
        {
            $('#idunica').focus();
        }
    }
    
    return true;
}


// verifica identificação única
function verificaIdentificacaoUnica($this)
{
    if ($this.attr("id") === 'idunica' && $this.val().length >= 9)
    {
        var msg = validaIdUnica($this.val()); 

        //if (msg !== "")
        //{
        //    mostraMensagem(msg, 'danger');
        //    return false;
        //}
        //else
        //{
            $('#dt_nasc').focus();
        //}
    }
    
    return true;
}


/*-------------------------------------------------------\
 |     Reinica a senha                                   |
 \-------------------------------------------------------*/
function enviarDados()
{
    // mensagem processando
    showProcessando();
     
    //create the ajax request
    var oForm   = $('#form1');
    var destino = "confirmareiniciar.php";
    var dados   = oForm.serialize();
        
    $.ajax({
        
        url:  destino, // a pagina que sera chamada
        type: "POST",
        data: dados, // dados enviados
        dataType: "json"

    }).done(function(resultado) {
        
        console.log(resultado.mensagem + ' | ' + resultado.tipo);

        if (resultado.tipo === 'success')
        {
            mostraMensagem(resultado.mensagem, resultado.tipo, 'entrada.php', null);
        }
        else
        {
            mostraMensagem(resultado.mensagem, resultado.tipo, null, null);
        }
        hideProcessando();
        return (resultado.tipo === 'success');

    }).fail(function(jqXHR, textStatus ) {
        
        console.log("Request failed: " + textStatus);
        hideProcessando();
        return false;

    }).always(function() {
        
        console.log("completou");
        hideProcessando();
        return false;
    
    });

    return false;
}
</script>

<form class="form-horizontal margin-30" method="POST" id="form1" name="form1" action="#" onSubmit="javascritp:return false;">
    <div class="col-md-8 col-md-offset-2">
        <div class="form-group">
            <p class="text-justify">
                Para reiniciar senha informe os dados solicitados.
            </p>
            <p class="text-justify">
                Após a reinicialização da senha, será encaminhado um email para o detentor da matrícula, informando que sua senha foi reinicializada para a senha padrão (data de nascimento - ddmmaaaa).
            </p>
        </div>
        
        <div class="form-group">
            <div class="col-md-3">
                <label for="pSiape" class="control-label" style="padding:0px;">Usuário</label>
            </div>
            <div class="col-md-9">
                <?= tratarHTML($nome); ?>
            </div>
        </div>
       
        <div class="form-group">
            <div class="col-md-3">
                <label for="lot" class="control-label" style="padding:0px;">Órgão</label>
            </div>
            <div class="col-md-9">
                <?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-md-3">
                <label for="lot" class="control-label" style="padding:0px;">Unidade</label>
            </div>
            <div class="col-md-9">
                <?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-md-3">
                <label for="siape" class="control-label">SIAPE</label>
            </div>
            <div class="col-md-3">
                <input type="text" id="siape" name="siape" maxlength="7" title="SIAPE deve conter 7 caracteres numéricos" class="form-control" onkeyup="javascript:ve(this.value);">
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-md-3">
                <label for="idunica" class="control-label text-nowrap">Identificação Única</label>
            </div>
            <div class="col-md-3">
                <input type="text" id="idunica" name="idunica" maxlength="9" title="Identificação Única deve conter 9 caracteres numéricos" class="form-control" onkeyup="javascript:ve(this.value);">
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-3">
                <label for="dt_nasc" class="control-label text-nowrap">Data de Nascimento</label>
            </div>
            <div class="col-md-3">
                <input type="text" id="dt_nasc" name="dt_nasc" maxlength="8" title="Data de nascimento do usuário" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-5 col-xs-6 col-md-offset-1">
                <button type="submit" name="enviar" id="btn-reiniciar-senha" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> OK
                </button>
            </div>
            <div class="col-md-5 col-xs-6">
                <a class="btn btn-danger btn-block" href="reiniciar.php" id="btn-voltar" role="button">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>
</form>
<?php

DataBase::fechaConexao();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/* ************************************************ *
 *                                                  *
 *              FUNÇÕES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */

/*
 * @info Total de servidores/estagiarios por UPAG
 *
 * @param string $cpf  CPF do usuário
 * @return object Dados selecionados 
 *
 * @author Edinalvo Rosa
 */
function selecionaDadosDoUsuarioReiniciar($cpf)
{
    // banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino('reiniciar.php'); // em caso de erro
    $oDBase->setMensagem('Problemas na seleção dos dados do usuário!');

    // dados do usuário
    $oDBase->query("
    SELECT
        servativ.mat_siape, 
        servativ.nome_serv, 
        servativ.cpf, 
        servativ.ident_unica, 
        DATE_FORMAT(servativ.dt_nasc, '%d%m%Y') AS dt_nasc,
        servativ.email,
        servativ.cod_lot, 
        tabsetor.upag
    FROM
        servativ
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    WHERE
        servativ.excluido = :excluido
        AND servativ.cpf = :cpf
        AND servativ.cod_sitcad NOT IN ('02','08','15')
    ", array(
        array(":cpf",      $cpf, PDO::PARAM_STR),
        array(":excluido", 'N',  PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        retornaErro('reiniciar.php', 'Usuário não cadastrado, cedido ou excluído!', false);
    }

    return $oDBase->fetch_object();
}

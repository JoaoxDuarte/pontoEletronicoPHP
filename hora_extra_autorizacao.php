<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once("hora_extra_autorizacao_funcoes.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

/**
 * @class Hora Extra - Serviço Exgtraordinário
 *
 * @author Edinalvo Rosa
 */
class HoraExtraModel {

    private $chave;
    private $escolha;

    private $primeira_vez;
    
    private $var1;
    private $var2;

    private $id;
    private $delete;
    private $nome;


    /**
     * @method Construtor
     */
    public function __construct()
    {
        $this->argumentosGet();
    }

    /**
     * @info Carrega contéudo de "chave"
     *
     * @param void
     * @return string
     */
    public function getChave()
    {
        return $this->chave;
    }

    /**
     * @info Carrega contéudo de "escolha"
     *
     * @param void
     * @return string
     */
    public function getEscolha()
    {
        return $this->escolha;
    }

    /**
     * @info Carrega contéudo de "var1"
     *
     * @param void
     * @return string
     */
    public function getChaveVar1()
    {
        return $this->var1;
    }

    /**
     * @info Carrega contéudo de "var2"
     *
     * @param void
     * @return string
     */
    public function getEscolhaVar2()
    {
        return $this->var2;
    }
    
    /**
     * @info Carrega acesso "primeira vez"
     *
     * @param void
     * @return boolean
     */
    public function getPrimeiraVez()
    {
        return $this->primeira_vez;
    }

    /**
     * @info Carrega dados passados por
     *       $_GET/$_POST/$_REQUEST
     *
     * @param void
     * @return void
     */
    public function argumentosGet() {
        $this->primeira_vez = ( !isset($_REQUEST['primeira_vez']) );
        $this->chave        = anti_injection($_REQUEST["chave"]);
        $this->escolha      = anti_injection(str_replace('"','',$_REQUEST["escolha"]));
        $this->delete       = "";
        $this->id           = "";
        $this->nome         = "";

        $dadosorigem = $_REQUEST['dados'];

        if ( !empty($dadosorigem) )
        {
            $dados_get = explode("&", base64_decode($dadosorigem));
            $this->delete = explode("=", $dados_get[0])[1];
            $this->id     = explode("=", $dados_get[1])[1];
            $this->nome   = explode("=", $dados_get[2])[1];

            if ($this->delete == 'sim')
            {
                deleteAutorizacaoHoraExtra($this->id,$this->nome);
            }
        }
    }

    /**
     * @info Utilizada para carregar o SQL gerado para 
     *       impressao sua alimentação será via ajax 
     *       (jquery.js), a chamada encontra-se no sorttable.js
     * 
     * @param void
     */
    public function dadosParaPesquisa()
    {
        if (($this->primeira_vez == false) && ($this->chave == "") && ($this->escolha != 'todos'))
        {
            $this->var1 = $_SESSION['sChaveCriterioExtra']["chave"];
            $this->var2 = $_SESSION['sChaveCriterioExtra']["escolha"];
        }
        else
        {
            $_SESSION['sSQLPesquisaExtra']   = "";
            $_SESSION['sChaveCriterioExtra'] = "";

            $this->var1 = $this->chave;
            $this->var2 = $this->escolha;
        }
    }
}

$horaExtra = new HoraExtraModel();
$horaExtra->dadosParaPesquisa();
$var1 = $horaExtra->getChaveVar1();
$var2 = $horaExtra->getEscolhaVar2();


// pesquisa de dados
$servidor = pesquisaChaveEscolha( $var1, $var2 );
$nRows = $servidor->num_rows();

switch ($var2)
{
    case 'nome':
        $escolha_nome = 'selected';
        break;

    case 'cargo':
        $escolha_cargo = 'selected';
        break;

    case 'lotacao':
        $escolha_lotacao = 'selected';
        break;

    case 'siape':
        $escolha_siape = 'selected';
        break;

    default:
        $escolha_todos = 'selected';
        break;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo( 'Autorização de Serviços Extraordinários' );
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'hora_extra_autorizacao.js?v.1' );

$oForm->setOnLoad("$('#chave').focus();");

if (isset($_REQUEST["chave"]))
{
    //$oForm->setIconeParaImpressao("pesquisa_servidor_imp.php");
}
else
{
    $_SESSION['sColunaSortTable'] = "";
}

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <?php exibeDescricaoOrgaoUorg(); // Exibe Órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <form method="POST" id="form1" name="form1" action="hora_extra_autorizacao.php" onSubmit="javascript:return false;">
        <input type="hidden" name="modo" value="<?= $modo; ?>" >
        <input type="hidden" name="corp" value="<?= $corp; ?>">

        <div class="row margin-10">
            <div class="row">
                <div class="col-md-2 text-right" style="margin-top: 8px;">
                    <p><b>Selecione o Filtro: </b></p>
                </div>
                <div class="col-md-4 text-left">
                    <select class="form-control ciclos" name="escolha" id="escolha">
                        <option value='todos'   <?= tratarHTML($escolha_todos); ?>> Todos </option>
                        <option value='siape'   <?= tratarHTML($escolha_siape); ?>> Por Siape </option>
                        <option value='nome'    <?= tratarHTML($escolha_nome);  ?>> Por Nome </option>
                        <option value='cargo'   <?= tratarHTML($escolha_cargo); ?>> Por Cargo </option>
                        <option value='lotacao' <?= tratarHTML($escolha_lotacao); ?>> Por Lotação </option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 text-right" style="margin-top: 8px;">
                    <p><b>Chave: </b></p>
                </div>
                <div class="col-md-8 text-left">
                    <input type="text" class='form-control' id="chave" name="chave" title="Não informe pontos" size="28" maxlength="28" value='<?= tratarHTML($var1); ?>'>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-xs-6 col-md-offset-5 margin-30 margin-bottom-30">
                    <button class="btn btn-success btn-block" id="btn-continuar" role="button">
                        <span class="glyphicon glyphicon-search"></span> Pesquisar
                    </button>
                </div>
            </div>
        </div>
    </form>
<?php

if ($nRows > 0)
{
    ?>
    <div class="col-md-12">
        <br>
        <div class="row">
            <fieldset width='100%'>Total de <?= tratarHTML($nRows); ?> registros.</fieldset>
            <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                <thead>
                    <?php ImprimirTituloDasColunasHoraExtra($subView=false); ?>
                </thead>
                <tbody>
                    <?php
                    if (is_null($servidor))
                    {
                        ?>
                        <tr>
                            <td colspan="<?= tratarHTML($colunas); ?>"><?= 'Sem registros para exibir!'; ?></td>
                        </tr>
                        <?php
                    }
                    else
                    {
                        while ($rco = $servidor->fetch_object())
                        {
                            $oDBase = pesquisaChaveEscolha($rco->siape, 'siape', 'nulo');

                            ImprimirDadosHoraExtra($rco, $oDBase);

                            if (!is_null($oDBase))
                            {
                                ImprimirDadosHoraExtraDetalhes($rco, $oDBase);
                            }
                        }
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
else if (!empty($var1) && !empty($var2))
{
    unset($_SESSION['sChaveCriterioExtra']);
    mensagem("Nenhum registro selecionado!");
}

?>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();


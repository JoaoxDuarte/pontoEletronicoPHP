<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once("comparecimento_gecc_funcoes.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('chefia');

$chave   = anti_injection($_REQUEST["chave"]);
$escolha = anti_injection(str_replace('"','',$_REQUEST["escolha"]));

// APAGA O REGISTRO DO PERÍODO DE HORA-EXTRA
$dadosorigem = $_REQUEST['dados'];

if ( !empty($dadosorigem) )
{
    $dados_get1 = descriptografa($dadosorigem);
    if (substr_count($dados_get1,':|:') > 0)
    {
        $dados_get = explode(':|:',descriptografa($dadosorigem));
        $delete = $dados_get[0];
        $id     = $dados_get[1];
        $nome   = $dados_get[2];
    }
    else
    {
        $dados_get = base64_decode($dadosorigem);
        $campos = args2array($dados_get);
        $delete = $campos['delete'];
        $id     = $campos['id'];
        $nome   = $campos['nome'];
    }
    
    if ($delete == 'sim')
    {
        deleteAutorizacaoGECC($id,$nome);
    }
}

$var1 = $chave;
$var2 = $escolha;


// pesquisa de dados
$servidor = pesquisaChaveEscolha( $var1, $var2 );
$nRows = $servidor->num_rows();

$sequencia = 1;

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
$oForm->setSubTitulo( 'Gratificação por Encargo de Curso ou Concurso' );
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'comparecimento_gecc.js' );

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

    <form method="POST" id="form1" name="form1" action="comparecimento_gecc.php" onSubmit="javascript:return false;">
        <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
        <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
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
            
            <div class="form-group col-md-12 text-center">
                <div class="col-md-3"></div>
                <div class="col-md-2 col-xs-6 margin-30 margin-bottom-30">
                    <button class="btn btn-success btn-block" id="btn-continuar" role="button">
                        <span class="glyphicon glyphicon-search"></span> Pesquisar
                    </button>
                </div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-danger btn-block margin-30 margin-bottom-30" id="btn-voltar" href="javascript:window.location.replace('/sisref/<?= tratarHTML($_SESSION['voltar_nivel_1']); ?>')" role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>
                <div class="col-md-2"></div>
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
                        <?php ImprimirTituloDasColunasGECC($subView=false); ?>
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

                                ImprimirDadosGECC($rco, $oDBase);

                                if (!is_null($oDBase))
                                {
                                    ImprimirDadosGECCDetalhes($rco, $oDBase);
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
        unset($_SESSION['sChaveCriterioGECC']);
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


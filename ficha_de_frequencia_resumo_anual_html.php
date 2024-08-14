<?php
##
# PROPOSTA:
# Edinalvo, boa tarde.
#   Seria possível liberar o acesso ao módulo frequência/visualizar/ficha de frequência para a chefia imediata??
#   Estamos com várias chefias que necessitam verificar a frequência nos últimos 12 meses do servidor para
# enquadramento de licença saúde no Decreto 7003/2009 e este módulo não abre para as chefias.
# Att.
# Vilma Fontes Camargo
# Chefe de Seção Operacional da Gestão de Pessoas
# Gex Jundiaí - 56723
##
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
// Inicializa a sessão (session_start)
verifica_permissao('sRH ou Chefia');

// unidade de lotação
$qlotacao = ($_REQUEST["sLotacao"] == "" ? $_SESSION['sLotacao'] : anti_injection($_REQUEST["sLotacao"]));

$siape = anti_injection($_REQUEST['siape']);
$siape = getNovaMatriculaBySiape($siape);


$ano   = anti_injection($_REQUEST['ano']);

// instancia mensagens de erro/aviso
$oMensagem = new mensagem();


// instancia banco de dados
$oDBase = new DataBase('PDO');


// le dados da frequencia historica
$oDBase->setMensagem("Não foi possível emitir relatório com os dados informados");
$oDBase->setDestino("ficha_de_frequencia_resumo_anual.php");
$oDBase->query("
		SELECT
			cad.mat_siape AS siape, cad.nome_serv AS nome, cad.cod_lot AS lotacao, DATE_FORMAT(cad.dt_adm,'%d/%m/%Y') AS data_admissao, und.descricao AS lotacao_descricao, und.upag AS lotacao_upag
		FROM
			servativ AS cad
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		WHERE
			cad.mat_siape = '$siape'
	");
$oServidor = $oDBase->fetch_object();

$aux = 0;

// verifica permissões adicionais
if ($_SESSION['sCAD'] == "S")
{

}
elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && ($sLot != $qlotacao && $uorg_pai != $_SESSION["sLotacao"]) && $magico < '3')
{
    $oMensagem->exibeMensagem(24);
}
elseif ($_SESSION['sRH'] == "S" && $oServidor->lotacao_upag != $_SESSION['upag'])
{
    //$oMensagem->exibeMensagem(25);
}

/*
  if($_SESSION['sSenhaI'] != 'S')
  {
  if($oServidor->lotacao != $qlotacao && $_SESSION['sAPS'] == 'S')
  {
  mensagem("Não é permitido visualizar ficha de frequência de servidor de outra Unidade!", "ficha_de_frequencia_resumo_anual.php", 1 );
  }

  if($oServidor->lotacao_upag != $_SESSION['upag'])
  {
  mensagem("Não é permitido visualizar ficha de frequência de servidor de outra UPAG!", "ficha_de_frequencia_resumo_anual.php", 1 );
  }
  }
 */

// grava sessao
$_SESSION['sIMPMatricula']        = $siape;
$_SESSION['sIMPAno']              = $ano;
$_SESSION['sIMPCaminho']          = 'Frequência » Visualizar » Ficha de Frequência';
$_SESSION['sIMPLotacao']          = $oServidor->lotacao;
$_SESSION['sIMPLotacaoDescricao'] = $oServidor->lotacao_descricao;
$_SESSION['sIMPTituloFormulario'] = "Ficha de Frequ&ecirc;ncia de Servidor";

$colunas = "3";


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Frequência » Visualizar » Ficha de Frequência');
$oForm->setJS(_DIR_JS_ . 'funcoes.js');
$oForm->setJS("ficha_de_frequencia_resumo_anual_html.js");

$oForm->setSeparador(0);
$oForm->setLargura(820);

$oForm->setIconeParaImpressao("ficha_de_frequencia_resumo_anual_html_imp.php");

$oForm->setSubTitulo("Ficha de Frequ&ecirc;ncia de Servidor");

$oForm->setObservacaoTopo("Emitido em: " . date("d/m/Y"));

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<div class="container">
    <!-- Row Referente aos dados dos funcionários  -->
    <div class="row margin-10">
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>SIAPE</strong></h5>
                <p><?= tratarHTML(removeOrgaoMatricula($oServidor->siape)); ?></p>
            </div>
            <div class="col-md-6">
                <h5><strong>NOME</strong></h5>
                <p><?= tratarHTML($oServidor->nome); ?></p>
            </div>
        </div>
    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10 comparecimento">
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>ÓRGÃO</strong></h5>
                <p><?= tratarHTML(getOrgaoMaisSigla($oServidor->lotacao)); ?></p>
            </div>
            <div class="col-md-6">
                <h5><strong>UNIDADE DE EXERCÍCIO</strong></h5>
                <p><?= tratarHTML(getUorgMaisDescricao($oServidor->lotacao)); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>ADMISSÃO</strong></h5>
                <p><?= tratarHTML($oServidor->data_admissao); ?></p>
            </div>
        </div>
    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10">
        <div class="col-md-12 text-center">
            <h3><strong>Ano: <?= tratarHTML($ano); ?></strong></h3>
        </div>
    </div>
</div>


<div class="row margin-10">
    <!-- Row Referente aos dados de horário de trabalho do funcionario  -->
    <table id="ficha" class="table table-striped table-condensed table-bordered table-hover text-center">
        <thead>
            <tr>
                <td class='text-center text-nowrap col-md-1'>DIAS/MÊS</td>
                <td class='text-center text-nowrap' style='width:50px;'>JAN</td>
                <td class='text-center text-nowrap' style='width:50px;'>FEV</td>
                <td class='text-center text-nowrap' style='width:50px;'>MAR</td>
                <td class='text-center text-nowrap' style='width:50px;'>ABR</td>
                <td class='text-center text-nowrap' style='width:50px;'>MAI</td>
                <td class='text-center text-nowrap' style='width:50px;'>JUN</td>
                <td class='text-center text-nowrap' style='width:50px;'>JUL</td>
                <td class='text-center text-nowrap' style='width:50px;'>AGO</td>
                <td class='text-center text-nowrap' style='width:50px;'>SET</td>
                <td class='text-center text-nowrap' style='width:50px;'>OUT</td>
                <td class='text-center text-nowrap' style='width:50px;'>NOV</td>
                <td class='text-center text-nowrap' style='width:50px;'>&nbsp;DEZ&nbsp;</td>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>


<script type="text/javascript">
    var spinner = "";

    $(document).ready(function ()
    {
        spinner = $('.loading-spinner');
        carregaDados('<?= tratarHTML($siape); ?>', '<?= tratarHTML($ano); ?>');
    });
</script>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

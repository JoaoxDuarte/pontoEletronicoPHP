<?php

// conexao ao banco de dados, funcoes diversas
include_once("config.php");
include_once("class_form.frequencia.php");
include_once("class_ocorrencias_grupos.php");
include_once("comparecimento_tabela_auxiliar.php");
include_once("src/controllers/TabPontoController.php");

verifica_permissao("sRH ou Chefia");


// Valores passados
$ocor       = $_REQUEST['ocor'];
$dt_ini     = $_REQUEST['data_inicio'];
$dt_fim     = $_REQUEST['data_fim'];

$matriculas = array();

$selecionou_matricula = false;

foreach($_REQUEST['C'] as $key => $value)
{
    $dados = explode(':|:', base64_decode($value));

    $matriculas[] = getNovaMatriculaBySiape($dados[0]);

    $selecionou_matricula = ($selecionou_matricula == false && !empty($dados[0]) ? true : $selecionou_matricula);
}

$compete = dataMes($dt_ini) . dataAno($dt_fim);


$objOcorrenciasGrupos = new OcorrenciasGrupos();
$objTabPonto          = new TabPontoController();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// validação
$mensagem = validacao_basica_dados_informados();
    
if ( !empty($mensagem) )
{
    mensagem($mensagem, $_SESSION['voltar_nivel_2']);
    exit();
}


// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$nome_do_arquivo = nomeTabelaFrequencia($tipo_acao, $compete);
    
$pagina_anterior = $_SESSION['voltar_nivel_2'];
$ip              = getIpReal(); //linha que captura o ip do usuario.

$gravou     = 0;
$nao_gravou = 0;
$registros  = array();


// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($pagina_anterior);
$oDBase->setMensagem("Falha no registro do ponto! ");


$mes = dataMes($dt_ini);
$ano = dataAno($dt_ini);

$ini = dataDia($dt_ini);
$fim = dataDia($dt_fim);

settype($ini, "integer");
settype($fim, "integer");

// Matrículas
foreach($matriculas as $key => $mat)
{
    $registros[$mat] = array('gravou' => 0, 'nao_gravou' => 0);
    
    for ($diax = $ini; $diax <= $fim; $diax++)
    {
        $dia_alterar = $ano . "-" . $mes . "-" . str_pad($diax, 2, 0, STR_PAD_LEFT);

        if (validaData($dia_alterar) == false)
        {
            $registros[$mat]['nao_gravou']++;
            continue;
        }

        //Implementar busca para saber se já ocorreu o registro de entrada no dia
        $oDBase = $objTabPonto->registrosPorSiapeDia($mat, $dia_alterar);

        $nRows  = $oDBase->num_rows();

        $oPonto     = $oDBase->fetch_object();
        $ocorrencia = $oPonto->oco;
        $sitcad     = $oPonto->sigregjur;
        $idreg      = $oPonto->idreg;
        $lot        = $oPonto->cod_lot;

        // ocorrências
        $codigosCOVID19             = $objOcorrenciasGrupos->CodigosCOVID19( $sitcad );
        $codigosUsoExclusivoSistema = $objOcorrenciasGrupos->CodigosUsoExclusivoSistema( $sitcad );

        if ($idreg == 'X' || ($ocorrencia !== $ocor && in_array($ocor, $codigosCOVID19)))
        {
            //
        }
        elseif ($nRows > 0 && (($ocorrencia == $ocor && in_array($ocor, $codigosCOVID19)) || !in_array($ocorrencia, $codigosUsoExclusivoSistema) || $idreg !== 'X'))
        {
            $registros[$mat]['nao_gravou']++;
            continue;
        }


        // para registro no histórico da ação ou grupo de registro
        // Ex.: A : ação alteração (chefia/RH)
        //      C : registro chefia
        //      R : registro RH
        $idregPonto = define_quem_registrou($lot);

        $ip_alterou        = ($idregPonto == 'C' ? 'ipch' : 'iprh');
        $mat_alterou       = ($idregPonto == 'C' ? 'matchef' : 'siaperh');
        //$alterou_historico = ($tipo_acao == 'historico_manutencao' ? ', acao_executada = :acao_executada' : '');

        $var['dia']      = $dia_alterar;
        $var['siape']    = $mat;
        $var['entra']    = '00:00:00';
        $var['intini']   = '00:00:00';
        $var['intsai']   = '00:00:00';
        $var['sai']      = '00:00:00';
        $var['jornd']    = '00:00';
        $var['jornp']    = '00:00';
        $var['jorndif']  = '00:00';
        $var['oco']      = $ocor;
        $var['just']     = '';
        $var['seq']      = '00';
        $var['idreg']    = $idregPonto;
        $var['ip']       = '';
        $var['ip2']      = '';
        $var['ip3']      = '';
        $var['ip4']      = '';
        $var['justchef'] = '';
        $var['ipch']     = ($idregPonto == 'C' ? '' : $ip);
        $var['iprh']     = ($idregPonto == 'C' ? $ip : '');
        $var['matchef']  = ($idregPonto == 'C' ? $_SESSION['sMatricula'] : '');
        $var['siaperh']  = ($idregPonto == 'C' ? '' : $_SESSION['sMatricula']);

        if ($nRows == 0)
        {
            if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") || ($_SESSION['sRH'] == "S"))
            {
                $retorno = $objTabPonto->insert( $var );
            }
        }
        else
        {
            //grava os dados anteriores
            if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") || ($_SESSION['sRH'] == "S"))
            {
                $retorno = $objTabPonto->update( $var );
            }
        }

        switch ($retorno)
        {
            case 'gravou':
                $registros[$mat]['gravou']++;
                break;
            case 'nao_gravou':
            default:
                $registros[$mat]['nao_gravou']++;
                break;
        }
        
        // verifica se há registro de comparecimento
        // a consulta médica ou exame ou GECC
        AjustaSaldoFrequenciaSeConsultaMedicaRegistrada($mat, $dia_alterar);
    }
}

//$mensagem = "Ocorrência(s) registrada(s) com sucesso!";
$prim_vez = true;

foreach ($registros as $key => $valor)
{
    $mensagem .= ($prim_vez ? "" : "<br>") . removeOrgaoMatricula($key) . ": ";

    if ($valor['gravou'] > 0)
    {
        $mensagem .= "Registrada(s) " . $valor['gravou'] . " ocorrência(s)";
    }

    if ($valor['nao_gravou'] > 0)
    {
        $mensagem .= ($valor['gravou'] > 0 ? " e " : "") . "NÃO registrou " . $valor['nao_gravou'] . " ocorrência(s)!";
    }

    $prim_vez = false;
}

mensagem($mensagem, $_SESSION['voltar_nivel_1']);


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();


function validacao_basica_dados_informados()
{
    global $ocor, $dt_ini, $dt_fim, $dias_no_mes, $selecionou_matricula;

    $mensagem = "";

    // código da ocorrência
    if (soNumeros($ocor) == "")
    {
        $mensagem .= "Selecione uma ocorrência!\\n";
    }

    // data de início da ocorrência
    if (validaData($dt_ini) == false)
    {
        $mensagem .= "Informe a Data Inicial da Ocorrência!\\n";
    }

    // data de término da ocorrência
    if (validaData($dt_fim) == false)
    {
        $mensagem .= "Informe a Data Final da Ocorrência!\\n";
    }

    // matricula(s)
    if ($selecionou_matricula == false)
    {
        $mensagem .= "Selecione um Servidor!\\n";
    }
    
    return $mensagem;
}

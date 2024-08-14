<?php

// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sRH ou sTabServidor");

// pesquisa e exclui
$sDescricao    = anti_injection($_POST['descricao']);
$sUorgAntes    = anti_injection($_POST['sUorgAntes']);
$sUorg         = anti_injection($_POST['sUorg']);
$upai          = anti_injection($_POST['upai']);
$upag          = anti_injection($_POST['upag']);
$sUg           = anti_injection($_POST['sUg']);
$area          = anti_injection($_POST['area']);
$inicio        = $_POST['inicio'];
$fim           = $_POST['fim'];
$sAtivo        = anti_injection($_POST['sAtivo']);
$excessao      = anti_injection($_POST['excessao']);
$sigla         = anti_injection($_POST['sigla']);
$codmun        = anti_injection($_POST['codmun']);
$fuso_horario  = anti_injection($_POST['fuso_horario']);
$horario_verao = anti_injection($_POST['horario_verao']);

$pagina_de_origem = pagina_de_origem();

$_SESSION['sTablotaCodigo'] = $sUorg;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setSubTitulo("Confirmação de Gravação");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


if (empty($sDescricao) || strlen(trim($sDescricao)) == 0)
{
    mensagem("A Descricao é obrigatória!", $pagina_de_origem);
    exit;
}
    
if (soNumeros($sUorg) == 0)
{
    mensagem("A Uorg é um campo obrigatório!", $pagina_de_origem);
    exit;
}
    
if (soNumeros($upai) == 0)
{
    mensagem("A Uorg Pai é um campo obrigatório!", $pagina_de_origem);
    exit;
}
    
if (soNumeros($upag) == 0)
{
    mensagem("A Upag é um campo obrigatório!", $pagina_de_origem);
    exit;
}
    
if (time_to_sec($inicio) == 0)
{
    mensagem("Início do atendimento é um campo obrigatório!", $pagina_de_origem);
    exit;
}
    
if (time_to_sec($fim) == 0)
{
    mensagem("Fim do atendimento é um campo obrigatório!", $pagina_de_origem);
    exit;
}

$oDBase = CarregaDadoMunicipio($codmun);
$oCidade = $oDBase->fetch_object();
$cidade_lota = $oCidade->nome;
$uf_lota     = $oCidade->uf;


// gravando os dados
switch ($pagina_de_origem)
{
    case 'tablota_cadastrar.php': 
        gravarInclusaoDeUORG();
        break;
    
    default:
        gravarAlteracaoDeUORG();
        break;
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();


/* ******************************************* *
 *                                             *
 *           FUNÇÕES COMPELEMENTARES           *
 *                                             *
 * ******************************************* */



/*
 * @info Grava inclusãode UORG
 * 
 */
function gravarInclusaoDeUORG()
{
    global $pagina_de_origem, $sUorg, $sUorgAntes, $sDescricao, $area, $upai, 
        $upag, $sUg, $inicio, $fim, $sAtivo, $sigla, $codmun, $cidade_lota, 
        $uf_lota, $fuso_horario, $horario_verao, $excessao;

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    if (($_SESSION['sAdmCentral']=='S' || $_SESSION['sSenhaI']=='S'))
    {
        $oDBase->query("
        SELECT codigo 
            FROM tabsetor 
                WHERE codigo = :codigo
        ", array(
            array( ':codigo', $sUorg, PDO::PARAM_STR )
        ));
            
        if ($oDBase->num_rows() > 0 && $sUorg != $sUorgAntes)
        {
            mensagem( "Inclusão do código da UORG não realizada!<br>Já existe UORG cadastrada com o código $sUorgAntes.", $pagina_de_origem);
            exit;
        }
    
        $oDBase->query("
        INSERT INTO tabsetor 
            (codigo, descricao, cod_uorg, area, uorg_pai, cod_uorg_pai, upag,
             ug, inicio_atend, fim_atend, ativo, sigla, codmun, cidade_lota,
             uf_lota, fuso_horario, horario_verao, periodo_excecao)
            VALUES
            (:codigo, :descricao, :cod_uorg, :area, :uorg_pai, :cod_uorg_pai, 
             :upag, :ug, :inicio_atend, :fim_atend, :ativo, :sigla, :codmun,
             :cidade_lota, :uf_lota, :fuso_horario, :horario_verao, 
            :periodo_excecao)
        ", array(
            array( ':codigo',          $sUorg,         PDO::PARAM_STR ),
            array( ':descricao',       $sDescricao,    PDO::PARAM_STR ),
            array( ':cod_uorg',        $sUorg,         PDO::PARAM_STR ),
            array( ':area',            $area,          PDO::PARAM_STR ),
            array( ':uorg_pai',        $upai,          PDO::PARAM_STR ),
            array( ':cod_uorg_pai',    $upai,          PDO::PARAM_STR ),
            array( ':upag',            $upag,          PDO::PARAM_STR ),
            array( ':ug',              $sUg,           PDO::PARAM_STR ),
            array( ':inicio_atend',    $inicio,        PDO::PARAM_STR ),
            array( ':fim_atend',       $fim,           PDO::PARAM_STR ),
            array( ':ativo',           $sAtivo,        PDO::PARAM_STR ),
            array( ':sigla',           $sigla,         PDO::PARAM_STR ),
            array( ':codmun',          $codmun,        PDO::PARAM_STR ),
            array( ':cidade_lota',     $cidade_lota,   PDO::PARAM_STR ),
            array( ':uf_lota',         $uf_lota,       PDO::PARAM_STR ),
            array( ':fuso_horario',    $fuso_horario,  PDO::PARAM_STR ),
            array( ':horario_verao',   $horario_verao, PDO::PARAM_STR ),
            array( ':periodo_excecao', $excessao,      PDO::PARAM_STR ),
        ));

        if ($oDBase->affected_rows() > 0)
        {
            registraLog(" incluiu o setor $sDescricao, Codigo $sUorg,");
            mensagem("Dados gravados com sucesso!", $pagina_de_origem);
        }   
        else
        {
            mensagem("Dados NÃO foram gravados!", $pagina_de_origem);
        }
    }
    else
    {
        mensagem("Usuário sem permissão para essa tarefa!", $pagina_de_origem);
    }
}


/*
 * @info Grava as alterações de dados de UORG
 * 
 */
function gravarAlteracaoDeUORG()
{
    global $pagina_de_origem, $sUorg, $sUorgAntes, $sDescricao, $area, $upai, 
        $upag, $sUg, $inicio, $fim, $sAtivo, $sigla, $codmun, $cidade_lota, 
        $uf_lota, $fuso_horario, $horario_verao, $excessao;
    
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    if (($_SESSION['sAdmCentral']=='S' || $_SESSION['sSenhaI']=='S'))
    {
        $oDBase->query("
        SELECT codigo, ativo 
            FROM tabsetor 
                WHERE codigo = :codigo
        ", array(
            array( ':codigo', $sUorg, PDO::PARAM_STR )
        ));
    
        if ($oDBase->num_rows() > 0 && $sUorg != $sUorgAntes)
        {
            mensagem( "Alteração do código da UORG não realizada!<br>Já existe UORG cadastrada com o código $sUorgAntes.", $pagina_de_origem);
            exit;
        }
    
        $oDBase->query("
        UPDATE tabsetor 
            SET codigo          = :codigo,
                descricao       = :descricao,
                cod_uorg        = :cod_uorg,
                area            = :area,
                uorg_pai        = :uorg_pai,
                cod_uorg_pai    = :uorg_pai,
                upag            = :upag,
                ug              = :ug,
                inicio_atend    = :inicio_atend,
                fim_atend       = :fim_atend,
                ativo           = :ativo,
                sigla           = :sigla,
                codmun          = :codmun,
                cidade_lota     = :cidade_lota,
                uf_lota         = :uf_lota,
                fuso_horario    = :fuso_horario,
                horario_verao   = :horario_verao,
                periodo_excecao = :periodo_excecao
                    WHERE codigo = :codigo_antes
        ", array(
            array( ':codigo',          $sUorg,         PDO::PARAM_STR ),
            array( ':descricao',       $sDescricao,    PDO::PARAM_STR ),
            array( ':cod_uorg',        $sUorg,         PDO::PARAM_STR ),
            array( ':area',            $area,          PDO::PARAM_STR ),
            array( ':uorg_pai',        $upai,          PDO::PARAM_STR ),
            array( ':upag',            $upag,          PDO::PARAM_STR ),
            array( ':ug',              $sUg,           PDO::PARAM_STR ),
            array( ':inicio_atend',    $inicio,        PDO::PARAM_STR ),
            array( ':fim_atend',       $fim,           PDO::PARAM_STR ),
            array( ':ativo',           $sAtivo,        PDO::PARAM_STR ),
            array( ':sigla',           $sigla,         PDO::PARAM_STR ),
            array( ':codmun',          $codmun,        PDO::PARAM_STR ),
            array( ':cidade_lota',     $cidade_lota,   PDO::PARAM_STR ),
            array( ':uf_lota',         $uf_lota,       PDO::PARAM_STR ),
            array( ':fuso_horario',    $fuso_horario,  PDO::PARAM_STR ),
            array( ':horario_verao',   $horario_verao, PDO::PARAM_STR ),
            array( ':periodo_excecao', $excessao,      PDO::PARAM_STR ),
            array( ':codigo_antes',    $sUorgAntes,    PDO::PARAM_STR ),
        ));
    }
    else if ($_SESSION['sRH'] == "S" && $_SESSION['sTabServidor'] == "S")
    {
        $oDBase->query("
        UPDATE tabsetor 
            SET uorg_pai        = :uorg_pai,
                cod_uorg_pai    = :uorg_pai,
                inicio_atend    = :inicio_atend,
                fim_atend       = :fim_atend,
                ativo           = :ativo,
                sigla           = :sigla,
                codmun          = :codmun,
                cidade_lota     = :cidade_lota,
                uf_lota         = :uf_lota,
                periodo_excecao = :periodo_excecao
                    WHERE codigo = :codigo
        ", array(
            array( ':uorg_pai',        $upai,        PDO::PARAM_STR ),
            array( ':inicio_atend',    $inicio,      PDO::PARAM_STR ),
            array( ':fim_atend',       $fim,         PDO::PARAM_STR ),
            array( ':ativo',           $sAtivo,      PDO::PARAM_STR ),
            array( ':sigla',           $sigla,       PDO::PARAM_STR ),
            array( ':codmun',          $codmun,      PDO::PARAM_STR ),
            array( ':cidade_lota',     $cidade_lota, PDO::PARAM_STR ),
            array( ':uf_lota',         $uf_lota,     PDO::PARAM_STR ),
            array( ':periodo_excecao', $excessao,    PDO::PARAM_STR ),
            array( ':codigo',          $sUorg,       PDO::PARAM_STR ),
        ));
    }

    if ($oDBase->affected_rows() > 0)
    {
        registraLog(" alterou os dados do setor $sDescricao, Codigo $sUorgAntes,");
        mensagem("Dados gravados com sucesso!", $pagina_de_origem);
    }   
    else
    {
        mensagem("Dados NÃO foram gravados!", $pagina_de_origem);
    }
}


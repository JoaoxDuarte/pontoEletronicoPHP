<?php

// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// parametros passados por formulario
$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];

$unidade = anti_injection($_REQUEST["unidade"]);

// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI'] . "?sMatricula=$sMatricula&magico=$magico";

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// permissão para administrar mais que uma unidade
if ($_SESSION["sLancarExcessao"] !== "S" && $_SESSION["sAPS"] !== "S")
{
    // dados da funcao - titular
    $rows = frequenciaEntraPesquisaOcupanteFuncao($sMatricula);

    // dados do substituição efetivada
    $linha = frequenciaEntraPesquisaSubstitutoFuncao($sMatricula);
    
    // Titular de função e substituindo outra
    if ($magico == '2' || $rows == 1 || ($rows >= 2 && $linha == 0))
    {
        replaceLink($form_destino[0] . "?dados=" . $form_destino[1]);
    }
}

$_SESSION['sResponsavelPorMaisDeUmaUnidade'] = ($rows >= 2 ? 'S' : 'N');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'frequencia_entra_formulario.js?time='.rand(2222, 9999) );
$oForm->setOnLoad("if ($('#qlotacao') != null) { $('#qlotacao').focus(); }");
$oForm->setSubTitulo("Informe o setor que deseja " . ucfirst($form_caminho));

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

if ($rows >= 2 || $_SESSION['sSenhaI'] == 'S' || $_SESSION['sAudCons'] == 'S' || $_SESSION["sLancarExcessao"] == "S" || ($_SESSION["sAPS"] == "S"  /*&& $totalSubordinadas > 1 */))
{
    ?>
    <form method="POST" id="form1" name="form1" onSubmit="javascript:return false;">
        <input type='hidden' id='cmd'   name='cmd'   value='1'>
        <input type='hidden' id='orig'  name='orig'  value='1'>
        <input type='hidden' id='dia'   name='dia'   value='<?= date('d/m/Y'); ?>'>
        <input type='hidden' id='dados' name='dados' value='<?= tratarHTML($form_destino[1]); ?>'>
        <input type='hidden' id='form_caminho' name='form_caminho' value='<?= tratarHTML($form_caminho); ?>'>
        <input type='hidden' id='form_destino' name='form_destino' value='<?= tratarHTML($form_destino[0]); ?>'>

        <div align="center">
            <table width="70%" cellspacing="0">
                <tr>
                    <td align="center" valign="middle">
                        <div align="center">
                            <font size=1>
                            <select name='qlotacao' id='qlotacao' size="1" class="form-control select2-single" title="Selecione a unidade desejada!">
                                <?php

                                if ($_SESSION['sSenhaI']=='S' || $_SESSION['sAudCons']=='S' || $_SESSION["sLancarExcessao"]=="S")
                                {
                                    // lista unidades
                                    frequenciaEntraPrintListboxUnidades();
                                }
                                else
                                {

                                    // pega dados da funcao ocupada
                                    if($_SESSION["sAPS"] == "S"){
                                        frequenciaEntraPrintUnidadesSubordinadas();

                                    }else{
                                        frequenciaEntraPrintListboxUnidadeOcupante($sMatricula);

                                    }
                                }

                                ?>
                            </select>
                            </font>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="col-md-12 margin-25"></div>

            <div class="col-md-2 col-xs-6 col-md-offset-5 margin-30 margin-bottom-30">
                <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                    <span class="glyphicon glyphicon-ok"></span> Continuar
                </a>
            </div>
        </div>
    </form>
    <?php
}
else
{
    mensagem("Usuário sem permissão para essa tarefa!");
}

// Base do formulário
//

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit;


/*
 * FUNÇÕES COMPLEMENTARES
 *
 */
function frequenciaEntraPesquisaOcupanteFuncao($sMatricula)
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT
        func.num_funcao, func.cod_lot, chf.num_funcao, chf.sit_ocup
    FROM
        tabfunc AS func
    LEFT JOIN
        ocupantes AS chf ON func.num_funcao = chf.num_funcao
    WHERE
        chf.mat_siape = '" . $sMatricula . "'
        AND chf.dt_fim = '0000-00-00'
        AND func.resp_lot = 'S'
    ");

    return $oDBase->num_rows();
}

function frequenciaEntraPesquisaSubstitutoFuncao($sMatricula)
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT
        subs.inicio, subs.fim
    FROM
        substituicao AS subs
    WHERE
        subs.siape = '" . $sMatricula . "'
        AND subs.situacao = 'A'
    ");

    return $oDBase->num_rows();
}


function frequenciaEntraPrintListboxUnidades()
{
    if ($_SESSION["sLancarExcessao"] == "S")
    {
        // pega lista dos setores se administrador
        $oDBase = frequenciaEntraSelecionaUnidadesTodasExcessaoGestores();
    }
    else
    {
        $oDBase = frequenciaEntraSelecionaUnidadesTodasGestorSistema();
    }

    $gerencia = '';
    $regional = '';

    $opcoes = "";

    while ($campo = $oDBase->fetch_object())
    {
        if (($regional == '' || $regional != $campo->regional)
            || ($gerencia == '' || $gerencia != $campo->gerencia))
        {
            if ($regional == '' || $regional != $campo->regional)
            {
                $opcoes .= "<option value='" . tratarHTML($campo->cod_lot) . "' disabled>" . tratarHTML($campo->gerencia) . "</option>";
            }
            else if ($gerencia == '' || $gerencia != $campo->gerencia)
            {
                $opcoes .= "<option value='" . tratarHTML($campo->cod_lot) . "' disabled>" . tratarHTML($campo->gerencia) . "</option>";
            }

            $regional = $campo->regional;
            $gerencia = $campo->gerencia;
        }

        $opcoes .= "<option value='" . tratarHTML($campo->cod_lot) . "'";

        if ($campo->cod_lot == $_SESSION['sLotacao'] || ($unidade != '' && $campo->cod_lot == $unidade))
        {
            $opcoes .= " selected ";
        }

        $opcoes .= ">&nbsp;&nbsp;&nbsp;" . tratarHTML($campo->cod_lot) . " - " . tratarHTML($campo->descricao) . "</option>";
    }

    print $opcoes;
}

function frequenciaEntraSelecionaUnidadesTodasExcessaoGestores()
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $where = "
        und.ativo = 'S'
        AND fun.resp_lot = 'S'
        AND 0 <> ANY (SELECT COUNT(*)
                      FROM servativ AS cad
                      WHERE
                          cad.cod_lot = und.codigo
                          AND excluido='N'
                          AND cod_sitcad NOT IN ('02','08','15','66'))
    ";

    if ($_SESSION['sBrasil'] == "S")
    {
        // Todas as unidades
    }
    else if ($_SESSION['sSR'] == "S")
    {
        $where .= " AND ger.id_ger='" . $_SESSION['regional'] . "' ";
    }
    else if ($_SESSION['sUF'] == "S")
    {
        $where .= " AND LEFT(und.codigo,2) = '" . substr($_SESSION['sLotacao']) . "' ";
    }
    else if ($_SESSION['sGEX'] == "S")
    {
        $where .= " AND und.upag='" . $_SESSION['upag'] . "' ";
    }

    $sql = "
    SELECT
        fun.num_funcao, fun.cod_lot, und.descricao, REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(UPPER(gex.nome_gex)),'Ç','C'),'Á','A'),'Ã','A'),'À','A'),'Ä','A'),'Â','A'),'É','E'),'È','E'),'Ë','E'),'Ê','E'),'Ü','U'),'Ú','U'),'Í','I'),'A‡','C'),'Ó','O'),'Ò','O'),'Ô','O'),'Õ','O'),'Ö','O'),'  ',' ') AS gerencia, und.regional, ger.id_ger, ger.nome_ger, IF(SUBSTR(und.codigo,3,3)='150' OR LEFT(und.codigo,2)='01','Administração',IF(IFNULL(gex.nome_gex,'')='','',IF(SUBSTR(und.codigo,1,2)='01',gex.nome_gex,CONCAT('Gerência Executiva em ',gex.nome_gex)))) AS nome_gex, gex.cod_gex, und.codigo, und.descricao, IFNULL(fun.desc_func,'') AS funcao
    FROM tabsetor AS und
    LEFT JOIN tabsetor_gex AS gex ON und.upag = gex.upag
    LEFT JOIN tabsetor_ger AS ger ON und.regional = ger.id_ger
    LEFT JOIN tabfunc AS fun ON und.codigo = fun.cod_lot
    WHERE
        " . $where . "
    ORDER BY
        ger.id_ger,
        IF(LEFT(und.codigo,2)='01',und.codigo,99999999999999),
        IF(SUBSTR(und.codigo,3,3)='150',0,CONCAT(LEFT(und.codigo,2),SUBSTR(und.codigo,4,2))),
        IF(SUBSTR(und.codigo,3,3)='150',0,
        IF(SUBSTR(und.codigo,3,1)='0',1,
        IF(SUBSTR(und.codigo,3,1)='2',2,
        IF(SUBSTR(und.codigo,3,1)='3',3,
        IF(SUBSTR(und.codigo,3,1)='4',4,
        IF(SUBSTR(und.codigo,6,3)='521',4,
        IF(SUBSTR(und.codigo,3,1)='5',6,
        IF(SUBSTR(und.codigo,3,1)='6',7,
        IF(SUBSTR(und.codigo,3,1)='7',8,
        IF(SUBSTR(und.codigo,3,1)='9',9,
        IF(SUBSTR(und.codigo,3,1)='1',10,
        IF(SUBSTR(und.codigo,3,1)='8',11, 99)))))))))))), und.codigo
    ";

    $oDBase->query($sql);

    return $oDBase;
}


function frequenciaEntraSelecionaUnidadesTodasGestorSistema()
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // pega lista dos setores se administrador
    $oDBase->query("
    SELECT
        func.num_funcao, func.cod_lot, und.descricao, upg.gerencia, und.regional
    FROM
        tabsetor AS und
    LEFT JOIN
        tabfunc AS func ON und.codigo = func.cod_lot
    LEFT JOIN
        upag AS upg ON func.upag = upg.upag_cod
    WHERE
        func.resp_lot = 'S'
        AND func.ativo = 'S'
        AND upg.desativado = 'nao'
    GROUP BY
        func.cod_lot, func.num_funcao
    ORDER BY
        IF(SUBSTR(func.cod_lot,1,2)='01',1,2),
        und.regional,
        IF(SUBSTR(func.cod_lot,3,3)='150',1,2),
        upg.gerencia,
        LEFT(func.cod_lot,2),
        func.cod_lot
    ");

    $oDBase->query($sql);

    return $oDBase;
}

function frequenciaEntraPrintListboxUnidadeOcupante($sMatricula)
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // pega dados da funcao ocupada
    $oDBase->query("
    SELECT
        func.num_funcao, func.cod_lot, chf.num_funcao, chf.sit_ocup, und.descricao
    FROM
        tabfunc AS func
    LEFT JOIN
        ocupantes AS chf ON func.num_funcao = chf.num_funcao
    LEFT JOIN
        tabsetor AS und ON func.cod_lot = und.codigo
    WHERE
        chf.mat_siape = '" . $sMatricula . "'
    GROUP BY
        func.num_funcao
    ");

    $qq = $_SESSION['sLotacao'];

    $opcoes = "";

    while ($campo = $oDBase->fetch_object())
    {
        $opcoes .= "<option value='" . tratarHTML($campo->cod_lot) . "'";

        if ($campo->cod_lot == $qq) //$campo->num_funcao == $qq)
        {
            $opcoes .= " selected";
        }

        $opcoes .= " >" . tratarHTML($campo->cod_lot) . " - " . tratarHTML($campo->descricao) . "</option>";
    }

    print $opcoes;
}

function frequenciaEntraPrintUnidadesSubordinadas($count = false)
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    
    // pega dados da funcao ocupada
    $oDBase->query("
    SELECT
        *
    FROM
        tabsetor 
    WHERE
        ( uorg_pai = '" . $_SESSION['sLotacao'] . "' AND ativo = 'S' ) OR codigo = '".$_SESSION['sLotacao']."'
    ");

    $opcoes = "";
    $total = 0;
    while ($campo = $oDBase->fetch_object())
    {
        $total++;
        $opcoes .= "<option value='" . tratarHTML($campo->codigo) . "'";

        if ($campo->codigo == $_SESSION['sLotacao']) //$campo->num_funcao == $qq)
        {
            $opcoes .= " selected";
        }

        $opcoes .= " >" . tratarHTML($campo->codigo) . " - " . tratarHTML($campo->descricao) . "</option>";
    }
    
    if($count){
        return $total;
    }else{
        print $opcoes;
    }
}

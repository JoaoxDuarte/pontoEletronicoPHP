<?php

include_once( 'config.php' );

verifica_permissao('administrador_e_chefe_de_rh');

// parametros
$pesquisa = $_POST['pesquisa'];

$where = "und.upag = :upag ";
if ($pesquisa == 'RH')
{
    $where .= "
    AND und.codigo = :upag
    ";
}
// instancia do banco de dados
$oDBase = new DataBase('PDO');

// pesquisa
$oDBase->query("
SELECT
    und.codigo AS setor, und.descricao
FROM
    tabsetor AS und
WHERE
    " . $where . " 
",
array(
    array( ':upag', $_SESSION['upag'], PDO::PARAM_STR ),
));
$oSetor = $oDBase->fetch_object();

$oDBase->query("
SELECT
    usu.siape, 
    cad.nome_serv AS nome, 
    cad.cod_lot AS setor, 
    cad.cod_uorg, und.upag, 
    und.descricao, 
    usu.senha, 
    usu.acesso,
    cad.cpf
FROM
    usuarios AS usu
LEFT JOIN
    servativ AS cad ON usu.siape = cad.mat_siape
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
WHERE
    " . $where . " 
ORDER BY
    cad.nome_serv
",
array(
    array( ':upag', $_SESSION['upag'], PDO::PARAM_STR ),
));

$html = "
<style>
    .width10p { width: 10%; }
    .width60p { width: 60%; }
    .width30p { width: 30%; }
    .centra { text-align: center; }
    .unidade_descricao { 
        text-align:center;font-size:13px;font-family:Verdana;font-color:#FFFFFF;
        font-weight:bold;padding:5px 2px 6px 2px;
    }
    .coluna_lista { 
        text-align:center;font-size:11px;font-family:Verdana;font-color:#FFFFFF;
        font-weight:bold;padding:5px 2px 5px 2px;
    }
    .lista_nomes { 
        font-size:9px;font-family:Verdana;font-color:#FFFFFF;
        padding:1px 1px 1px 1px;height:5px;
    }
    .sem_registro { 
        text-align:center;font-size:12px;font-family:Verdana;font-color:#FFFFFF;
        padding:2px 2px 2px 2px;
    }
</style>

<table class='table table-striped table-condensed table-bordered'>
    <tr colspan='3'>
	<td class='unidade_descricao'>&nbsp;" . tratarHTML($oSetor->setor) . ' - ' . tratarHTML(strtoupper($oSetor->descricao)) . "</td>
    </tr>
</table>

<table class='table table-striped table-condensed table-bordered'>
    <tr bgcolor='#dbdbb7'>
	<td class='coluna_lista width16p'>SIAPE</td>
	<td class='coluna_lista width42p'>NOME</td>
	<td class='coluna_lista width25p'>ACESSO</td>
    </tr>
";

if ($oDBase->num_rows() > 0)
{
    while ($linha = $oDBase->fetch_object())
    {
        $c = $linha->acesso;

        $bloqueio  = ($c[0] == "N" || $c[9] == "N" ? " checked " : "");
        $consulta  = ($c[0] == "S" && $c[9] == "N" ? " checked " : "");
        $alteracao = ($c[9] == "S" ? " checked " : "");

        $html .= "
        <tr>
            <td class='lista_nomes width10p centra'>" . tratarHTML($linha->siape) . "</td>
            <td class='lista_nomes width60p'>" . strtoupper(ltrim(rtrim($linha->nome))) . "</td>
            <td class='lista_nomes width30p centra' nowrap>
                <table border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td class='lista_nomes centra'>&nbsp;<input type='radio' name='C" . tratarHTML($linha->siape) . "[]' value='00'" . $bloqueio . " onclick='javascript:gravarPermissao(\"" . $linha->siape . "\")'>&nbsp;Bloqueado</td>
                        <td class='lista_nomes centra'>&nbsp;<input type='radio' name='C" . tratarHTML($linha->siape) . "[]' value='01'" . $consulta . " onclick='javascript:gravarPermissao(\"" . $linha->siape . "\")'>&nbsp;Consultar</td>
                        <td class='lista_nomes centra'>&nbsp;<input type='radio' name='C" . tratarHTML($linha->siape) . "[]' value='09'" . $alteracao . " onclick='javascript:gravarPermissao(\"" . $linha->siape . "\")'>&nbsp;Alterar</td>
                        <td class='lista_nomes centra'><div id='gravou" . tratarHTML($linha->siape) . "'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' width='14px' border='0'></div></td>
                    </tr>
                </table>
            </td>
        </tr>
        ";
    }
}
else
{
    $html .= "
    <tr>
        <td class='lista_nomes' colspan='3'>Sem Registros para Exibir</td>
    </tr>
    ";
}

$html .= "
</table>
";

print $html;

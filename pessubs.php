<?php
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

$chave = urldecode($_REQUEST["chave"]);
$campo = urldecode($_REQUEST["escolha"]);

$upag = $_SESSION['upag'];

## pesquisa substituições
#
if (isset($_REQUEST["chave"]))
{
    $oDBase = pesquisa_substituicoes($chave, $campo, $upag);

    $num_rows = $oDBase->num_rows();

    $oSubstituicao = $oDBase->fetch_object();
    $nome          = $oSubstituicao->nome_serv;
    $siape         = $oSubstituicao->siape;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Relatórios » Gerencial » Substituições » Substituições do servidor');
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setSeparador(0);
$oForm->setSubTitulo("Substituições do Servidor");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


html_inicio();
html_dados();
html_fim();


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


########################################################
#                                                      #
#    Funçoes para montagem da página de pesquisa       #
#                                                      #
########################################################
#

function html_inicio()
{
    global $modo, $corp, $nome, $siape, $chave;

    ?>
    <style>
        .subs_radio_siape { text-align: left; width: 79px; height: 35px; }
        .subs_radio_nome  { text-align: left; width: 80px; height: 35px; }
        .subs_radio_cpf   { text-align: left; width: 70px; height: 35px; }
        .subs_radio_chave { text-align: left; width: 450px; height: 35px; font-weight: bold; }
        .subs_nome  { text-align: left; width: 390px; height: 25px; }
        .subs_siape { text-align: left; width: 75px; height: 25px; }
    </style>
    <form method="POST" action="pessubs.php" id="form1" name="form1">
        <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
        <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
        <table border="0" width='1%' cellspacing="0" cellpadding="0">
            <tr>
                <td class='tahomaSize_1 subs_radio_siape'>
                    <input type="radio" name="escolha" value="siape" onClick="document.all['chave'].focus()" checked> Por Siape
                </td>
                <td class='tahomaSize_1 subs_radio_nome'>
                    <input type="radio" name="escolha" value="nome" onClick="document.all['chave'].focus()"> Por Nome
                </td>
                <td class='tahomaSize_1 subs_radio_cpf'>
                    <input type="radio" name="escolha" value="cpf" onClick="document.all['chave'].focus()"> Por Cpf
                </td>
            </tr>
            <tr>
                <td class='tahomaSize_2 subs_radio_chave' colspan='3'>
                    Chave:&nbsp;<input type="text" class="caixa" name="chave" title="Não informe pontos" size="28">
                </td>
            </tr>
        </table>
        <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
    </form>

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class='tahomaSize_2' width='50px'>
                &nbsp;<b>Nome:</b>&nbsp;
            </td>
            <td class='tahomaSize_2 subs_nome'>
                <?= tratarHTML($nome); ?>
            </td>
            <td class='tahomaSize_2' width='50px'>
                &nbsp;<b>Siape:</b>&nbsp;
            </td>
            <td class='tahomaSize_2 subs_siape'>
                <?= tratarHTML($siape); ?>
            </td>
        </tr>
    </table>
    <table border="1" width="100%" cellspacing="0" cellpadding="0" bordercolor="#F1F1E2">
        <tr bgcolor="#DBDBB7">
            <td width="70" align="center">&nbsp;<b>AÇÃO</b>&nbsp;</td>
            <td width="70" align="center">&nbsp;<b>SIAPE</b>&nbsp;</td>
            <td width="900" height='18'>&nbsp;<b>FUN&Ccedil;&Atilde;O</b>&nbsp;</td>
            <td width="100" align="center">&nbsp;<b>IN&Iacute;CIO</b>&nbsp;</td>
            <td width="100" align="center">&nbsp;<b>FIM</b>&nbsp;</td>
            <td align="center">&nbsp;<b>SITUAÇÃO</b>&nbsp;</td>
        </tr>
    <?php
}

function html_dados()
{
    global $siape, $upag, $num_rows, $chave;

    if ($num_rows > 0)
    {
        $pesquisa = "
		SELECT
			a.id, a.siape, a.sigla, a.numfunc, c.desc_func, DATE_FORMAT(a.inicio,'%d/%m/%Y') AS inicio, DATE_FORMAT(a.fim,'%d/%m/%Y') AS fim, a.situacao, b.nome_serv
		FROM
			substituicao AS a
		LEFT JOIN
			servativ AS b ON a.siape = b.mat_siape
		LEFT JOIN
			tabfunc AS c ON a.numfunc = c.num_funcao
		LEFT JOIN
			tabsetor AS und ON b.cod_lot = und.codigo
		WHERE
			c.upag = '$upag' AND a.siape = '$siape'
		ORDER BY
			b.nome_serv, a.inicio DESC
		";

        // instancia banco de dados
        $oDBase = new DataBase('PDO');

        $oDBase->query($pesquisa);

        while ($pm_partners = $oDBase->fetch_array())
        {
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <td align="center">&nbsp;<a href="substituicao_encerrar.php?id=<?= tratarHTML($pm_partners['id']); ?>&orig=2" target="new">ALTERAR</a>&nbsp;</td>
                <td align="center">&nbsp;<?= tratarHTML($pm_partners['siape']); ?>&nbsp;</td>
                <td align="left">&nbsp;<?= tratarHTML($pm_partners['numfunc']) . " " . tratarHTML($pm_partners['desc_func']); ?>&nbsp;</td>
                <td align="center">&nbsp;<?= tratarHTML($pm_partners['inicio']); ?>&nbsp;</td>
                <td align="center">&nbsp;<?= tratarHTML($pm_partners['fim']); ?>&nbsp;</td>
                <td align="center">&nbsp;<?= tratarHTML($pm_partners['situacao']); ?>&nbsp;</td>
            </tr>
            <?php
        } // fim do while
    }
    else if ($chave != "")
    {
        ?>
        <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
            <td align="center" colspan='6' height='30'>Não há registros de substituição(ões) para exibir</td>
        </tr>
        <?php
    }
}
function html_fim()
{
    ?>
    </table>
    <br>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td valign='top'><font size="2"><b>Obs:&nbsp;</b></font></td>
            <td><font size="1">1&nbsp;-&nbsp;</font></td>
            <td><font size="1">O servidor s&oacute; poder&aacute; ter um per&iacute;odo ativo de substitui&ccedil;&atilde;o por vez, caso existam mais de um encerre os outros per&iacute;odos de forma que fique apenas um periodo ativo por interm&eacute;dio do link alterar.</font></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><font size="1">2&nbsp;-&nbsp;</font></td>
            <td><font size="1">Caso j&aacute; tenha encerrado o per&iacute;odo de substitui&ccedil;&atilde;o e ainda conste situa&ccedil;&atilde;o A = Ativo nesse per&iacute;odo, encerre a substitui&ccedil;&atilde;o por interm&eacute;dio do link alterar.</font></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><font size="1">3&nbsp;-&nbsp;</font></td>
            <td><font size="1">Caso o servidor esteja em efetiva substitui&ccedil;&atilde;o e a situa&ccedil;&atilde;o desse per&iacute;odo conste E - encerrado, ative a substitui&ccedil;&atilde;o por interm&eacute;dio do link alterar.</font></td>
        </tr>
    </table>
    <br>
    <?php
}

function pesquisa_substituicoes($chave, $campo, $upag)
{
    $pesquisa = "
		SELECT
			a.id, a.siape, a.sigla, a.numfunc, c.desc_func, DATE_FORMAT(a.inicio,'%d/%m/%Y') AS inicio, DATE_FORMAT(a.fim,'%d/%m/%Y') AS fim, a.situacao, b.nome_serv
		FROM
			substituicao AS a
		LEFT JOIN
			servativ AS b ON a.siape = b.mat_siape
		LEFT JOIN
			tabfunc AS c ON a.numfunc = c.num_funcao
		LEFT JOIN
			tabsetor AS und ON b.cod_lot = und.codigo
		WHERE
			und.upag = '" . $upag . "'
	";

    switch ($campo)
    {
        case "cpf": $pesquisa .= " AND b.cpf = '" . $chave . "' ";
            break;
        case "siape": $pesquisa .= " AND a.siape = '" . $chave . "' ";
            break;
        case "nome": $pesquisa .= " AND b.nome_serv LIKE '%" . $chave . "%' ";
            break;
    }

    $pesquisa .= "GROUP BY a.siape ";
    $pesquisa .= "ORDER BY b.nome_serv ";

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query($pesquisa);

    return $oDBase;
}

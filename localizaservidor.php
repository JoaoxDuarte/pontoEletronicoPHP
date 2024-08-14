<?php
// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "class_form.telas.php" );

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('sRH e sTabServidor');

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// parametros passados por formulario
$siape = anti_injection($_REQUEST['pSiape']);

// valores registrado em sessao
// upag do cadastrador
$upag = $_SESSION['upag'];

// testa se foi passada a matr�cula siape
// sen�o verifica se h� dados na se��o sobre a matr�cula do servidor
if (empty($siape) && empty($_SESSION['sMov_Matricula_Siape']))
{
    mensagem("Obrigat�rio informar a matr�cula do Servidor!", null, 1);
}
elseif (empty($siape))
{
    $siape = $_SESSION['sMov_Matricula_Siape'];
}
else
{
    // salvamos a matr�cula do servidor
    // para que o teste de erro de upag
    // possa funcionar corretamente caso aconte�a algum
    // problema na movimenta��o e retorne para este script
    $_SESSION['sMov_Matricula_Siape'] = $siape;
}

// pesquisa servidor
$oDBase = selecionaServidor($siape);
$nRows  = $oDBase->num_rows();

$oServidor = $oDBase->fetch_object();

if ($nRows == 0)
{
    mensagem('Servidor n�o est� ativo ou inexistente!', $pagina_de_origem, 1);
}
else if ($oServidor->upag != $upag) // verifica se a uorg ou a upag do servidor eh a mesma do usuario
{
    mensagem('N�o � permitido "localizar" servidor de outra UPAG!', $pagina_de_origem, 1);
}
else if ($oServidor->chefia == 'S')
{
    mensagem('Servidor ocupa chefia, n�o pode ser "localizado"!', $pagina_de_origem, 1);
}


## classe para montagem do formulario
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro � Movimenta��o � Alterar Localiza��o');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setJS('localizaservidor.js');
$oForm->setOnLoad("javascript: if($('#dtsai')) { $('#dtsai').focus() };");
$oForm->setSeparador(0);
$oForm->setSeparadorBase(20);

$oForm->setSubTitulo("Localiza��o de Exerc&iacute;cio de Servidores");

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="gravalocalservidor.php" onsubmit="return verificadados()" id="form1" name="form1" >
    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
        <tr>
            <td class='tahomaSize_2' width="665px" height="41px" style="width:665px; height:41px;">
                &nbsp;Nome:<br>
                &nbsp;<input name="sNome" type="text" class='caixa' id="sNome" value='<?= tratarHTML($oServidor->nome_serv); ?>' size="60" readonly>
            </td>
            <td class='tahomaSize_2' width="175px" height="41px" align="center" style="width:175px; height:41px; text-align: center;">
                CPF:<br>
                <input name="sCpf" type="text" class='caixa' id="sCpf" value='<?= tratarHTML($oServidor->cpf); ?>' size="15" readonly>
            </td>
            <td class='tahomaSize_2' width="177px" height="41px" align="center" style="width:177px; height:41px; text-align: center;">
                Matr�cula Siape:<br>
                <input name="tSiape" type="text" class='caixa' id="tSiape" value='<?= tratarHTML($oServidor->mat_siape); ?>' size="7" readonly>
            </td>
        </tr>

        <tr>
            <td class='tahomaSize_2' width="665px" height="41px" style="width:665px; height:41px;">
                &nbsp;Localiza&ccedil;&atilde;o atual:<br>
                <?php
                $oDBase->query('SELECT und.descricao FROM tabsetor AS und WHERE codigo = "' . $oServidor->cod_loc . '" ');
                $wnomelocal = $oDBase->fetch_object()->descricao;
                ?>
                &nbsp;<input name="local" type="text" class='caixa' id="local" value='<?= tratarHTML($oServidor->cod_loc) . ' - ' . tratarHTML($wnomelocal); ?>' size="90" readonly>
            </td>
            <td class='tahomaSize_2' width="175px" height="41px" align="center" style="width:175px; height:41px; text-align: center;">
                Data de Ingresso:<br>
                <input name="dtinglocal" type="text" class='alinhadoAEsquerda' id="dtinglocal"  OnKeyPress="formatar(this, '##/##/####')"  value='<?= tratarHTML($oServidor->dt_ing_loc); ?>' size="11" readonly>
            </td>
            <td class='tahomaSize_2' width="177px" height="41px" align="center" style="width:177px; height:41px; text-align: center;">
                Data de Sa&iacute;da:<br>
                <input name="dtsailocal" type="text" class='alinhadoAEsquerda' id="dtsailocal"  OnKeyPress="formatar(this, '##/##/####')" size="11" maxlength="11">
            </td>
        </tr>

        <tr>
            <td class='tahomaSize_2' colspan='2' width="840px" height="41px" style="width:840px; height:41px;">
                &nbsp;Nova localiza&ccedil;&atilde;o:<br>
                &nbsp;<select name="novalocal" size="1" class="drop" id="novalocal">
                    <?php

                    // tabela de lotacao
                    $oDBase->query('SELECT und.codigo, und.descricao FROM tabsetor AS und WHERE und.upag = "' . $_SESSION['upag'] . '" AND und.codigo <> "00000000000000" ORDER BY und.codigo');
                    while ($campo      = $oDBase->fetch_object())
                    {
                        echo '<option value="' . tratarHTML($campo->codigo) . '"';
                        if ($campo->codigo == $oServidor->cod_loc)
                        {
                            echo " selected";
                        }
                        echo ' >' . tratarHTML($campo->codigo) . ' - ' . tratarHTML($campo->descricao) . '</option>';
                    }
                    // Fim da tabela de lotacao

                    ?>
                </select>
            </td>
            <td class='tahomaSize_2' width="177px" height="41px" align="center" style="width:177px; height:41px; text-align: center;">
                Data de Ingresso:<br>
                <input name="dtingnlocal" type="text" class='alinhadoAEsquerda' id="dtingnlocal"  OnKeyPress="formatar(this, '##/##/####')" size="11" maxlength="11">
            </td>
        </tr>
    </table>

    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6">&nbsp;</p>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6">
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>

</form>
<?php

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

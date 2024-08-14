<?php
include_once( "config.php" );

verifica_permissao("logado");

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

$diaa     = $_REQUEST["dia"];
$orig     = anti_injection($_REQUEST["orig"]);
$cmd      = '3';
$qlotacao = anti_injection($_REQUEST["qlotacao"]);

switch ($orig)
{
    case "1":
        $vDatas = date("Y-m-d");
        $dia    = date("d/m/Y");
        break;
    case "2":
        $vDatas = conv_data($diaa);
        $dia    = $diaa;
        break;
    default:
        $vDatas = ($diaa == '' ? date("Y-m-d") : conv_data($diaa));
        $dia    = date("d/m/Y");
        break;
}

// definicao da competencia
$mes  = substr($dia, 3, 2);
$ano  = substr($dia, 6, 4);
$comp = $mes . $ano;

// instancia banco de dados
$oTbDados = new DataBase('PDO');

// descricao da lotacao
$oTbDados->query("SELECT descricao FROM tabsetor WHERE codigo = '$qlotacao' AND ativo='S' ");
$oPonto            = $oTbDados->fetch_object();
$descricao_lotacao = $oPonto->descricao;

// seleciona os registros para homologação
$oServidores      = seleciona_servidores_ponto($link, $qlotacao, $vDatas);
$total_servidores = $oServidores->num_rows();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setSeparador(0);
$oForm->setLargura("950px");
$oForm->setCaminho('Frequência » Gerencial');
$oForm->setSubTitulo("Acompanhamento di&aacute;rio de Registro de Frequ&ecirc;ncia do m&ecirc;s corrente");
$oForm->setObservacaoTopo("Dia <big>$dia</big> - Lota&ccedil;&atilde;o <input name='lot' type='text' class='alinhadoAoCentro' id='lot' value='$qlotacao - $descricao_lotacao' size='60' maxlength='60' readonly> - Para acompanhar outro dia clique na figura <a href='acompanharpassado.php?mes=$mes&ano=$ano&lot=$qlotacao&cmd=$cmd'><img border='0' src='" . _DIR_IMAGEM_ . "copiar.gif' align='absmiddle'></a>");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber1">
    <tr>
        <td>

            <table class="thin sortable draggable" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
                <tr bgcolor="#DBDBB7">
                    <td width="5%" align='center'><b> <div align="center">SIAPE</div></td>
                    <td width="18%"><b>Nome</td>
                    <td width="5%"><div align="center"><strong>Hor&aacute;rio</strong></div></td>
                    <td width="5%"><b> <div align="center">Sit</div></td>
                    <td width="5%"><b> <div align="center">Jornada</div></td>
                    <td width="6%"><div align="center"><strong>Entrada</strong></div></td>
                    <td width="7%"><div align="center"><strong>Intervalo Inicio</strong></div></td>
                    <td width="7%"><div align="center"><strong>Intervalo Fim</strong></div></td>
                    <td width="5%"><div align="center"><strong>Saida</strong></div></td>
                    <td width="7%"><div align="center"><strong>Horas no dia</strong></div></td>
                    <td width="7%"><div align="center"><strong>Ocorr&ecirc;ncia</strong></div></td>
                </tr>
                <?php
                while ($pm = $oServidores->fetch_array())
                {
                    $ent  = $pm['entra'];
                    $inti = $pm['intini'];
                    $ints = $pm['intsai'];
                    $sai  = $pm['sai'];
                    $jd   = $pm['jornd'];
                    $oco  = $pm['oco'];
                    if ($pm[cod_sitcad] == "66")
                    {
                        $sit = ETG;
                    }
                    else
                    {
                        $sit = RJU;
                    }
                    ?>
                    <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                        <td align='center'>
                            <a href='veponto2.php?pSiape=$pm[mat_siape]&mes=$mes&ano=$ano&sLotacao=$pm[cod_lot]&cmd=$cmd<?= ($total_servidores != "0" ? "&so_ver=sim" : ""); ?>' target='new'><font color='#000000'><?= tratarHTML($pm['mat_siape']); ?></a>
                        </td>
                        <td><?= tratarHTML($pm['nome_serv']); ?></td>
                        <td align='center'>
                            <a href='reghora.php?mat=$pm[mat_siape]&lot=$qlotacao<?= ($total_servidores != "0" ? "&so_ver=sim" : ""); ?>' target='new'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Alterar horario'></a>
                        </td>
                        <td align='center'><?= tratarHTML($sit); ?></td>
                        <td align='center'><?= tratarHTML($pm['jornada']); ?></td>
                        <td align='center'><?= tratarHTML($ent); ?></td>
                        <td align='center'><?= tratarHTML($inti); ?></td>
                        <td align='center'><?= tratarHTML($ints); ?></td>
                        <td align='center'><?= tratarHTML($sai); ?></td>
                        <td align='center'><?= tratarHTML($jd); ?></td>
                        <td align='center'><?= tratarHTML($oco); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>

        </td>
    </tr>
</table>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

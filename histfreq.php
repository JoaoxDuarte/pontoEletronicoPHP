<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sLog");

$mat  = anti_injection($_REQUEST["mat"]);
$comp = anti_injection($_REQUEST["comp"]);

// instancia BD
$oDBase = new DataBase('PDO');

// dados
$oDBase->query("SELECT * FROM histponto$comp WHERE siape = '$mat' ");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Utilitários » Auditoria » Registros Alterados » Alterações de Frequência');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setJS(_DIR_JS_ . 'jquery.js');
$oForm->setJS(_DIR_JS_ . 'sorttable.js');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Dados Históricos de Frequência");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function validar()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var chave = $('#chave');

        if (chave.val().length == 0)
        {
            oTeste.setMsg('É obrigatório informar o critério de pesquisa!', chave);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }
</script>

<form  action="histfreq.php" method="POST" id="form1" name="form1" onSubmit="return validar()">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <br>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td class="corpo">
                <div align="center">
                    <font size="2">
                    O relat&oacute;rio demonstra os registros que sofreram altera&ccedil;&atilde;o ou exclus&atilde;o com seus dados originais
                    </font>
                </div>
            </td>
        </tr>
    </table>
    <table class="thin sortable draggable" border="1" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" id="AutoNumber2">
        <tr bgcolor='#008000'>
            <td width="6%" align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">DIA</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPE</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">ENTRADA</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">I_INTERV.</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">V_INTERV</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SAIDA</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">JRN_DIA</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">JRN_P</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">RES_DIA</font></b></td>
            <td width="2%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">RESP</font></b></td>
            <td width="2%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">OCO</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPECHEF</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPERH</font></b></td>
            <td width="6%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">DA_ALT</font></b></td>
            <td width="4%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">HORA_ALT</font></b></td>
            <td width="6%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPEALT</font></b></td>
            <td width="8%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">IPALT</font></b></td>
            <td width="2%"  align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">ID_ORIGEM</font></b></td>
        </tr>
        <?php
        while ($pm_partners = $oDBase->fetch_array())
        {
            $ano = substr($pm_partners['dia'], 0, 4);
            $mes = substr($pm_partners['dia'], 5, 2);
            $dia = substr($pm_partners['dia'], 8, 2);

            $ano1 = substr($pm_partners['diaalt'], 0, 4);
            $mes1 = substr($pm_partners['diaalt'], 5, 2);
            $dia1 = substr($pm_partners['diaalt'], 8, 2);
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <td width="6%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($dia) . '/' . tratarHTML($mes) . '/' . tratarHTML($ano); ?></font></td>
                <td  width="4%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['siape']); ?></font></td>
                <td  width="4%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['entra']); ?></font></td>
                <td  width="4%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['intini']); ?></font></td>
                <td  width="4%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['intsai']); ?></font></td>
                <td  width="4%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['sai']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['jornd']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['jornp']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['jorndif']); ?></font></td>
                <td width="2%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['idreg']); ?></font></td>
                <td width="2%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['oco']); ?></font></td>
                <td width="4%" align="center"><font size="1" face="Tahoma"><?= ($pm_partners['matchef'] < 1 ? "000000000000" : tratarHTML($pm_partners['matchef'])); ?>
                    </font>
                </td>
                <td width="4%" align="center">
                    <font size="1" face="Tahoma">
                    <?= ($pm_partners['siaperh'] < 1 ? "000000000000" : tratarHTML($pm_partners['siaperh'])); ?>
                    </font>
                </td>
                <td width="6%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($dia1) . '/' . tratarHTML($mes1) . '/' . tratarHTML($ano1); ?></font></td>
                <td width="4%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['horaalt']); ?></font></td>
                <td width="6%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['siapealt']); ?></font></td>
                <td width="8%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['ipalt']); ?></font></td>
                <td width="2%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['idaltexc']); ?></font></td>
            </tr>
            <?php
        } // fim do while
        ?>
    </table>
    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px; margin-bottom: 0">
        <font size="1">
        - Jrn_dia = jornada do dia<br>
        - Jrn_p = jornada prevista para o cargo<br>
        - Res_dia = Resultado do dia<br>
        - Resp = Respons&aacute;vel pelo registro<br>
        - Oco = C&oacute;digo da Ocorr&ecirc;ncia<br>
        - Siapechef = Matricula do chefe que efetuou o lan&ccedil;amento<br>
        - Siaperh = Matr&iacute;cula do servidor de RH que lan&ccedil;ou a ocorr&ecirc;ncia<br>
        - Da_alt = Data da altera&ccedil;&atilde;o - Hora_alt = Hora da altera&ccedil;&atilde;o<br>
        - Siapealt = Matr&iacute;cula de quem efetuou a altera&ccedil;&atilde;o<br>
        - Ipalt = Ip da m&aacute;quina em que foi efetuada a altera&ccedil;&atilde;o<br>
        - Id_origem = Identifica se foi alterado ou excluido.<br>
        - Resp : S = Servidor, C = Chefe, A = Abonado pelo chefe, R = Recursos Humanos<br>
        </font>
    </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

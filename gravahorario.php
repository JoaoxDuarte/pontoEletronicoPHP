<?php
include_once( "config.php" );

$path_parts       = pathinfo($_SESSION["sVePonto"]);
$pagina_de_origem = $path_parts['filename'];
$pagina_de_origem = ($pagina_de_origem == '' ? 'x' : $pagina_de_origem);

$pagina_autorizadas   = array();
$pagina_autorizadas[] = 'entrada5';
$pagina_autorizadas[] = 'entrada6';
$pagina_autorizadas[] = 'pontoser';

verifica_permissao((in_array($pagina_de_origem, $pagina_autorizadas) ? "logado" : "sAPS"));

$modo = anti_injection($_REQUEST['modo']);

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

// historico de navegacao
$sessao_navegacao       = new Control_Navegacao();
$pagina_retorno_sucesso = $sessao_navegacao->getPaginaPrimeira();
$pagina_retorno_erro    = $sessao_navegacao->getPaginaUltima();

// caminho e titulo
switch ($modo)
{
    case '3':
        $script_caminho = 'Utilitários » Gestores » Prazos » Horário de Verão';
        $script_titulo  = "Horário de Verão";
        $titulo         = $script_titulo;
        break;
    case '6':
        $script_caminho = 'Frequência » Visualizar » Solicitações de Autorização de Trabalho';
        $script_titulo  = "Solicitações de Trabalho em Dia não Útil";
        $titulo         = $script_titulo;
        break;
    default:
        $script_caminho = 'Frequência » ...';
        $script_titulo  = "";
        $titulo         = "Registro de Horário de Trabalho";
        break;
}


// instancia o BD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo( $titulo );

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 1                                              |
  |     - grava horário de serviço do servidor -            |
  |                                                         |
  \*------------------------------------------------------- */
if ($modo == "1")
{

    // $vUsuario = $sUsuario;
    $vHoras    = strftime("%H:%M:%S", time());
    $vDatas    = date("Y-m-d");
    $tSiape    = anti_injection($_REQUEST['tSiape']);
    $lotacao   = anti_injection($_REQUEST['lota']);
    $entra     = anti_injection($_REQUEST['entra']) . ':00';
    $intini    = anti_injection($_REQUEST['intini']) . ':00';
    $intsai    = anti_injection($_REQUEST['intsai']) . ':00';
    $sai       = anti_injection($_REQUEST['sai']) . ':00';
    $inisetor  = anti_injection($_REQUEST['inisetor']);
    $fimsetor  = anti_injection($_REQUEST['fimsetor']);
    $bhoras    = anti_injection($_REQUEST['bhoras']);
    $bhtipo    = anti_injection($_REQUEST['bhoras']);
    $jornada   = anti_injection($_REQUEST['jornada']);
    $hjornada2 = anti_injection($_REQUEST['jd']);
    $hjornada  = $hjornada2 * 5;

    $situacao_cadastral = anti_injection($_REQUEST['sitcad']);
    $sAutorizadoTE      = anti_injection($_REQUEST['sAutorizadoTE']);
    $ocupaFuncao        = anti_injection($_REQUEST['ocupaFuncao']);
    $cpf                = anti_injection($_REQUEST['sCpf']);

    $ip         = getIpReal(); //linha que captura o ip do usuario.
    $sMatricula = $_SESSION["sMatricula"];

    if ($bhoras == '00' || $bhtipo == '9')
    {
        mensagem("Favor autorizar ou não banco de horas!", null, 1);
    }

    /* ALTERAÇÃO CANCELADA
      $bhoras = ($bhtipo == "0" ? "N" : "S");
     */
    $bhtipo = ($bhoras == "N" ? "0" : "1");

    if ($sAutorizadoTE == 'N' || $ocupaFuncao == 'S')
    {
        $oDBase->setMensagem("Problemas no acesso ao CADASTRO!");
        $oDBase->query("SELECT entra_trab, ini_interv, sai_interv, sai_trab, autchef, bhoras, bh_tipo FROM servativ WHERE cpf = :cpf and mat_siape!= :siape and excluido = 'N' ", array(
            array(":cpf", $cpf, PDO::PARAM_STR),
            array(":siape", $tSiape::PARAM_STR),
        ));
        $lret       = $oDBase->num_rows();
        $oServidor2 = $oDBase->fetch_object();
        $Nsai       = $oServidor2->sai_trab;
        $Nentra     = $oServidor2->entra_trab;
    }
    else
    {
        $Nsai   = $sai;
        $Nentra = $entra;
    }

    if ($lret == 1)
    {
        if ($sai > $Nentra && $entra < $Nsai)
        {
            mensagem("Horário informado é incompatível com o horário da outra matrícula do servidor!", null, 1);
        }
    }

    $oDBase->setMensagem("Erro na gravação do histórico!");
    $oDBase->query("INSERT INTO histcad (mat_siape, defvis, jornada, entra_trab, sai_trab, ini_interv, sai_interv, horae, processo, motivo, dthe, dthefim, autchef, bhoras, bh_tipo, dataalt, horaalt, siapealt, ipalt) (SELECT mat_siape, defvis, jornada, entra_trab, sai_trab, ini_interv, sai_interv, horae, processo, motivo, dthe, dthefim, autchef, bhoras, bh_tipo, '$vDatas' AS dataalt, '$vHoras' AS horaalt, '$sMatricula' AS siapealt, '$ip' AS ipalt FROM servativ WHERE mat_siape='$tSiape') ");

    $oJornada          = new DefinirJornada();
    $oJornada->setSiape($tSiape);
    $oJornada->setLotacao($lotacao);
    $oJornada->setData($vDatas);
    $oJornada->jornada = $jornada;

    $oJornada->autorizado_te = $sAutorizadoTE;
    $oJornada->chefiaAtiva   = $ocupaFuncao;

    /* Verifica se o horario  registrado e maior ou menor que o da tabela de setores */
    $sautChef = (($entra < $inisetor) || ($sai > $fimsetor) ? 'S' : 'N');

    $oDBase->setMensagem("Falha no registro da solicitação");
    if ($sAutorizadoTE == 'S' && $ocupaFuncao == 'N' && $situacao_cadastral != '66')
    {
        $oDBase->query("UPDATE servativ SET autchef='$sautChef', bhoras='$bhoras', bh_tipo='$bhtipo', motivo='" . ($sAutorizadoTE == 'S' && $hmotivo != 0 ? '' : 'T') . "' WHERE mat_siape = '$tSiape' ");
    }
    else
    {
        $oDBase->query("UPDATE servativ SET autchef='$sautChef', bhoras = '$bhoras', bh_tipo='$bhtipo', entra_trab='$entra', ini_interv='$intini', sai_interv='$intsai', sai_trab='$sai' WHERE mat_siape='$tSiape' ");
    }

    // grava horarios no jornada historico
    $oJornada->gravaHorario($entra, $intini, $intsai, $sai);

    mensagem("Alteração de horário registrada com sucesso!", null, 2);
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 2                                              |
  |     - grava justificativa do servidor -                 |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "2") //
{

    $sMatricula = $_SESSION['sMatricula'];
    $just       = strtr($_REQUEST['just'], array("'" => "`", ";" => ":", '"' => "`"));
    $dia        = $_REQUEST['dia'];
    $data       = conv_data($dia);
    $siape      = anti_injection($_REQUEST['siape']);

    // dados para uso ao retornar a pagina 'pontoser.php'
    $_SESSION['sDadosParaVerComprovante']= array(
        'siape' => $sMatricula,
        'mes'   => dataMes($dia),
        'ano'   => dataAno($dia),
    );

    if (strlen(trim($just)) > 15)
    {
        $aData = explode('/', $dia);
        $comp  = $aData[1] . $aData[2]; //$_REQUEST['comp'];

        $oDBase->query("UPDATE ponto$comp SET just = :just WHERE siape = :siape AND dia = :dia ", array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":dia", $data, PDO::PARAM_STR),
            array(":just", $just, PDO::PARAM_STR)
        ));

        if ($oDBase->affected_rows() > 0)
        {
            if (substr($pagina_retorno_sucesso, 0, 12) == 'entrada6.php')
            {
                $sessao_navegacao->SubstituiValorREQUEST(($sessao_navegacao->UltimoElemento() - 1), 'mes3', dataMes($dia));
                $sessao_navegacao->SubstituiValorREQUEST(($sessao_navegacao->UltimoElemento() - 1), 'ano3', dataAno($dia));
                $sessao_navegacao->SubstituiValorREQUEST(($sessao_navegacao->UltimoElemento() - 1), 'cmd', $modo);
                $pagina_retorno_sucesso = $sessao_navegacao->getPagina(($sessao_navegacao->UltimoElemento() - 1));
            }
            mensagem("Justificativa para o dia $dia, registrada com sucesso!", $pagina_retorno_sucesso, NULL, 'success');
        }
        else
        {
            mensagem("Justificativa para o dia $dia, não foi alterada!", $pagina_retorno_erro, 1, 'danger');
        }
    }
    else
    {
        mensagem('Obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!', $pagina_retorno_erro, 1, 'warning');
    }
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 3                                              |
  |     - grava horario de verao -                          |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "3")
{

    $id         = anti_injection($_REQUEST['id']);
    $ano        = anti_injection($_REQUEST['ano']);
    $inicio     = conv_data($_REQUEST['inicio']);
    $fim        = conv_data($_REQUEST['fim']);
    $base_legal = anti_injection($_REQUEST["base_legal"]);
    $estados    = anti_injection($_REQUEST["estados"]);
    $ativo      = anti_injection($_REQUEST["ativo"]);

    $periodo = dataAno($inicio) . '/' . dataAno($fim);

    $mes_ano_ini = substr($inicio, 5, 2) . '/' . substr($inicio, 0, 4);
    $mes_ano_fim = substr($fim, 5, 2) . '/' . substr($fim, 0, 4);

    $oDBase->setMensagem("Falha no registro de horario de verao");
    
    if (empty($id))
    {
        $oDBase->query("SELECT periodo FROM tabhorario_verao WHERE periodo = :periodo ", array(
            array(':periodo', $periodo, PDO::PARAM_STR)
        ));
        
        if ($oDBase->num_rows() == 0)
        {
            $oDBase->query("
            INSERT INTO tabhorario_verao 
                (id, periodo, hverao_inicio, hverao_fim, base_legal, estados_incluidos, ativo) 
                VALUES 
                (0, :periodo, :inicio, :fim, :base_legal, :estados, :ativo) 
            ", 
            array(
                array(':periodo',    $periodo,    PDO::PARAM_STR),
                array(':inicio',     $inicio,     PDO::PARAM_STR),
                array(':fim',        $fim,        PDO::PARAM_STR),
                array(':base_legal', $base_legal, PDO::PARAM_STR),
                array(':estados',    $estados,    PDO::PARAM_STR),
                array(':ativo',      $ativo,      PDO::PARAM_STR),
            ));
        }
        else
        {
            mensagem('Período já registrado!', "tabvalida.php?aba=seg", 1);
        }
    }
    else 
    {
        $oDBase->query("
        UPDATE 
            tabhorario_verao 
        SET
            periodo           = :periodo,
            hverao_inicio     = :inicio, 
            hverao_fim        = :fim,
            base_legal        = :base_legal,
            estados_incluidos = :estados,
            ativo             = :ativo 
        WHERE 
            id = :id
        ", 
        array(
            array(':id',         $id,         PDO::PARAM_STR),
            array(':periodo',    $periodo,    PDO::PARAM_STR),
            array(':inicio',     $inicio,     PDO::PARAM_STR),
            array(':fim',        $fim,        PDO::PARAM_STR),
            array(':base_legal', $base_legal, PDO::PARAM_STR),
            array(':estados',    $estados,    PDO::PARAM_STR),
            array(':ativo',      $ativo,      PDO::PARAM_STR),
        ));
    }

    if ($oDBase->affected_rows() > 0)
    {
        registraLog("O usuário $sMatricula alterou o horário de verão para o período " . $mes_ano_ini . " a " . $mes_ano_fim . " "); // grava o LOG
        mensagem("Horário de Verão registrado com sucesso!", "tabvalida.php?aba=seg", 1);
    }
    else
    {
        mensagem('Não houve sucesso no registro/alteração do Horário de Verão!', "tabvalida.php?aba=seg", 1);
    }
}
elseif ($modo == "4")
{

    /*
      $vUsuario = $sUsuario;
      $vHoras   = strftime("%H:%M:%S",time());
      $vDatas   = date("Y-m-d");
      if ((date(n)<"10"))
      {
      $comp = "0".date(n);
      }
      else
      {
      $comp = date(n);
      }
      $query = "UPDATE servativ SET  malt = '$comp', autchef = 'S', entra_trab = '$ent', ini_interv = '$inti', sai_interv = '$ints', sai_trab = '$sai' where mat_siape = '$mat' ";
      $result = mysql_query($query) or die("Inexiste registro de horas a autorizar".mysql_error());

      echo "<div align='center'><font color='#800000' face='Tahoma' size='4'><br><br>Autorização registrada com sucesso! <br> Informe ao servidor que pode iniciar a nova jornada! </font></div>";
     */
}


/* --------------------------------------------------------\
  |                                                         |
  |     MODO 5                                              |
  |     - grava solicitação trabalho em Dia Não Útil        |
  |                                                         |
  \-------------------------------------------------------- */
elseif ($modo == "5")
{

    $tSiape = anti_injection($_REQUEST['tSiape']);
    $sNome  = anti_injection($_REQUEST['sNome']);
    $dnu    = $_REQUEST['dnu'];
    $codmun = anti_injection($_REQUEST['codmun']);

    $lot   = $_SESSION['sLotacao'];
    $chefe = $_SESSION['sAPS'];

    $m = substr($dnu, 3, 2);
    $d = substr($dnu, 0, 2);
    $y = substr($dnu, 6, 4);

    // pagina de retorno
    $voltar = "entrada5.php";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($voltar);

    // inverte a data
    $dnu = conv_data($dnu);

    // verifica se eh chefe de unidade
    if ($chefe == "S")
    {
        $oDBase->setMensagem("Tabela de setores inexistente");
        $oDBase->query("SELECT uorg_pai FROM tabsetor WHERE codigo = '" . $lot . "' ");
        $oSetor = $oDBase->fetch_object();
        $lot    = $oSetor->uorg_pai;
    }
    else
    {
        //$lot = $lot;
    }

    #checa se dia solicitado é útil ou não, ou se é inferior a data de hoje
    if ($dnu <= date('Y-m-d'))
    {
        $mensagem = "Data anterior a data atual!";
    }
    elseif (verifica_se_dia_nao_util($dnu, $lot) == false)
    {
        $mensagem = "Operação não realizada, o dia solicitado é dia útil!";
        $voltar   = "regdnu.php";
    }
    elseif (verifica_se_dia_nao_util($dnu, $lot) == true) // a data é de um dia não útil (fim de semana, feriado)
    {
        $oDBase->setMensagem('Tabela não encontrada (DNU)!');
        $oDBase->setSemDBErro(true);

        $oDBase->query("SELECT dia FROM tabdnu WHERE siape='$tSiape' AND dia='$dnu' ");
        if ($oDBase->num_rows() > 0)
        {
            $mensagem = "Data já registrada no banco de dados para essa matrícula!";
        }
        else
        {
            $xRes = $oDBase->query("INSERT INTO tabdnu (dia, siape) VALUES ('$dnu', '$tSiape') ");
            if ($xRes)
            {
                //obtendo email do chefe e do substituto para comunicação da solicitação
                $oDBase->setMensagem("Tabela de ocupantes inexistente");
                $oDBase->query("SELECT a.mat_siape, d.nome_serv AS nome, c.num_funcao, a.sit_ocup, IF(IFNULL(b.siape,'')='','N','S') AS substituindo, d.email FROM ocupantes AS a LEFT JOIN substituicao AS b ON a.num_funcao=b.numfunc AND (NOW() >= IF(inicio='0000-00-00','9999-99-99',inicio) AND NOW() <= IF(fim='0000-00-00','9999-99-99',fim)) AND b.situacao='A' LEFT JOIN tabfunc AS c ON a.num_funcao=c.num_funcao LEFT JOIN servativ AS d ON a.mat_siape=d.mat_siape WHERE c.cod_lot='$lot' AND c.resp_lot='S' AND ((a.sit_ocup='T' && IF(IFNULL(b.siape,'')='','N','S')='N') || (a.sit_ocup='S' && IF(IFNULL(b.siape,'')='','N','S')='S')) ORDER BY IF(a.sit_ocup='S',1,2),a.mat_siape ");
                $oChefe     = $oDBase->fetch_object();
                $nomechefe  = $oChefe->nome;
                $emailchefe = $oChefe->email;

                $count = enviarEmail($emailchefe, 'SOLICITACAO DE TRABALHO EM DIA NAO UTIL', "<br><br><big>Senhor(a) " . nome_sobrenome($nomechefe) . ",<br>Informamos que foi solicitado trabalho em dia n&atilde;o util pelo servidor(a) $sNome, siape $tSiape, ficando a seu cargo autorizar ou n&atilde;o por intermédio da opção 'Frequência » Visualizar » Autorização de Trabalho' no SISREF.<br> Atenciosamente<br> Equipe SISREF.</big><br><br>");

                if ($count != 0)
                {
                    $mensagem = "Solicitação registrada com sucesso!\\n\\nO início do trabalho em dia não útil depende\\nde autorização da chefia imediata.\\n\\nEmail enviado com sucesso para " . nome_sobrenome($nomechefe) . "!";
                }
                else
                {
                    $mensagem = "Ocorreu um erro durante o envio do email para " . nome_sobrenome($nomechefe) . "!";
                }
            }
        }
    }

    mensagem($mensagem, $voltar, 1);
}


/* --------------------------------------------------------\
  |                                                         |
  |     MODO 6                                              |
  |     - grava autorização para trabalho em Dia Não Útil   |
  |                                                         |
  \-------------------------------------------------------- */
elseif ($modo == "6")
{

    $mat   = anti_injection($_REQUEST['mat']);
    $dia   = $_REQUEST['dia'];
    $email = $_REQUEST['email'];
    $lot   = anti_injection($_REQUEST['lot']);

    $oDBase->setMensagem("Data não localizada no banco de dados para essa matrícula!");
    $oDBase->query("UPDATE tabdnu SET autorizado = 'S' WHERE siape = '$mat' AND dia = '$dia' ");

    $oDBase->query("SELECT * FROM tabdnu WHERE autorizado = 'N' AND siape = '$mat' ");
    $nrows = $oDBase->num_rows();
    ?>
    <p align='center'>
        <font color='#800000' face='Tahoma' size='4'><br><br>Autorização registrada com sucesso!</font>
    </p>
    <p align='center' style='word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;'>
        <a href='<?= ($nrows == 0 ? "autorizacao_trabalho_dia_nao_util_entra.php" : "autorizacao_trabalho_dia_nao_util.php?qlotacao=".tratarHTML($lot).""); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>tras.gif' alt='Voltar'></a>
    </p>
    <p align='center'>
        <font color='#800000' face='Tahoma' size='4'><br><br>Clique na figura para imprimir a Autorização de entrada! </font>
    </p>
    <p align='center' style='word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;'>
        <a href='autentra.php?siape=<?= tratarHTML($mat); ?>&dia=<?= tratarHTML($dia); ?>' target='new'><img border='0' src='<?= _DIR_IMAGEM_; ?>impremini.gif' alt='Emitir Autorização de Entrada'></a>
    </p>
    <?php
    enviarEmail($email, 'AUTORIZACAO DE TRABALHO EM DIA NAO UTIL', "<br><br><big>Sua solicitação de trabalho para o dia $dia foi autorizada, é necessário apresentar o documento de autorização assinado por sua chefia imediata ao setor responsável pela administração predial.<br> Atenciosamente<br> SISREF.</big><br><br>");
}
elseif ($modo == "7") // grava período de recesso
{

    $inicio = conv_data($_REQUEST['inicio']);
    $fim    = conv_data($_REQUEST['fim']);

    $mensagem = "Período de recesso registrado com sucesso!";

    $oDBase->setMensagem("Falha no registro do período de recesso!");
    $oDBase->query("UPDATE tabvalida SET recessoi = '$inicio',  recessof = '$fim'");

    if ($oDBase->affected_rows() == 0)
    {
        $mensagem = "Período de recesso não registrado!";
    }

    mensagem($mensagem, null, 1);
}
elseif ($modo == "9") // grava quarta feira de cinzas
{

    $qcinza = conv_data($_REQUEST['qcinzas']);

    $oDBase->setMensagem("Falha no registro de quarta feira de cinzas!");
    $oDBase->query("UPDATE tabvalida SET qcinzas = '$qcinza' ");

    mensagem("Registrado com sucesso!", null, 1);
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

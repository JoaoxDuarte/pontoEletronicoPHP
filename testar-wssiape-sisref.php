<?php

// URL: http://10.209.63.129/sisref/testar-wssiape-sisref.php
include_once("config.php");
include_once("Siape.php");

$_SESSION['sMatricula'] = '170000881838';

//Iniciando a sessão:
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

//Gravando valores dentro da sessão aberta:
$_SESSION['mes_inicial'] = '06';
$_SESSION['mes_final']   = '06';
$_SESSION['ano_final']   = '2020';

$cpfs = array('40194795187',
'30858062100','29285275168','62055879715','22096230144','22622519168','22219579115','36682810172','25815903191','43186122791','14998157191','34301976191','15109844100','15340872149','29609313191','22662111100','30748674772','50966120906','28382730191','47135867149','26642417100','23860383191','29138167115','74325540725','04698304768','46391444749','00729854884','52464962953','30315506687','36638722620','35213930620','19124287687','34087060691','24060801615','09443312620','15996638649','35216980687','26270986672','25320653620','19647590644','44569360610','35157577672','39475948600','53900510687','67195105687','48103306649','49835769672','45119007600','49374532620','49164457672','48915602668','23977280600','23973870691','46067531615','57797161615','31585183172','63001144653','94672288820','75667088649','39176045153','62986503691','51130629600','39424448600','43517471615','55889468634','50430416415','50130137715','28967208200','18136230153','32635354553','31715095120','94202125720','21874638420','42459184549','23819243020','26437031034','28909453915','39928012920','21604622920','27181596004','34988637468','27984834172','19989997349','23973021104','35779241953','27144607049','24003611187','11629380300','26876566749','10287426572','23476982300','61420581600','30226171787','09509828300','06272673353','01914843851','40853918953','05574669897','67174582915','94448361268','42120241600','11378676475','05581988994','70437977480','11269908413','04906408850','06126065387','48293601191','11476060134','14949911104','06912397468','15278727187','02155967810','10386874549','19584830520','08532265553','28565525520','18621813149','11804521353','27059740678','41202937934','07646976334','18416802904','24852686149','38520486568','17985951172','21371660115','02255459850','37344803100','23873493187','17032156304','22875670387','29631084787','43287751700','28307615453','45896097972','19621469449','18561152400','12061522149','20630409072','44622880091','29682851149','22119140120','15020592153','34271732168','00803385420','22401326120','12994154315','08818851268','38629267020','06206395200','14667630515','08288054587','13920200420','21056455004','00770375880','63416700791','02076440801','54226414934','12426040220','13525590210','10104470410','04553241404','27521265653','14303434353','22331123004','03255867215','03463974215','17789117491','18650686320','15402193487','08450676215','49395777753','11888199504','07074875368','13386352120','04110978220','07192371220','24582620310','23322209172','09773150291','13004212234','13397265487','05014956220','44524374949','22174664100','28601432115','20204914949','16352114253','03006476204','20286007215','08901333368','16072502415','04191633287','08508801491','04303946249','34532145449','20502281120','38269384615','42184711420','30160049172','21438706200','21477647104','37593544753','18076289491','16796977491','00539686468','06685218487','12369306491','10266429300','02721724215','13470612404','15108848153','08917663320','26679612187','41282868004','26177587453','34984070668','40953297420','25449079320','00267724934','21812136315','11190353253','14464314287','19050313191','14323427204','29225230478','12656348234','04473647234','11267461268','20854919287','02345366249','13941984268','07954093204','13263188372','13913603204','37855832653','35276479453','32482361400','21068135468','05996090287','05427835253','07507083870','05964369200','08006792291','20956720110','25806416615','64716724972','62378708572','62517031304','25299417691','01079898832','23996633149','39796523949','19724942287','32098960972','06271499368','31380654149','40808220730','89863670090','54140280018','18465110115','09652752134','00823231194','48342556891','01067864822','01079819800','68527284804','04339469890','10301700885','68059477820','00672484897','01011353865','11662257805','44155557649','05641635898','04280835870');

$i = 1;
$cpf   = '10085076368'; //$cpfs[$i];

$oDBase = new DataBase();
$oDBase->query( "SELECT mat_siape, LEFT(mat_siape,5) AS orgao FROM servativ WHERE cpf = '".$cpfs[$i]."'" );
$oServativ = $oDBase->fetch_object();
$orgao = '57202'; //$oServativ->orgao;
$mat   = $oServativ->mat_siape;

// codOcorrExclusao	        02500;
// dataOcorrExclusao	    31122019;
// nomeOcorrAposentadoria   ;
// nomeOcorrExclusao	    TRANSFERENCIA SERVIDOR - CSC;
//
//

print $orgao . '<br>';
print $mat . '<br>';

//updateServerBySiape($mat, $exibe_erro = false);
//exit();


$teste = file_get_contents("https://www1.siapenet.gov.br/WSSiapenet/services/ConsultaSIAPE?wsdl");
var_dump($teste);


$obj = new Siape();

// RECUPERA OS DADOS PESSOAIS
$dadosPessoais = $obj->buscarDadosPessoais($cpf , $orgao);

// print_r('<pre>' . print_r($dadosPessoais, 1));
print '<table border="1" cellspacing="0">';
foreach ($dadosPessoais as $key => $Dados)
{
	print '<tr><td>'.$key.'</td><td>'.$Dados.'</td></tr>';
}
print '</table>';
print '<br>';

// RECUPERA OS DADOS FUNCIONAIS
$dadosFuncionais = $obj->buscarDadosFuncionais($cpf , $orgao);
//$situacao_funcional = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional;
print_r('<pre>' . print_r($dadosFuncionais, 1));
// print_r('Situação funcional: '.$situacao_funcional);
print '<table border="1" cellspacing="0">';
foreach ($dadosFuncionais as $Dados1)
{
    foreach ($Dados1 as $Dados2)
    {
        foreach ($Dados2 as $key => $Dados)
        {
            print '<tr><td>'.$key.'</td><td>'.$Dados.'</td></tr>';
        }
    }
}
print '</table>';

exit();

	// RECUPERA O HISTORICO DE AFASTAMENTO
	$dadosAfastamentoHistorico = $obj->buscarDadosAfastamentoHistorico($cpf , $orgao);

	// Conversão de objeto para array
	$myArray = json_decode(json_encode($dadosAfastamentoHistorico), true);
	// print_r('<pre>' . print_r($myArray, 1));
	print '<br>';

	foreach ($myArray as $ArrayDadosAfastamento) {
		foreach ($ArrayDadosAfastamento as $dadosAfastamentoPorMatricula){
			foreach ($dadosAfastamentoPorMatricula as $DadosAfastamentoPorMatricula){
				foreach ($dadosAfastamentoPorMatricula as $ocorrencias){
					foreach ($ocorrencias as $DadosOcorrencias){
						if (is_array($DadosOcorrencias) && (array_key_exists("DadosOcorrencias", $DadosOcorrencias))){
							// print_r('<pre>' . print_r($DadosOcorrencias, 1));
							foreach ($DadosOcorrencias as $DadosOcorrenciasNivel2){
								foreach ($DadosOcorrenciasNivel2 as $DadosOcorrenciasNivel3) {
									foreach ($DadosOcorrenciasNivel3 as $key => $value) {
										// print_r('<pre>' . print_r($value, 1));
										echo "Key {".$key."} value {".$value."}<br/>";
									}
								}
							}
						}
					}
				}
			}
		}
	}

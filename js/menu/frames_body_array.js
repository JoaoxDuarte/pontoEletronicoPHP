/*
 Milonic DHTML Menu
 Written by Andy Woolley
 Copyright 2002 (c) Milonic Solutions. All Rights Reserved.
 Plase vist http://www.milonic.co.uk/menu or e-mail menu3@milonic.com
 You may use this menu on your web site free of charge as long as you place prominent links to http://www.milonic.co.uk/menu and
 your inform us of your intentions with your URL AND ALL copyright notices remain in place in all files including your home page
 Comercial support contracts are available on request if you cannot comply with the above rules.

 Please note that major changes to this file have been made and is not compatible with versions 3.0 or earlier.

 You no longer need to number your menus as in previous versions.
 The new menu structure allows you to name the menu instead, this means that you can remove menus and not break the system.
 The structure should also be much easier to modify, add & remove menus and menu items.

 If you are having difficulty with the menu please read the FAQ at http://www.milonic.co.uk/menu/faq.php before contacting us.

 Please note that the above text CAN be erased if you wish as long as copyright notices remain in place.
 */


function alerta(msg)
{
    alert(msg);
}

function replaceLink(link, sFrame)
{
    var sFrame = (sFrame == null ? 'main' : sFrame);
    if (sFrame == 'main')
    {
        parent.main.location.replace(link);
    }
    else
    {
        window.location.replace(link);
    }
}

function openPDF(link, modo, jan)
{
    var link = (link == null ? '' : link);
    var modo = (modo == null ? 1 : modo);
    var jan = (jan == null ? 'jan' : jan);
    if (link != '')
    {
        //link += '?modo='+modo;
        var jan = window.open(link, jan, 'top=0,left=0,width=799,height=700');
    }
}


//The following line is critical for menu operation, and MUST APPEAR ONLY ONCE. If you have more than one menu_array.js file rem out this line in subsequent files
menunum = 0;
menus = new Array();
_d = document;
function addmenu()
{
    menunum++;
    menus[menunum] = menu;
}
function dumpmenus()
{
    mt = "<script language=javascript>";
    for (a = 1; a < menus.length; a++)
    {
        mt += " menu" + a + "=menus[" + a + "];"
    }
    mt += "<\/script>";
    _d.write(mt)
}
//Please leave the above line intact. The above also needs to be enabled if it not already enabled unless this file is part of a multi pack.



////////////////////////////////////
// Editable properties START here //
////////////////////////////////////


// Special effect string for IE5.5 or above please visit http://www.milonic.co.uk/menu/filters_sample.php for more filters
if (navigator.appVersion.indexOf("MSIE 6.0") > 0)
{
    effect = "Fade(duration=0.2);Alpha(style=0,opacity=88);Shadow(color='#777777', Direction=135, Strength=5)"
}
else
{
    effect = "Shadow(color='#777777', Direction=135, Strength=5)"
}


timegap = 500					// The time delay for menus to remain visible
followspeed = 5				// Follow Scrolling speed
followrate = 40				// Follow Scrolling Rate
suboffset_top = 3;			// Sub menu offset Top position
suboffset_left = -2;			// Sub menu offset Left position
Frames_Top_Offset = 0 		// Frames Page Adjustment for Top
Frames_Left_Offset = 0		// Frames Page Adjustment for Left

var bSistemaCorAzul = (bSistemaCorAzul == null ? false : bSistemaCorAzul);
var off_back_color = (bSistemaCorAzul == false ? "669966" : "#4894D9"); //'#2367A4'
var on_back_color = (bSistemaCorAzul == false ? "336600" : '#2367A4');

plain_style = [// Menu Properties Array
    "FFFFFF", // Off Font Color
    off_back_color, // Off Back Color
    "FFEBCD", // On Font Color
    on_back_color, // On Back Color
    "B9D0B9", // Border Color
    10, // Font Size
    "normal", // Font Style
    "", // Font Weight
    "Verdana, Tahoma, Arial, Helvetica", // Font
    4, // Padding
    "imagem/arrow.gif"					// Sub Menu Image
        , // 3D Border & Separator
    , "66ccff"					// 3D High Color
        , "000099"					// 3D Low Color
]

//---------------------------------------------------------------------------//
//                                                                           //
// CADASTRO                                                                  //
//                                                                           //
//---------------------------------------------------------------------------//

addmenu(menu = ["cadastro"
        , 150, , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Funcional</font>", "show-menu=funcional", , "Cadastro de servidores e estagi�rios", 0
        , "<font color='#FFEBCD'>Gerencial</font>", "show-menu=gerencial", , "Cadastro de ocupantes de fun��o", 0
        , "<font color='#FFEBCD'>Movimenta��o</font>", "show-menu=movimentar", , "Movimentar servidores", 0
])

// CADASTRO -> FUNCIONAL
addmenu(menu = ["funcional"
        , , , 65, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Consultar</font>", "javascript:replaceLink(\"cadastro_consulta.php\")", , "Consultar cadastro de servidores e/ou estagi�rios", 0
        , "<font color='#FFEBCD'>Incluir</font>", "incluiserv.php", , "Incluir servidores e/ou estagi�rios", 0
        , "<font color='#FFEBCD'>Alterar</font>", "cadastro_alteracao.php", , "Alterar cadastro de servidores e/ou estagi�rios", 0
        , "<font color='#FFEBCD'>Excluir</font>", "show-menu=excluir", , "Excluir servidores e/ou estagi�rios", 0
])
addmenu(menu = ["excluir"
        , , , 112, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Efetivar Exclus�o</font>", "cadastro_exclusao.php", , "Exclui servidor/estagi�rio do cadastro", 0
        , "<font color='#FFEBCD'>Cancelar Exclus�o</font>", "cadastro_exclusao_cancela.php", , "Cancela exclusao por erro", 0
])

// CADASTRO -> GERENCIAL
addmenu(menu = ["gerencial"
        , , , 135, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Ocupar Fun��es</font>", "incfuncserv.php", , "Registrar Exerc�cio de Fun��es", 0
        , "<font color='#FFEBCD'>Vagar Fun��es</font>", "excfuncserv.php", , "Registrar Vac�ncia de Fun��es", 1
        , "<font color='#FFFFFF'>Efetivar Substitui��o</font>", "subsfuncinf.php", , "Registrar Efetiva Substitui��o", 0
        , "<font color='#FFFFFF'>Delegar Atribui��o</font>", "show-menu=delegar", , "Delegar atividades do SISREF", 1
        , "<font color='#FFEBCD'>Alterar Registro</font>", "altfuncserv.php", , "Alterar Registrar de Fun��es", 0
        , "<font color='#FFEBCD'>Manter Hist�rico</font>", "manutencao_historico_funcao.php", , "Manuten��o de Hist�rico de Fun��es do Servidor", 0
])
addmenu(menu = ["delegar"
        , , , 125, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "Registrar Delega��o", "delegacao.php?modo=10", , "Delegar atividades do SISREF", 0
        , "Cancelar Delega��o", "delegacao.php?modo=9", , "Cancelamento de delega��o", 0
])

// CADASTRO -> MOVIMENTAR
addmenu(menu = ["movimentar"
        , , , 115, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Alterar Lota��o</font>", "movserv.php", , "Movimentar servidores e estagi�rios", 0
        //,"<font color='#FFEBCD'>Alterar Localiza��o</font>","localserv.php",,"Localizar servidores e estagi�rios",0
        , "<font color='#FFEBCD'>Mudar UPAG</font>", "show-menu=upag", , "Transferir servidor a outra upag.", 0
        , "<font color='#FFEBCD'>Manter Hist�rico</font>", "peshistlot.php", , "Manuten��o de Hist�rico de lota��o", 0
])
addmenu(menu = ["upag"
        , , , 115, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Liberar</font>", "liberupag.php", , "Libera servidor a outra upag", 0
        , "<font color='#FFEBCD'>Cancelar Libera��o</font>", "canliberupag.php", , "Cancela libera��o de servidor", 0
        , "<font color='#FFEBCD'>Receber</font>", "recupag.php", , "Recebe servidor de outra upag", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// FREQUENCIA                                                                //
//                                                                           //
//---------------------------------------------------------------------------//

addmenu(menu = ["frequencia"
        , 200, , 190, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Gerencial</font>", "regfreqger_entra.php", , "Acompanhar unidades", 1
        , "<font color='#FFEBCD'>Acompanhar</font>", "frequencia_acompanhar_entra.php", , "Acompanhar Frequ�ncia", 0
        , "<font color='#FFEBCD'>Homologar</font>", "frequencia_homologar_entra.php", , "Registro de Frequ�ncia", 0
        , "Autoriza��o de Trabalho", "autorizacao_trabalho_dia_nao_util_entra.php", , "Solicita��es de trabalho em dia n�o util", 1
        , "<font color='#FFEBCD'>Visualizar</font>", "show-menu=Visualizar", , "Consulta registro de Frequ�ncia", 1
        , "<font color='#FFEBCD'>RH Atualizar</font>", "show-menu=Manter", , "Manuten��o do registro de Frequ�ncia", 0
])

// FREQUENCIA -> VISUALIZAR
addmenu(menu = ["Visualizar"
        , , , 190, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Folha de Frequ�ncia", "pontoser.php?cmd=1", , "Folha de Frequ�ncia do servidor", 0
        , "Ficha de Frequ�ncia - Resumo Anual", "ficha_de_frequencia_resumo_anual.php", , "Ficha de Frequ�ncia do Servidor", 1
        , "Consulta Frequ�ncia", "veponto.php", , "Consultar folha de Frequ�ncia do servidor", 0
        , "Consulta Extrato Frequ�ncia", "entrada9_individual.php?saldo=1", , "Consultar Extrato Frequ�ncia do servidor", 1
        , "Consulta Compensa��es - Matr�cula (Recessos / Copa do Mundo 2014)", "entrada9_individual.php?saldo=2", , "Consultar Compensa��es do Servidor (Recessos e Copa do Mundo 2014)", 0
        , "Consulta Compensa��es - Todos (Copa do Mundo 2014)", "sisref_relatorio_copa2014.php", , "Consultar Compensa��es do Servidor (Copa do Mundo 2014)", 0
        //,"Altera��o de hor�rio","vesolhor.php",,"Solicita��es de altera��o de hor�rio de Servidor",0
])

// FREQUENCIA -> MANTER
addmenu(menu = ["Manter"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Mes Corrente</font>", "show-menu=Atual", , "", 0
        , "<font color='#FFEBCD'>Mes em Homologa��o</font>", "show-menu=Homol", , "", 1
        , "<font color='#FFEBCD'>Hist�rico</font>", "javascript:replaceLink(\"historico_frequencia.php\")", , "", 1
        , "<font color='#FFEBCD'>Desomologar</font>", "frequencia_verificar_homologados_devolucao.php?tipo=1", , "Devolver Frequ�ncia � chefia para ajuste", 0
        , "<font color='#FFEBCD'>Verificar Homologa��es</font>", "frequencia_verificar_homologados.php", , "Verificar se h� diverg�ncias na Frequ�ncia Homologada.", 0
        //,"Ferramentas","javascript:replaceLink(\"historico_frequencia.php\")",,"",1
])
addmenu(menu = ["Atual"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Incluir Ocorr�ncia", "freqinclui.php", , "Inclus�o de registro", 0
        , "Alterar Ocorr�ncia", "freqaltera.php", , "Altera��o de registro", 0
        , "Excluir Ocorr�ncia", "freqexclui.php", , "Exclus�o de registro", 0
])
addmenu(menu = ["Homol"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Incluir Ocorr�ncia", "freqincluih.php", , "Inclus�o de registro", 0
        , "Alterar Ocorr�ncia", "freqaltera2.php", , "Altera��o de registro", 0
        , "Excluir Ocorr�ncia", "freqexclui2.php", , "Exclus�o de registro", 0
])
addmenu(menu = ["Historico"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"Incluir Ocorr�ncia","freqinclui3.php",,"Inclus�o de registro",0
        , "Incluir Ocorr�ncia", "paginamanutencao.php", , "Inclus�o de registro", 0
        //,"Retificar Frequ�ncia","freqretifica.php",,"Altera��o de registro",0
        , "Retificar Frequ�ncia", "paginamanutencao.php", , "Altera��o de registro", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// RELATORIOS                                                                //
//                                                                           //
//---------------------------------------------------------------------------//

addmenu(menu = ["relatorios"
        , 150, , 125, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Frequ�ncia</font>", "show-menu=freq", , "Relat�rios de Frequ�ncia", 0
        , "<font color='#FFEBCD'>Gerencial</font>", "show-menu=Relacao", , "Relat�rios de fun��es", 0
        , "<font color='#FFEBCD'>Movimenta��o</font>", "show-menu=mov", , "Relat�rios de movimenta��es", 0
])

// RELATORIOS -> FREQ
addmenu(menu = ["freq"
        , , , 170, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Por Ocorr�ncias", "sisref_relatorio_por_ocorrencia.php", , "Relat�rio por Ocorr�ncia", 0
        , "Setores Pendentes", "relfrqsetorp2.php", , "Setores pendentes de lan�amento.", 0
        , "Sem Homologa��o", "sisref_relatorio_frequencia_nao_homologados.php", , "Servidores sem Homologa��o.", 0
        , "Homologados", "relfrqhomol.php", , "Verificar servidores homologados.", 0
        , "Cedidos e Descentralizados", "sisref_relatorio_cedidos_descentralizados.php", , "Verificar servidores cedidos e descentralizados.", 1
        , "<font color='#FFEBCD'>Para Comando</font>", "show-menu=cmd", , "Relat�rios de comando", 1
        , "Insconsist�ncias", "show-menu=erro", , "Relat�rios de verifica��o", 0
])
addmenu(menu = ["cmd"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"Cobran�a","rlsocofreq.php",,"Servidores com Ocorr�ncias para cobran�a.",0
        , "Cobran�a", "sisref_relatorio_ocorrencia_nao_compensada_competencia.php", , "Servidores com Ocorr�ncias para cobran�a.", 0
        , "Comando SIAPECAD", "sisref_relatorio_comando_siapecad_competencia.php", , "Servidores com Ocorr�ncias para comando siapecad", 0
        , "Recesso", "sisref_relatorio_recesso_nao_compensado_competencia.php", , "Servidores que n�o compensaram o recesso", 0
        , "Copa do Mundo 2014", "sisref_relatorio_copa2014_nao_compensado.php", , "Servidores que n�o compensaram horas devidas - Copa do Mundo 2014", 0
])
addmenu(menu = ["erro"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Dias Zerados", "rlsdiazero.php", , "Servidores com dia zerado.", 0
        //,"Comando siapecad","lsocosiapcd.php",,"Servidores com Ocorr�ncias para comando siapecad",0
])

// RELATORIOS -> RELACAO
addmenu(menu = ["Relacao"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Ocupantes de Fun��es", "ocupfuncserv.php", , "Consulta Ocupantes de Fun��o", 0
        , "Historico de Fun��es", "histfuncserv.php", , "Hist�rico de Fun��es do Servidor", 0
        , "Historico de Ocupantes", "histfuncserv2.php", , "Hist�rico Ocupantes de Fun��es", 0
        , "<font color='#FFEBCD'>Substitui��es</font>", "show-menu=sub", , "Verificar substitui��es", 0
])
addmenu(menu = ["sub",
    , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Substitui��es na UPAG</font>", "sisref_relatorio_substituicoes.php", , "Consulta Substitui��es efetivadas", 0
        , "<font color='#FFEBCD'>Substitui��es do Servidor</font>", "pessubs.php", , "Consulta substitui��es do servidor", 0

])

// RELATORIOS -> MOV
addmenu(menu = ["mov"
        , , , 145, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Consulta Hist�rico</font>", "sisref_relatorio_consulta_movimentacao_servidor.php", , "Consulta hist�rico de movimenta��o", 0
        , "<font color='#FFEBCD'>Consulta Movimenta��o</font>", "pesmov.php", , "Consulta movimenta��es", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// EXTRAORDINARIO                                                            //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["Extra"
        , 150, , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "Autoriza��o", "relautextra_entra.php", , "Autorizar extraordin�rio", 0
        , "Execu��o", "relextrahom_entra.php", , "homologar extraordin�rio", 0
        , "Pagamento", "paginadesen.php", , "Emitir extraordin�rio", 0
        //,"<font color='#FFEBCD'>Movimenta��o</font>","show-menu=mov",,"Relat�rios de movimenta��es",0
])


//---------------------------------------------------------------------------//
//                                                                           //
// TABELAS                                                                   //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["tabelas",
    , , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Lota��es</font>", "tablota.php", , "Tabela de lota��es", 0 //salario
        , "<font color='#FFEBCD'>Fun��es</font>", "tablfunca.php", , "Tabela de fun��es", 0 //salario
        , "<font color='#FFEBCD'>Feriados</font>", "tabferiados.php", , "Tabela de feriados", 0 //salario
        , "<font color='#FFEBCD'>Ocorr�ncias</font>", "show-menu=ocorrencias", , "", 0
])

// TABELAS -> OCORRENCIAS
addmenu(menu = ["ocorrencias"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"<font color='#FFEBCD'>Incluir</font>","tabocfrei.php",,"Inclui nova Ocorr�ncia",0
        , "<font color='#FFEBCD'>Incluir</font>", "", , "Inclui nova Ocorr�ncia", 0
        , "<font color='#FFEBCD'>Consultar / Alterar</font>", "tabela_ocorrencia_de_frequencia_visualizar.php", , "Consulta e Altera Ocorr�ncias", 0
        //,"<font color='#FFEBCD'>Consultar / Alterar</font>","tabocfre.php",,"Consulta e Altera Ocorr�ncias",0
]);


//---------------------------------------------------------------------------//
//                                                                           //
// UTILITARIOS                                                               //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["senhas"
        , , , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        //,"Usu�rios","acessoi.php",,"Incluir um novo usu�rio do sistema",0
        , "<font color='#FFEBCD'>Usu�rios</font>", "show-menu=usuarios", , "Manuten��o de usu�rios", 0
        , "<font color='#FFEBCD'>Auditoria</font>", "show-menu=auditoria", , "Verifica��es no sistema", 0
        , "<font color='#FFEBCD'>Gestores</font>", "show-menu=gestores", , "M�dulos de uso dos gestores", 0
])

// UTILITARIOS -> USUARIOS / SENHAS
addmenu(menu = ["usuarios"
        , , , 140, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"<font color='#FFEBCD'>Incluir Usu�rio</font>","usuario_inc.php",,"Incluir novo usu�rio",0
        , "<font color='#FFEBCD'>Alterar/Excluir Usu�rio</font>", "javascript:replaceLink(\"usuario_lista.php\")", , "Alterar/Excluir usu�rios", 0
        , "<font color='#FFEBCD'>Reiniciar Senhas</font>", "reiniciar1.php", , "Reinicializar senhas", 0
        , "<font color='#FFEBCD'>Trocar Senha</font>", "trocasenha.php", , "Trocar senha", 0
])
// fim senhas e usuarios

// UTILITARIOS -> AUDITORIA
addmenu(menu = ["auditoria"
        , , , 140, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Opera��es de Usu�rio</font>", "perrellog.php", , "Pesquisar opera��es", 0
        , "<font color='#FFEBCD'>Identificar IP</font>", "pesquisa_ip.php", , "Pesquisar IP de registro", 0
        , "<font color='#FFEBCD'>Opera��es Ilegais</font>", "pesilegal.php", , "Pesquisar opera��es ilegais", 0
        , "<font color='#FFEBCD'>Registros Alterados</font>", "show-menu=regalt", , "Servidores com registros alterados ou exclu�dos", 0
])
addmenu(menu = ["regalt"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Altera��es de Frequ�ncia</font>", "veregalterado.php", , "Servidores com registros de Frequ�ncia alterados ou exclu�dos", 0
        , "<font color='#FFEBCD'>Altera��es de Cadastro</font>", "vecadalt.php", , "Servidores com cadastro alterado", 0
])

// UTILITARIOS -> GESTORES
addmenu(menu = ["gestores",
    , , 185, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Corrigir Acesso do Usu�rio</font>", "gestao_acesso_usuario.php", , "Corrigir problemas de acesso do usu�rio", 0
        , "<font color='#FFEBCD'>Liberar Homologa��o</font>", "gestao_libera_homologacao.php", , "Liberar Setor para Homologa��o", 0
        , "<font color='#FFEBCD'>Reinicializar Senha do Usu�rio</font>", "gestao_trocasenha_usuario.php", , "Reiniciar a Senha do Usu�rio", 1
        , "Prazos", "tabvalida.php", , "Tabela de Prazos do Sistema", 0 //salario
        , "Importar Ocorr�ncias", "atualizaocor.php", , "Importa Ocorr�ncias do siape", 0 //salario
        , "Recalcular Recesso", "atualizarecessobhoras_entra.php", , "Recalcular compensa��o do recesso", 0 //salario
        , "<font color='#FFEBCD'>Corrigir Substitui��o</font>", "suporte_substituicao1.php", , "Corrigir problemas com substitui��o", 0
        , "<font color='#FFEBCD'>Acertar Ficha Anual</font>", "atualiza_ficha_anual_individual.php", , "Corrigir problemas na ficha anual", 0

])


//---------------------------------------------------------------------------//
//                                                                           //
// ESTRATEGICA                                                               //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["estrategica"
        , , , 155, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Consulta Frequ�ncia", "veponto.php", , "Consulta a Frequ�ncia do servidor", 0
//		,"Paralisa��es/Faltas (M�dicos)","show-menu=estrategica_faltas",,"Relat�rio de Paralisa��es/Faltas (M�dicos)",0
        , "Paralisa��es/Faltas", "relatorio_paralisacoes_quadro.php", , "Relat�rio de Paralisa��es/Faltas (M�dicos)", 0
        , "Registro Fora do Hor�rio", "relatorio_registro_fora_horario.php", , "Relat�rio de Registros fora do Hor�rio", 1
        , "Quadro de Hor�rio", "relatorio_registro_grade_horario.php", , "Grade de Hor�rio das Unidades", 0
])
addmenu(menu = ["estrategica_faltas"
        , , , 155, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Todo o Brasil", "relatorio_paralisacoes.php?modo=1", , "Lista Paralisa��es", 0
        , "Administra��o Central", "relatorio_paralisacoes.php?modo=2", , "Lista Paralisa��es", 0
        , "Superintend�ncia Regional", "relatorio_paralisacoes.php?modo=3", , "Lista Paralisa��es", 0
        , "Ger�ncias Executivas", "relatorio_paralisacoes.php?modo=4", , "Lista Paralisa��es", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// ENCERRA A SE��O - FINALIZA O USO DA APLICA��O                             //
//                                                                           //
//---------------------------------------------------------------------------//

// sair
addmenu(menu = ["encerrar_sessao",
    , , 138, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "Encerrar Sess�o", "finaliza2.php?modulo=" + modulo_ativado, , "Finaliza a sess�o, encerra o uso do sistema.", 0
])
// fim usuarios



dumpmenus()
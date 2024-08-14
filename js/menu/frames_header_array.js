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
suboffset_top = 10;			// Sub menu offset Top position
suboffset_left = 10;			// Sub menu offset Left position
Frames_Top_Offset = 0 		// Frames Page Adjustment for Top
Frames_Left_Offset = 100		// Frames Page Adjustment for Left

var bSistemaCorAzul = (bSistemaCorAzul == null ? false : bSistemaCorAzul);
var off_back_color = (bSistemaCorAzul == false ? "669966" : "#4894D9"); //'#2367A4'
var on_back_color = (bSistemaCorAzul == false ? "336600" : '#2367A4');

var pos_menu_top = (bSistemaCorAzul == false ? 116 : 80);
var pos_menu_left = 0;

plain_style = [// Menu Properties Array
    "FFFFFF", // Off Font Color
    off_back_color, // Off Back Color
    "FFEBCD", // On Font Color
    on_back_color, // On Back Color
    "808080", // Border Color
    10, // Font Size
    "normal", // Font Style
    "", // Font Weight
    "Verdana, Tahoma, Arial, Helvetica", // Font
    4, // Padding
    "imagem/arrow.gif"	// Sub Menu Image
        , // 3D Border & Separator
    , "66ccff"						// 3D High Color
        , "000099"						// 3D Low Color
]

var tam_da_tela = screen.width;
switch (tam_da_tela)
{
    case 640:
    case 800:
        pos_menu_left = 109 + 115;
        break;
    case 960:
        pos_menu_left = 163 + 115;
        break;
    case 1024:
        pos_menu_left = 195 + 115;
        break;
    case 1280:
        pos_menu_left = 323 + 115;
        break;
    case 1440:
    default:
        pos_menu_left = 403 + 115;
        break;
}

addmenu(menu = [// This is the array that contains your menu properties and details
    "simplemenu1", // Menu items Name
    pos_menu_top, // Top
    pos_menu_left, //309,				// left
    , // Width
        1, // Border Width
    "center", // Screen Position - here you can use "center;left;right;middle;top;bottom"
    plain_style, // Properties Array - this is set higher up, as above
    1, // Always Visible - allows the menu item to be visible at all time
    "left", // Alignment - sets the menu elements alignment, HTML values are valid here for example: left, right or center
    effect, // Filter - Text variable for setting transitional effects on menu activation
    , // Follow Scrolling - Tells the menu item to follow the user down the screen
        1, // Horizontal Menu - Tells the menu to be horizontal instead of top to bottom style
    , // Keep Alive - Keeps the menu visible until the user moves over another menu or clicks elsewhere on the page
        , // Position of sub image left:center:right:middle:top:bottom
        , // Show an image on top menu bars indicating a sub menu exists below
        , // Reserved for future use
        , // Reserved for future use
        , // Reserved for future use
        , , ,
        , "Cadastro", "show-menu=cadastro target=main;sourceframe=main", , "Cadastro de Servidores", 1  // "Description Text", "URL", "Alternate URL", "Status", "Separator Bar"
        , "Frequência", "show-menu=frequencia target=main;sourceframe=main", , "Controle de Frequência", 1  // "Description Text", "URL", "Alternate URL", "Status", "Separator Bar"
        , "Relatórios", "show-menu=relatorios target=main;sourceframe=main", , "Relatórios Gerenciais", 1
//,"Extraordinário","show-menu=Extra target=main;sourceframe=main",,"Serviço Extra",1
        , "Tabelas", "show-menu=tabelas target=main;sourceframe=main", , "Tabelas", 1
        , "Utilitários", "show-menu=senhas target=main;sourceframe=main", , "Acesso ao Sistema (Inclusão, Troca de senha)", 1
        , "Gestão&nbsp;Estratégica", "show-menu=estrategica target=main;sourceframe=main", , "Gestão Estratégica", 1
        , "&nbsp;Sair", "show-menu=encerrar_sessao target=main;sourceframe=main", , "Finaliza a sessão, encerra o uso so sitema.", 0
])


dumpmenus()

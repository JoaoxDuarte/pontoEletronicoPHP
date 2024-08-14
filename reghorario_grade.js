
function imprimirGrade()
{
    var tela = null;
    
    // dados
    $("#imagemPrinter").css( "display", "none" );
    
    tela = window.print();
    
    $("#imagemPrinter").css( "display", "" );
}

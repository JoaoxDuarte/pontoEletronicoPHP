
//var msg = 'Função desativada!';

//ESSAS SERÃO AS TECLAS DESABILITADAS
var asciiF2 = 113;
var asciiF3 = 114;
var asciiF4 = 115;
var asciiF5 = 116;
var asciiF6 = 117;
var asciiF11 = 122;
var asciiF12 = 123;
var asciiF1 = 112;

//ie has to block in the key down
if (document.all)
{
    document.onkeydown = onKeyPress;
    //NS and mozilla have to block in the key press
}
else if (document.layers || document.getElementById)
{
    document.onkeypress = onKeyPress;
}

function onKeyPress(evt)
{
    window.status = '';
    //get the event object
    var oEvent = (window.event) ? window.event : evt;

    //hmmm in mozilla this is jacked, so i have to record these seperate
    //what key was pressed
    var nKeyCode = oEvent.keyCode ? oEvent.keyCode : oEvent.which ? oEvent.which : void 0;
    var bIsFunctionKey = false;

    //hmmm in mozilla the keycode would contain a function key ONLY IF the charcode IS 0
    //else key code and charcode read funny, the charcode for 't'
    //returns 116, which is the same as the ascii for F5
    //SOOO,... to check if a the keycode is truly a function key,
    //ONLY check when the charcode is null OR 0, IE returns null, mozilla returns 0
    if (oEvent.charCode == null || oEvent.charCode == 0)
    {
        bIsFunctionKey = (nKeyCode >= asciiF1 && nKeyCode <= asciiF12)
    }

    //convert the key to a character, makes for more readable code
    var sChar = String.fromCharCode(nKeyCode).toUpperCase();

    //get the active tag that has the focus on the page, and its tag type
    var oTarget = (oEvent.target) ? oEvent.target : oEvent.srcElement;
    var sTag = oTarget.tagName.toLowerCase();
    var sTagType = oTarget.getAttribute("type");

    var bAltPressed = (oEvent.altKey) ? oEvent.altKey : oEvent.modifiers & 1 > 0;
    var bShiftPressed = (oEvent.shiftKey) ? oEvent.shiftKey : oEvent.modifiers & 4 > 0;
    var bCtrlPressed = (oEvent.ctrlKey) ? oEvent.ctrlKey : oEvent.modifiers & 2 > 0;

    var bRet = true; //assume true as that will be the case most times
    //alert (nKeyCode + ' ' + sChar + ' ' + sTag + ' ' + sTagType + ' ' + bShiftPressed + ' ' + bCtrlPressed + ' ' + bAltPressed);

    if (sTagType != null)
    {
        sTagType = sTagType.toLowerCase();
    }

    if (bIsFunctionKey)
    { // Capture and stop these keys
        bRet = false;
    }
    else if (bCtrlPressed && (sChar == 'R'))
    { // block Control + R
        bRet = false;
    }

    if (!bRet)
    {
        try
        {
            oEvent.returnValue = false;
            oEvent.cancelBubble = true;

            if (document.all)
            { //IE
                oEvent.keyCode = 0;
            }
            else
            { //NS
                oEvent.preventDefault();
                oEvent.stopPropagation();
            }
            //alert(msg);
        }
        catch (ex)
        {
            //alert(msg);
        }
    }
    return bRet;
}

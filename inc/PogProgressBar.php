<?php

/**
 * POG Progress Bar
 *
 * Required
 * 		- Is required the aplication support flush.
 * 		- Only call the method PogProgressBar::setProgress() where can be print
 * 		a javascript command.
 *
 * Recommendations
 * 		- The call to the metohd PogProgressBar::setProgress() be maded the most
 * 		possible close of the script end.
 * 		- When the process to be execute is to big, is necessary to configure
 * 		  the set_time_limit.
 *
 * Public Methods
 * 		- draw()		Draw the HTML of the progress bar.
 * 		- getProgress()	Returns the actual value of the percent done.
 * 		- setProgress()	Change the actual value of the percent done into range
 * 						from 0 to 100.
 * 		- setTheme 		change the actual theme (colors, styles) of the bar.
 *
 * ----- Português ------
 *
 * Requisitos
 * 		- É necessário que a aplicação suporte flush implícito.
 * 		- A chamada do método PogProgressBar::setProgress() deve ser realizada
 * 		  em uma área onde seja possível imprimir um código javascript.
 *
 * Recomendações
 * 		- A chamada do método setProgress() seja realizada o mais próxima
 * 		  possível do fim do script.
 * 		- Quando o processo a ser executada é muito grande é necessário
 * 		  configurar o set_time_limit
 *
 * Métodos Públicos
 * 		- draw()		Desenha o html da barra de progresso.
 * 		- getProgress()	Valor atual do progresso em porcentagem.
 * 		- setProgress()	Altera valor do progresso em porcentagem de 0 a 100.
 * 		- setTheme()	Altera tema (cores, estilos) da barra.
 *
 * @author Renan de Lima <renandelima@gmail.com>
 * @author Thiago Mata <thiago.henrique.mata@gmail.com>
 * @version 0.1


  EXEMPLO:

  $nrows = mysql_num_rows($rx);

  include_once("PogProgressBar.php");
  $objBar = new PogProgressBar( 'pb' );
  $objBar->setTheme( 'green');

  $objBar->draw( 'Aguarde, preparando Relatório '.$tpage );

  if ($nrows>0) {

  while (list(...) = mysql_fetch_array($rx))
  {

  $nlinha++;

  $objBar->setProgress( $nlinha * 100 / $nrows );
  usleep( 40 );

  } // while

  } // numrows

  if ($nrows>0) {
  $objBar->hide();
  }

 */
class PogProgressBar
{

    /**
     * Array with the name of all progress bar maded.
     *
     * Array com os nomes de todas as barras de progresso instanciadas.
     *
     * @var array
     */
    var $arrNames = array();

    /**
     * Array with the name of all javascript classes declared who controls the
     * progress bars.
     *
     * Nome das classes javascript declaradas que controla o progresso da barra.
     *
     * @var array
     */
    var $arrJsClasses = array();

    /**
     * Array with the options of theme to the bar.
     *
     * Temas possíveis para a barra.
     *
     * @var array
     */
    var $arrThemes = array(
        'basic' => array(
            'container' => 'border:0px solid #b0b0b0;background-color:#FFFFFF;height:10px;width:300px;',
            'bgbar'     => 'border:1px solid #b0b0b0;background-color:#d0d0d0;height:10px;width:300px;',
            'bar'       => 'white-space:nowrap;background-color:#f0f0f0;height:10px;width:0px;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;',
            'row'       => 'white-space:nowrap;background-color:transparent;height:10px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;'
        ),
        'blue'  => array(
            'container' => 'border:0px solid #aaaaff;background-color:#FFFFFF;height:10px;width:300px;',
            'bgbar'     => 'border:1px solid #aaaaff;background-color:#ddddff;height:10px;width:300px;',
            'bar'       => 'white-space:nowrap;background-color:#5050ff;height:10px;width:0px;color:white;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;',
            'row'       => 'white-space:nowrap;background-color:transparent;height:10px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;'
        ),
        'green' => array(
            'container' => 'border:0px solid #50aa50;background-color:#FFFFFF;height:10px;width:300px;',
            'bgbar'     => 'border:1px solid #50aa50;background-color:#ddffdd;height:10px;width:300px;',
            'bar'       => 'white-space:nowrap;background-color:#30aa30;height:10px;width:0px;color:white;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;',
            'row'       => 'white-space:nowrap;background-color:transparent;height:10px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;'
        ),
        'ocre'  => array(
            'container' => 'border:0px solid #ABAB58; background-color:#FFFFFF; height:10px; width:300px;',
            'bgbar'     => 'border:1px solid #ABAB58; background-color:#F3F3E9; height:10px; width:300px;',
            'bar'       => 'white-space:nowrap; background-color:#949449; height:10px; width:0px; color:white; font-size:8px; font-family:verdana; text-align:center; font-weight:bold;',
            'row'       => 'white-space:nowrap;background-color:transparent;height:10px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;'
        ),
        'red'   => array(
            'container' => 'border:0px solid #dd0000;background-color:#FFFFFF;height:10px;width:300px;',
            'bgbar'     => 'border:1px solid #dd0000;background-color:#ffdddd;height:10px;width:300px;',
            'bar'       => 'white-space:nowrap;background-color:#ff0011;height:10px;width:0px;color:white;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;',
            'row'       => 'white-space:nowrap;background-color:transparent;height:10px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;'
        )
    );

    /**
     * Actual percent value.
     *
     * Porcentagem atual.
     *
     * @var float
     */
    var $fltPercent = 0;

    /**
     * Name of the javascript class used to control the progress bar.
     *
     * Nome da classe javascript utilizada que controla o progresso da barra.
     *
     * @var string
     */
    var $strJsClass = 'PogProgressBar';

    /**
     * Name of the progress bar.
     *
     * Nome da barra de progresso.
     *
     * @var string
     */
    var $strName = '';

    /**
     * Theme choose to the progress bar
     *
     * Tema utilizado pela barra de progresso.
     *
     * @var string
     */
    var $strTheme = 'basic';

    /**
     * Initialize the progress bar.
     *
     * Inicia a barra de progressão.
     *
     * @param string $strName
     * @return void
     */
    function PogProgressBar($strName)
    {
        $this->strName    = (string) $strName;
        $this->arrNames[] = $this->strName;

    }

    /**
     * Draw the progress bar. This method should be call after the choose of the
     * Theme.
     *
     * Desenha a barra de progressão. Esse método deve ser chamado depois de ter
     * configurado o tema
     *
     * @see PogProgressBar::setTheme()
     * @return void
     */
    function draw($titulo, $top = 0, $left = 0, $wscreen = 1024)
    {
        $stylePosition = (empty($top) ? "" : "position: relative; top: " . $top . "; left: " . $left . ";");
        $left          = (empty($left) ? ($wscreen - 300) / 2 : $left);
        $arrTheme      = $this->arrThemes[$this->strTheme];
        ?>
        <div id="pbContainer<?= $this->getSufix(); ?>" style="<?= $stylePosition . $arrTheme['container']; ?>; display: ; z-index: 10000;">
            <table align="center" cellpadding="0" cellspacing="2" border="0" style='width: 300px; border: 2px outset #f4f4f4; background-color: #ffffff;'>
                <tr><td style="border: 0px;"><?= $titulo; ?></td></tr>
                <tr><td style='text-align: center;'>
                        <div id="pbBar<?= $this->getSufix(); ?>_top" style="display: none; <?= $arrTheme['row']; ?>">&nbsp;</div><br>
                    </td></tr>
                <tr><td style="<?= $arrTheme['bgbar']; ?>">
                        <div id="pbBar<?= $this->getSufix(); ?>" style="<?= $arrTheme['bar']; ?>"></div>
                    </td></tr>
                <tr><td style='text-align: center;'>
                        <div id="pbBar<?= $this->getSufix(); ?>_row" style="<?= $arrTheme['row']; ?>">Registros: </div>
                    </td></tr>
                <tr><td style='text-align: center;'>
                        <div id="pbBar<?= $this->getSufix(); ?>_bottom" style="display: none; <?= $arrTheme['row']; ?>">&nbsp;</div>
                    </td></tr>
            </table>
        </div>
        <?php
        $this->drawJsLibrary();
        $this->flush();

    }

    /**
     * Declare the javascript class who will control the progress bar. To
     * replace the controller you must overwrite this method since the
     * javascript class declare have the method refresh who receive the percent
     * value into a range from 0 to 100.
     *
     * Declara a classe javascript que controla a barra de progressão. Para
     * substituir o controlador você deve sobreescrever esse método de forma que
     * a classe javascript declarada contenha o método "refresh" que receba a
     * porcentagem de 0 a 100.
     *
     * @return void
     */
    function drawJsClass()
    {
        ?>
        <script type="text/javascript">

            var startday = new Date();
            var clockStart = startday.getTime();

            function <?= $this->strJsClass; ?>(strSufix)
            {

                this.construct = function construct(strSufix)
                {
                    this.objBar = $('#pbBar' + strSufix);
                    this.objBarRow = $('#pbBar' + strSufix + '_row');
                    this.objBarTop = $('#pbBar' + strSufix + '_top');
                    this.objBarBottom = $('#pbBar' + strSufix + '_bottom');
                    this.objContainer = $('#pbContainer' + strSufix);
                    this.intPercent = 0;
                }

                this.refresh = function refresh(fltPercent, fltRow, fltTotal, fltTempo, fltTexto)
                {
                    var fltTempo = (fltTempo == null ? 'bottom' : fltTempo);
                    var fltTexto = (fltTexto == null ? '' : fltTexto);
                    this.intPercent = parseInt(fltPercent);
                    this.intRow = parseInt(fltRow);
                    this.intTotal = parseInt(fltTotal);
                    this.objBar.innerHTML = this.intPercent.toFixed(0) + ' %';
                    this.objBar.style.width = ((this.intPercent / 100) * (this.objContainer.offsetWidth - 2)) + 'px';

                    var vr = number_format(this.intRow);
                    var tt = number_format(this.intTotal);

                    var myTime = new Date();
                    var timeNow = myTime.getTime();
                    var timeDiff = timeNow - clockStart;

                    var dfm = '0';
                    var dfs = '00';

                    var min = 0;

                    this.diffSecs = tempo(timeDiff / 1000);

                    this.objBarRow.innerHTML = 'Registros: ' + vr + ' / ' + tt; // + '<br>Tempo: ' + this.diffSecs;
                    switch (fltTempo)
                    {
                        case 'top':
                            this.objBarTop.innerHTML = (fltTexto == '' ? '' : '<b>' + fltTexto + '</b><br>') + 'Tempo: ' + this.diffSecs;
                            this.objBarTop.style.display = '';
                            this.objBarBottom.innerHTML = '';
                            this.objBarBottom.style.display = 'none';
                            break;
                        case 'bottom':
                            this.objBarTop.innerHTML = '';
                            this.objBarTop.style.display = 'none';
                            this.objBarBottom.innerHTML = (fltTexto == '' ? '' : '<b>' + fltTexto + '</b><br>') + 'Tempo: ' + this.diffSecs;
                            this.objBarBottom.style.display = '';
                            break;
                    }

                }

                this.construct(strSufix);

            }

            function number_format(intRow)
            {
                var vr = new String(intRow.toFixed(0));
                var vr2 = "";
                var tam = vr.length;
                if (tam >= 16)
                {
                    vr2 += vr.substr(0, tam - 15) + '.';
                }
                if (tam >= 13)
                {
                    vr2 += (tam <= 15 ? vr.substr(0, tam - 12) : vr.substr(tam - 15, 3)) + '.';
                }
                if (tam >= 10)
                {
                    vr2 += (tam <= 12 ? vr.substr(0, tam - 9) : vr.substr(tam - 12, 3)) + '.';
                }
                if (tam >= 7)
                {
                    vr2 += (tam <= 9 ? vr.substr(0, tam - 6) : vr.substr(tam - 9, 3)) + '.';
                }
                if (tam >= 4)
                {
                    vr2 += (tam <= 6 ? vr.substr(0, tam - 3) : vr.substr(tam - 6, 3)) + '.' + vr.substr(tam - 3, 3)
                }
                if (tam <= 3)
                {
                    vr2 = vr
                }
                return vr2;
            }

            function tempo(vr)
            {
                // Hora(s)
                var h = parseInt(vr / 3600);
                h = (isNaN(h) ? 0 : (isFinite(h) ? h : 0));
                // Minuto(s)
                vr = vr - (3600 * h);
                var m = parseInt((vr / 60));
                m = (isNaN(m) ? 0 : (isFinite(m) ? m : 0));
                // Segundo(s)
                vr = vr - (60 * m);
                var s = parseInt(vr);
                s = (isNaN(s) ? 0 : (isFinite(s) ? s : 0));
                //
                var sh = new String(h);
                var sm = new String(m);
                var ss = new String(s);
                var hms = ' seg(s)'
                if (h == 0 && m == 0)
                {
                    sh = '';
                    sm = '';
                }
                else
                {
                    ss = (s > 9 ? '' : '0') + ss;
                    sm = sm + ':';
                    hms = ' (mm:ss)';
                    if (h == 0)
                    {
                        sh = '';
                    }
                    else
                    {
                        sm = (m > 9 ? '' : '0') + sm + ':';
                        sh = sh + ':';
                        hms = ' (hh:mm:ss)';
                    }
                }
                return sh + sm + ss + hms;
            }

        </script>
        <?php

    }

    /**
     * Initialize the javascript required to manipulate the progress bar.
     *
     * Inicia javascript necessário para a manipulação da barra de progressão.
     *
     * @return void
     */
    function drawJsLibrary()
    {
        if (!in_array($this->strJsClass, $this->arrJsClasses))
        {
            $this->drawJsClass();
            array_push($this->arrJsClasses, $this->strJsClass);
        }
        ?>
        <script type="text/javascript">
            pb<?= $this->getSufix(); ?> = new <?= $this->strJsClass; ?>('<?= $this->getSufix(); ?>');
        </script>
        <?php

    }

    /**
     * Print all the buffer content.
     *
     * Imprime tudo que estiver em buffer.
     *
     * @return void
     */
    function flush()
    {
        while (ob_get_level())
        {
            ob_end_flush();
        }
        flush();

    }

    /**
     * Returns the progress actual.
     *
     * Captura o progresso atual.
     *
     * @return float
     */
    function getProgress()
    {
        return $this->fltPercent;

    }

    /**
     * Returns the sufix from the bar. Can be used as unique identifier to the
     * bar.
     *
     * Captura o sufixo da barra. Pode ser utilizado como identificador único da
     * barra.
     *
     * @return float
     */
    function getSufix()
    {
        return array_search($this->strName, $this->arrNames);

    }

    /**
     * Change the progress var value. The controller of the bar into the client
     * side is defined into the method drawJsClass.
     *
     * Altera o progresso da barra. O controle da barra, propriamente dito, é
     * realizado pela classe javascript definida no método drawJsClass.
     *
     * @see PogProgressBar::drawJsClass()
     * @param float $fltPercent
     * @return void
     */
    function setProgress($fltPercent, $fltRow = 0, $fltTotal = 0, $fltTempo = '', $fltTexto = '')
    {
        $fltPercent = tiravirgula(tiraponto($fltPercent));
        if ($fltPercent == $this->fltPercent || $fltPercent > 100 || $fltPercent < 0)
        {
            return;
        }
        $this->fltPercent = $fltPercent;
        ?>
        <script type="text/javascript">
            pb<?= $this->getSufix(); ?>.refresh(<?= $fltPercent; ?>, <?= $fltRow; ?>, <?= $fltTotal; ?>, '<?= $fltTempo; ?>', '<?= $fltTexto; ?>');
        </script>
        <?php
        $this->flush();

    }

    /**
     * Change the theme of the progress bar. This method must be called before
     * the draw, otherwise this method will have no usefull effect.
     *
     * Altera o tema da barra. Este método deve ser utilizado antes da chamada
     * do draw, se for chamado depois a alteração não surtirá efeito.
     *
     * @see PogProgressBar::draw()
     * @param string $strTheme
     */
    function setTheme($strTheme)
    {
        if (!array_key_exists($strTheme, $this->arrThemes))
        {
            return;
        }
        $this->strTheme = $strTheme;

    }

    function hide()
    {
        print "
		<script type='text/javascript'>
			document.getElementById( 'pbContainer' + " . $this->getSufix() . " ).style.display = 'none';
		</script>";

    }

}

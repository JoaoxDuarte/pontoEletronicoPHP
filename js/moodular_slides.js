jQuery(document).ready(function ()
{
    jQuery('#mensagens_comunicacao_social').moodular({
        controls: 'index stopOver',
        indexElement: jQuery('#index'),
        effects: 'left',
        direction: 'left',
        auto: true,
        speed: 1000,
        dispTimeout: 3200
    });
});
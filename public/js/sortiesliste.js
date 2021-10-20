$(document).ready( function () {
    moment.locale('fr');
    $('#filter_start').val('')
    $('#filter_start').datetimepicker({
        format: 'YYYY/MM/DD  HH:mm',
        useCurrent: false,
        widgetPositioning : {
            horizontal: 'auto',
            vertical: 'top',
        },
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
        } 
    });
    $('#filter_close').val('')
    $('#filter_close').datetimepicker({
        format: 'YYYY/MM/DD  HH:mm',
        useCurrent: false,
        widgetPositioning : {
            horizontal: 'auto',
            vertical: 'top',
        },
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
        } 
    });

    $("#filter_start").on("change.datetimepicker", function (e) {
        $('#filter_close').datetimepicker('minDate', e.date);
    });
    $("#filter_close").on("change.datetimepicker", function (e) {
        $('#filter_start').datetimepicker('maxDate', e.date);
    });

    $("#cardfilterheader").click(function (){
        $("#cardfilter").toggle();
        $("#down").toggle();
        $("#up").toggle();
    })


});
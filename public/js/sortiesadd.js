$(document).ready( function () {
    moment.locale('fr');
    $('#sorties_datedebut').datetimepicker({
        format: 'YYYY/MM/DD HH:mm',
        useCurrent: false,
        widgetPositioning : {
            horizontal: 'auto',
            vertical: 'top',
        },
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
        },
        minDate : {
            moment : true
        }
    });
    $('#sorties_datecloture').datetimepicker({
        format: 'YYYY/MM/DD',
        useCurrent: false,
        widgetPositioning : {
            horizontal: 'auto',
            vertical: 'top',
        },
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
        },
        minDate : {
            moment : true
        }
    });

    $("#sorties_datecloture").on("change.datetimepicker", function (e) {
        $('#sorties_datedebut').datetimepicker('minDate', e.date);
    });
    $("#sorties_datedebut").on("change.datetimepicker", function (e) {
        $('#sorties_datecloture').datetimepicker('maxDate', e.date);
    });
    $("#cree_sortie_lieu").change(function(){
        var lieu = $(this).val();

        $.ajax({
            url: 'http://localhost:8080/public/sortie/add',
            type: 'post',
            data: {depart:lieu},
            dataType: 'json',
            success:function(response){

                $("#informations_lieu").empty();
                for( var i = 0; i<len; i++){
                    var id = response[i]['id'];
                    var name = response[i]['name'];

                    $("#informations_lieu").append("<option value='"+id+"'>"+name+"</option>");

                }
            }
        });
    });
});

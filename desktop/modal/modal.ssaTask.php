<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
include_file('3rdparty', 'visjs/vis.min', 'css');
include_file('3rdparty', 'visjs/vis.min', 'js');

$Cmd=$_GET['ssa'];

$date = array(
  'start' => init('startDate', date('Y-m-d', strtotime(config::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d')))),
  'end' => init('endDate', date('Y-m-d')),
);



?>  
<div class="md_history">
  <input id="in_startDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 150px;" value="<?php echo $date['start']; ?>"/>
  <input id="in_endDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 150px;" value="<?php echo $date['end']; ?>"/>
  <a class="btn btn-success btn-sm tooltips" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
 
  <center><div id="visualization"></div></center>
        
       
</div>



<script type="text/javascript">
  // DOM element where the Timeline will be attached
  var container = document.getElementById('visualization');

  function ssaDate(timestampStr) {
      return new Date(new Date(timestampStr).getTime() + (new Date().getTimezoneOffset() * 60 * 1000));
  };

  function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
  }
  
  function createTimeline(ListOfdata)
  {   
    //console.log(ListOfdata); 
    ListOfdata.splice(-1,1);
   
    var items = new vis.DataSet();

    $.each(ListOfdata, function( index, element ) {
     
      var id= element[1] + "_" +index;
      var ldata= {id: id, content: element[1], start: ssaDate(element[0])};
      items.add(ldata);

    });

    

    //var debut = ($("#in_startDate").val()).match(/\d+/g);;
 
    var startDate = new Date($("#in_startDate").val());
    var endDate = new Date($("#in_endDate").val());

  // Configuration for the Timeline
    var options = { min: startDate,
                  max: addDays(endDate, 1),
                  //height: '200px',
                  locale: 'fr',
                  zoomMin: 1000 * 60 * 30,
                  zoomMax: 1000 * 60 * 60 * 24 * 10 
                 };

  // Create a Timeline
    var timeline = new vis.Timeline(container, items, options);
  }

  $(".in_datepicker").datepicker();
  $('#ui-datepicker-div').hide();
    
  $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "core/ajax/cmd.ajax.php", // url du fichier php
        data: {
            action: "getHistory",
            id: <?php echo $Cmd; ?>,
            dateRange: 'all',
            dateStart: $('#in_startDate').value(),
            dateEnd: $('#in_endDate').value(),
            
            derive:  '',
            allowZero: 1
        },
        dataType: 'json',
        global:  true,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            createTimeline(data.result.data);
            
        }
      }); 


  $('#bt_validChangeDate').on('click',function(){
                    var modal = false;
                    if($('#md_modal').is(':visible')){
                        modal = $('#md_modal');
                    }else if($('#md_modal2').is(':visible')){
                        modal = $('#md_modal2');
                    }
                    if(modal !== false){
                        modal.dialog({title: "{{Historique}}"});
                        modal.load('index.php?v=d&plugin=ssaTask&modal=modal.ssaTask&ssa=<?PHP echo $Cmd;?>&startDate='+$('#in_startDate').val()+'&endDate='+$('#in_endDate').val()).dialog('open');
                    }
                });
</script>



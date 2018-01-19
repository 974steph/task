
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

 


$("#ssaTask_task").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

$("#ssaTask_task").delegate('.bt_removeTask', 'click', function () {

    $(this).closest('.ssaTask').remove();
});


$("#ssaTask_task").delegate('.ssaProgrameSwitch', 'click', function () {
    id= $( this ).attr('data-id');
    type=$( this ).attr('data-type');
    checked= $( this ).is(':checked')
    if (checked==true)
    {   $("#ssaTaskDatePicker_"+id).prop('disabled', true);
        if (type=='sunrise')
        {  $("#sunset_"+id).prop('checked', false);
           $("#ssaTaskDatePicker_"+id).val(  $('#ssaTask_task').attr('data-sunrise') );

        }
        if (type=='sunset')
        {  $("#sunrise_"+id).prop('checked', false);
           $("#ssaTaskDatePicker_"+id).val($('#ssaTask_task').attr('data-sunset') ); 

        }

    }
    else
    {  $("#ssaTaskDatePicker_"+id).prop('disabled', false);

    }

    //console.log(id,type,checked);
     
});

$("#ssaTask_task").delegate(".listEquipementAction", 'click', function() {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function(result) {
        var calcul = el.closest('div').find('input');
        calcul.val(result.human);
        
    });
});

 function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
   
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="subType" style="display : none;">';
    tr += '<input  readonly class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}"></td>';
    tr += '</td>';
    
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '</td>';
    tr += '</tr>';
    
    $('#ssaTask_cmd tbody').append(tr);
    $('#ssaTask_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#ssaTask_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#ssaTask_cmd tbody tr:last'), init(_cmd.subType));
}





function addTask(_task)
{
   
    var random = Math.floor((Math.random() * 1000000) + 1);
    var tr = '<tr class="ssaTask">';
    
    tr += '<td>';
    tr += '<input  name="name" class="form-control" placeholder="{{Nom plage}}" value="'+_task.name +'">';
    tr += '</td>';
    
    tr += '<td>';



    tr += '<div  class="input-group">';
    tr += '     <input id="ssaTaskDatePicker_'+random+'" type="text" name="heure"  class="form-control" placeholder="{{heure Début}}" value="'+_task.heure +'">';
   
   
    tr += '</div>';

        tr += " <div class='ssa-switch'>";
        tr += "sunrise";
        tr += "     <input class='ssaProgrameSwitch' id='sunrise_"+ random +"' name='sunrise' type='checkbox' data-type='sunrise' data-id='"+random+"'/>";
        tr += "     <label for='sunrise_"+ random +"' class='label-success'></label>";
        tr += " </div>";

        tr += " <div class='ssa-switch'>";
        tr += "sunset";
        tr += "     <input class='ssaProgrameSwitch' id='sunset_"+ random +"' name='sunset' type='checkbox' data-type='sunset' data-id='"+random+"'/>";
        tr += "     <label for='sunset_"+ random +"' class='label-success'></label>";
        tr += " </div>";

    tr += '</td>';
    
   

    
    tr += '<td>';

    tr += '    <div class = "input-group">';
   
    tr += '     <input  name="cmd" class="form-control" placeholder="{{commande}}" value="'+_task.cmd +'">';
    tr += '     <span   class = "input-group-addon listEquipementAction"><i class="fa fa-cog"></i></span>';
    tr += '    </div>';

    
    tr += '</td>';
    
  
    tr += '<td>';
    var day=['l','ma','me','j','v','s','d','f'];
    var lib={'l':'L','ma':'Ma','me':'Me','j':'J','v':'V','s':'S','d':'D','f':'JF'};
    
   
    $.each(day, function( index, value ) {
        tr += " <div class='ssa-switch'>";
        tr += lib[value];
        tr += "     <input class='ssaDaySwitch' id='"+value+"_"+ random +"' name='"+value+"' type='checkbox'/>";
        tr += "     <label for='"+value+"_"+ random +"' class='label-success'></label>";
        tr += " </div>";
        
    });
    tr += '</td>';
    tr += '<td>';
    tr += '<a class=" btn btn-sm bt_removeTask btn-primary"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>';
    
    tr += '</td>';
    $('#ssaTask_task tbody').append(tr);
    
   

    $('#sunset_'+random).prop('checked', _task.sunset);
    $('#sunrise_'+random).prop('checked', _task.sunrise);
    if (_task.sunset ||  _task.sunrise)
    {   $("#ssaTaskDatePicker_"+random).prop('disabled', true);


    }

    _task.calendrier.forEach(function(element){
        var who='#'+element+'_'+random;
        $(who).prop('checked', true);
        
    });

    $('#ssaTaskDatePicker_'+random).datetimepicker({
        datepicker:false,
        format:'H:i',
        step:5,

    });
    
}


$('#bt_addSsaTaskCMD').on('click', function () {
    var task =new Object();
    task.name='' ;
    task.heure='' ;
    task.prog='';
    task.sunrise='';
    task.sunset='';  
    task.cmd='' ;  
    task.calendrier=[];
    addTask(task);
   
});



function printEqLogic(_eqLogic) {
    $('#ssaTask_task tbody').empty();
    if (isset(_eqLogic.configuration))
    {   
        setSunDefault(_eqLogic.configuration.sunset,_eqLogic.configuration.sunrise);
        //console.log($('#ssaTask_task'));
        if (isset(_eqLogic.configuration.task)) {
            for (var i in _eqLogic.configuration.task) {
                addTask(_eqLogic.configuration.task[i]) ;
                
            }
        }
      
    }
    
}

function setSunDefault(sunset,sunrise)
{   if (isset(sunset))
        $('#ssaTask_task').attr('data-sunset', sunset);
    else
        $('#ssaTask_task').attr('data-sunset', '22:00');
    if (isset(sunrise))
        $('#ssaTask_task').attr('data-sunrise', sunrise);
    else
        $('#ssaTask_task').attr('data-sunrise', '6:00');

}


function saveEqLogic(_eqLogic) {
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
    var data = getTask('#ssaTask_task');
    _eqLogic.configuration.task = data;
    return _eqLogic;
}


function getTask(table)
{ 
    
   var otArr = [];
   var tbl2 = $(table +" tbody  tr").each(function(i) {        
        var task =new Object();
        
        task.name=$(this).find("input[name=name]").val() ;
        task.heure=$(this).find("input[name=heure]").val() ;  
        task.cmd=$(this).find("input[name=cmd]").val() ;  
        task.sunrise=$(this).find("input[name=sunrise]").is(':checked') ;  
        task.sunset=$(this).find("input[name=sunset]").is(':checked') ;  
        if (task.sunset ||  task.sunrise)
        {   task.prog= true;

        }
        else
        {   task.prog= false;

        }    
    
        var checked = [];
        $(this).find('.ssaDaySwitch').each(function ()
        {   
            
            if ($(this).is(':checked'))
             checked.push($(this).attr('name'));
         
        });
        
        task.calendrier=checked;
        //console.log(task);
        otArr.push(task);
   })
 
   return otArr;
    
    
    
}



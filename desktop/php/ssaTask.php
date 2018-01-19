<?php
//https://xdsoft.net/jqplugins/datetimepicker/
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'ssaTask');
$eqLogics = eqLogic::byType('ssaTask');
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName() . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend>{{Mes organisateurs}}</legend>
        <?php
        if (count($eqLogics) == 0) {
            echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez pas encore d'organisateurs, cliquez sur Ajouter un Organisateur pour commencer}}</span></center>";
        } else {
            ?>
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/ssaTask/doc/images/ssaTask_icon.png" height="105" width="95" />';
                    echo "</center>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php } ?>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <a class="btn btn-default eqLogicAction pull-right" data-action="copy"><i class="fa fa-files-o"></i> {{Dupliquer}}</a>
        <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation">
                <a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
                    <i class="fa fa-arrow-circle-left"></i>
                </a>
            </li>
            <li role="presentation" class="active">
                <a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
                    <i class="fa fa-tachometer"></i> {{Configuration}}
                </a>
            </li>
            <li role="presentation">
                <a href="#ssaTaskTaskTab"  aria-controls="profile" role="tab" data-toggle="tab">
                    <i class="fa fa-list-alt"></i> {{Tâches}}
                </a>
            </li>
            <li role="presentation">
                <a href="#ssaTaskCommandTab" aria-controls="profile" role="tab" data-toggle="tab">
                    <i class="fa fa-list-alt"></i> {{Commandes}}
                </a>
            </li>
        </ul>
        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <form class="form-horizontal">
                    <fieldset>
                        <legend> {{Configuration de l'organisateur}}  </legend>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">{{Nom de l'organisateur}}</label>
                            <div class="col-lg-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label" >{{Objet parent}}</label>
                            <div class="col-lg-3">
                                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach (object::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Catégorie}}</label>
                            <div class="col-sm-8">
                                <?php
                                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                        echo '<label class="checkbox-inline">';
                                        echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                        echo '</label>';
                                    }
                                ?>
                           </div>
                        </div>
                        <div class="form-group">
        					<label class="col-lg-3 control-label" ></label>
        					<div class="col-sm-9">
        						<label class="checkbox-inline"> {{Activer}} <input type="checkbox" class="eqLogicAttr " data-label-text="{{Activer}}" data-l1key="isEnable" checked/></label>
        						<label class="checkbox-inline"> {{Visible}} <input type="checkbox" class="eqLogicAttr " data-label-text="{{Visible}}" data-l1key="isVisible" checked/></label>
        					</div>
                        </div>
        		    </fieldset>

                    

                    <fieldset>
                        <legend>{{Apparence}}</legend>  
                         <div class="form-group">
                            <label class="col-lg-3 control-label" >Affichage</label>
                            <div class="col-sm-8" >
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr"  data-l1key="configuration" data-l2key="affichage" data-l3key="date"/>{{affichage date}}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label" ></label>
                            <div class="col-sm-8" >
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr"  data-l1key="configuration" data-l2key="affichage" data-l3key="history"/>{{historique commande}}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">{{couleur}}</label>
                            <div class="col-lg-3">
                               
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="affichage" data-l3key="color" placeholder="{{Couleur}}"/>
                            </div>
                        </div>
                    </fieldset>



                </form>
            </div>


            <div role="tabpanel" class="tab-pane" id="ssaTaskTaskTab">

                <a class="btn btn-default btn-warning pull-right" id="bt_addSsaTaskCMD" style="margin:5px;">
                    <i class="fa fa-plus-circle"></i> {{Créer une tache}}
                </a>
                <legend><i class="fa fa-list-alt"></i> {{Tableau des tâches}}</legend>
                <table id="ssaTask_task" class="table table-bordered table-condensed" data-sunrise='' data-sunset=''>
                    <thead>
                        <tr>
                            <th class="col-sm-1">{{Nom}}</th>
                            <th class="col-sm-1">{{heure}}</th>
                            
                            <th class="col-sm-2">{{Commande}}</th>
                            <th class="col-sm-2">{{Jour}}</th>
                            <th class="col-sm-1">{{Supprimer}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
        	</div>

            <div role="tabpanel" class="tab-pane" id="ssaTaskCommandTab">
                <legend><i class="fa fa-list-alt"></i> {{Commandes}}</legend>
                <table id="ssaTask_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th class="col-sm-1">{{Nom}}</th>
                            <th class="col-sm-3">{{configuration}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <form class="form-horizontal">
                <fieldset>
                    <div class="form-actions">
                        <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                        <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<?php include_file('desktop', 'ssaTask', 'css', 'ssaTask');?>
<?php include_file('desktop', 'ssaTask', 'js', 'ssaTask'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
<?php include_file('3rdparty', 'visjs/vis.min', 'css'); ?>
<?php include_file('3rdparty', 'visjs/vis.min', 'js'); ?>

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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>

<form class="form-horizontal">
    <fieldset>
 <legend><i class="icon loisir-darth"></i> {{Position}}</legend>
<div class="form-group">
    <label class="col-lg-2 control-label">Longitude</label>
    <div class="col-lg-2">
       
        <input class="configKey form-control" data-l1key="latitude" />
    </div>
   

</div>

<div class="form-group">
    
    <label class="col-lg-2 control-label">Latitude</label>
    <div class="col-lg-2">
        <input class="configKey form-control" data-l1key="longitude" />
        
    </div>
   
</div>

<div class="form-group">
    
    <label class="col-lg-6 control-label">{{Coordonnées de votre adresse (https://www.coordonnees-gps.fr)}}</label>
    
   
</div>
</fieldset>
</form>


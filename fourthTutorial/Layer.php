<?php
// Copyright (c) 2011, Layar B.V.
// All rights reserved.

// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//    * Redistributions of source code must retain the above copyright
//      notice, this list of conditions and the following disclaimer.
//    * Redistributions in binary form must reproduce the above copyright
//      notice, this list of conditions and the following disclaimer in the
//      documentation and/or other materials provided with the distribution.
//    * Neither the name of the <organization> nor the
//      names of its contributors may be used to endorse or promote products
//      derived from this software without specific prior written permission.

// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
// ARE DISCLAIMED. IN NO EVENT SHALL LAYAR B.V BE LIABLE FOR ANY DIRECT,
// INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
// LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
// ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
// SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

include_once('Action.php');

// Define child class Layer. 
class Layer extends Parameter {
// Define the default values of optional parameters in Layer object.    
static $defaults = array (
  "refreshInterval" => 300,
  "fullRefresh" => TRUE,
  "showMessage" => NULL, 
  "deletedHotspots" => array(),
  "actions" => array()
  );
}

// Put fetched actions for this layer into an associative array.
//
// Arguments:
//   db ; The database connection handler. 
//   layerName, string ; The layer name.
//
// Returns:
//   array ; An associative array of received actions for this layer.
//   Otherwise, return an empty array. 
// 
function getLayerActions($db , $layerName) {
  // Define an empty $actionArray array.
  $actionArray = array();
  
  // A new table called 'LayerAction' is created to store actions, each action
  // has a field called 'layerName' which indicates the layer that this action
  // belongs to.  The SQL statement returns actions which have the same
  // layerName as $layerName. 
  $sql_actions = $db->prepare(' 
      SELECT label, 
             uri, 
             contentType,
             method,
             activityType,
             params,
             showActivity,
             activityMessage
        FROM LayerAction , Layer
       WHERE layerID = Layer.id  
         AND Layer.layer = :layerName '); 

  // Binds the named parameter markers ':layerName' to the specified parameter
  // value '$layerName'.                 
  $sql_actions->bindParam(':layerName', $layerName, PDO::PARAM_STR);
  // Use PDO::execute() to execute the prepared statement $sql_actions. 
  $sql_actions->execute();
  // Iterator for the $actionArray array.
  $count = 0; 
  // Fetch all the layer actions. 
  $actions = $sql_actions->fetchAll(PDO::FETCH_ASSOC);

  /* Process the $actions result */
  // if $actions array is not empty. 
  if ($actions) {
    
    $actionObject = new Action();
    // Put each action information into $actionArray array.
    foreach ($actions as $action) {
      $actionObject->add('label' , $action['label']);
      $actionObject->add('uri' , $action['uri']);
      $actionObject->add('contentType' , $action['contentType']);
      $actionObject->add('method' , $action['method']);
      // put 'params' into an array of strings
      $actionObject->add('params' , changetoArray($action['params'] , ','));
      // Change 'activityType' to Integer.
      $actionObject->add('activityType' ,  changetoInt($action['activityType'])); 
      // Change the values of 'showActivity' into boolean value.
      $actionObject->add('showActivity' , changetoBool($action['showActivity']));
      $actionObject->add('activityMessage', $action['activityMessage']);
      // Assign each action to $actionArray array. 
      $actionArray[$count] = $actionObject->getFiltered();
  
      $count++; 
    }// foreach
  }//if
  return $actionArray;
}//getLayerActions
// Put retrieved layer level parameters into an associative array. 
// 
// Arguments:
//   db ; The database handler. 
//   layerName, string ; The name of the layer in the getPOI request.
// Return: 
//   array ; An associative array which contains parameters defined on layer
//   level.
function getLayerDetails($db, $layerName){
  // Define an empty $layer array.  
  $layer = array();
  // A new table called 'Layer' is created to store general layer level
  // parameters. 
  // 'layer' is the name of this layer. 
  // The SQL statement returns layer which has the same name as the
  // $layerName passed in getPOI request. 
  $sql = $db->prepare( '
            SELECT layer, 
                   refreshInterval,  
                   fullRefresh,
                   showMessage     
            FROM Layer
            WHERE layer = :layerName ');
 
  // Binds the named parameter marker ':layerName' to the specified parameter
  // value $layerName                
  $sql->bindParam(':layerName', $layerName, PDO::PARAM_STR);
  // Use PDO::execute() to execute the prepared statement $sql. 
  $sql->execute();
  // Retrieve layer parameters
  $layerValue = $sql->fetch(PDO::FETCH_ASSOC);
  // If $layerName is not found in the database, throw an exception. 
  try{
    if(!$layerValue)
      throw new Exception('layer:' . $layerName . 'is not found in the database.');
    else {
      $layerDetails = new Layer();
      $layerDetails->add('layer' , $layerValue['layer']);
      $layerDetails->add('refreshInterval', changetoInt($layerValue['refreshInterval']));
      $layerDetails->add('fullRefresh', changetoBool($layerValue['fullRefresh']));
      $layerDetails->add('showMessage', $layerValue['showMessage']);
      // Get Layer level actions
      $layerDetails->add('actions' , getLayerActions($db , $layerName));
      // Filter out optional default values
      $layer = $layerDetails-> getFiltered();
    }
  return $layer;
  } 
  catch(Exception $e){
    echo 'Message: ' . $e->getMessage();
  }
}//getlayerDetails

?>

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


// Include Action.php
include_once 'Action.php';

// Define child class POI. 
class POI extends Parameter {
  // Define the default values of optional parameters in POI object. 
  static $defaults = array (
    "transform" => array(
      "rotate" => array (
        "rel" => FALSE,
        "angle" => 0.0, 
        "axis" => array ("x" => 0.0 , "y" => 0.0, "z" => 1.0)
      ),
      "translate" => array ("x" => 0.0, "y" => 0.0, "z" => 0.0),
      "scale" => 1.0
    ),
    "actions" => array()
  );  
}//POI

// Construct anchor object based on poiType of each POI. 
// Arguments: 
//   rawPoi, array ; An associative array which contains a POI object. 
// 
// Returns:
//  array ; An array which contains anchor dictionary information for a Vision
//  enabled POI. 
function getAnchor($rawPoi) {
  $anchor = array();
  
  $anchor['referenceImage'] = $rawPoi['referenceImage'];

  return $anchor;
}//getAnchor


// Put fetched actions for each POI into an associative array.
//
// Arguments:
//   db ; The database connection handler. 
//   poi ; The POI array.
//
// Returns:
//   array ; An associative array of received actions for this POI.Otherwise,
//   return an empty array. 
// 
function getPoiActions($db , $poi) {
  // Define an empty $actionArray array. 
  $actionArray = array();

  // A new table called 'POIAction' is created to store actions, each action
  // has a field called 'poiID' which shows the POI id that this action belongs
  // to. 
  // The SQL statement returns actions which have the same poiID as the id of
  // the POI($poiID).
  $sql_actions = $db->prepare(' 
      SELECT label, 
             uri, 
             autoTriggerOnly,
             contentType,
             method,
             activityType,
             params,
             showActivity,
             activityMessage,
             autoTrigger
      FROM POIAction
      WHERE poiID = :id '); 

  // Binds the named parameter marker ':id' to the specified parameter value
  // '$poiID.                 
  $sql_actions->bindParam(':id', $poi['id'], PDO::PARAM_STR);
  // Use PDO::execute() to execute the prepared statement $sql_actions. 
  $sql_actions->execute();
  // Iterator for the $actionArray array.
  $count = 0; 
  // Fetch all the poi actions. 
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
      $actionObject->add('autoTrigger' ,  changetoBool($action['autoTrigger']));
      // Change the values of 'autoTriggerOnly' into boolean value.
      $actionObject->add('autoTriggerOnly' ,  changetoBool($action['autoTriggerOnly'])); 
      // Assign each action to $actionArray array. 
      $actionArray[$count] = $actionObject->getFiltered();
      // filter each action array to remove default values of optional
      // parameters    
    $count++; 
    }// foreach
  }//if
  return $actionArray;
}//getPoiActions

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
// Put fetched object parameters for each POI into an associative array.
//
// Arguments:
//   db ; The database connection handler. 
//   objectID, integer ; The object id assigned to this POI.
//
// Returns:
//   associative array ; An array of received object related parameters for
//   this POI. otherwise, return an empty array. 
// 
function getObject($db , $objectID) {
  // Define an empty $object array. 
  $object = array();

  // A new table called 'Object' is created to store object related parameters,
  // namely 'url', 'contentType', 'reducedURL' and 'size'. The SQL statement
  // returns object which has the same id as $objectID stored in this POI. 
  $sql_object = $db->prepare(
    ' SELECT contentType, url, size 
      FROM Object
      WHERE id = :objectID 
      LIMIT 0,1 '); 

  // Binds the named parameter marker ':objectID' to the specified parameter
  // value $objectID.                 
  $sql_object->bindParam(':objectID', $objectID, PDO::PARAM_INT);
  // Use PDO::execute() to execute the prepared statement $sql_object. 
  $sql_object->execute();
  // Fetch the poi object. 
  $rawObject = $sql_object->fetch(PDO::FETCH_ASSOC);

  /* Process the $rawObject result */
  // if $rawObject array is not empty. 
  if ($rawObject) {
    // Change 'size' type to float. 
    $rawObject['size'] = changetoFloat($rawObject['size']);
    $object = $rawObject;
  }
  return $object;
}//getObject


// Put fetched transform related parameters for each POI into an associative
// array. The returned values are assigned to $poi[transform].
//
// Arguments:
//   db ; The database connection handler. 
//   transformID , integer ; The transform id which is assigned to this POI.
//
// Returns: associative array ; An array of received transform related
// parameters for this POI. Otherwise, return an empty array. 
// 
function getTransform($db , $transformID) {
  // Define an empty $transform array. 
  $transform = array();
  // A new table called 'Transform' is created to store transform related
  // parameters, namely 'rotate','translate' and 'scale'. 
  // 'transformID' is the transform that is applied to this POI. 
  // The SQL statement returns transform which has the same id as the
  // $transformID of this POI. 
  $sql_transform = $db->prepare('
      SELECT rel, 
             angle, 
             rotate_x,
             rotate_y,
             rotate_z,
             translate_x,
             translate_y,
             translate_z,
             scale
      FROM Transform
      WHERE id = :transformID 
      LIMIT 0,1 '); 

  // Binds the named parameter marker ':transformID' to the specified parameter
  // value $transformID                
  $sql_transform->bindParam(':transformID', $transformID, PDO::PARAM_INT);
  // Use PDO::execute() to execute the prepared statement $sql_transform. 
  $sql_transform->execute();
  // Fetch the poi transform. 
  $rawTransform = $sql_transform->fetch(PDO::FETCH_ASSOC);

  /* Process the $rawTransform result */
  // if $rawTransform array is not  empty 
  if ($rawTransform) {
    // Change the value of 'scale' into decimal value.
    $transform['scale'] = changetoFloat($rawTransform['scale']);
    // organize translate field
    $transform['translate']['x'] =changetoFloat($rawTransform['translate_x']);
    $transform['translate']['y'] = changetoFloat($rawTransform['translate_y']);
    $transform['translate']['z'] = changetoFloat($rawTransform['translate_z']);
    // organize rotate field
    $transform['rotate']['axis']['x'] = changetoFloat($rawTransform['rotate_x']);
    $transform['rotate']['axis']['y'] = changetoFloat($rawTransform['rotate_y']);
    $transform['rotate']['axis']['z'] = changetoFloat($rawTransform['rotate_z']);
    $transform['rotate']['angle'] = changetoFloat($rawTransform['angle']);
    $transform['rotate']['rel'] = changetoBool($rawTransform['rel']);
  }//if 
    
  return $transform;
}//getTransform

// Put received POIs into an associative array. The returned values are
// assigned to $reponse['hotspots'].
//
// Arguments:
//   db ; The handler of the database.
//   value , array ; An array which contains all the needed parameters
//   retrieved from GetPOI request. 
//
// Returns:
//   array ; An array of received POIs.
//
function getHotspots( $db, $value ) {
  // Define an empty $hotspots array.
  $hotspots = array();
/* Create the SQL query to retrieve vision POIs
   The first 50 returned POIs are selected.
*/
  
  // Use PDO::prepare() to prepare SQL statement. This statement is used due to
  // security reasons and will help prevent general SQL injection attacks.
  // ':layerName' is named parameter marker for
  // which real values will be substituted when the statement is executed.
  // $sql is returned as a PDO statement object. 
  $sql = $db->prepare('
    SELECT VisionPoi.id,       
           referenceImage,
           objectID,
           transformID
      FROM VisionPoi, Layer WHERE  
           VisionPoi.layerID = Layer.id AND
           Layer.layer = :layerName 
     LIMIT 0, 50
  ');

  // PDOStatement::bindParam() binds the named parameter markers to the
  // specified parameter values. 
  $sql->bindParam(':layerName', $value['layerName'], PDO::PARAM_STR);
  // Use PDO::execute() to execute the prepared statement $sql. 
  $sql->execute();
  // Iterator for the response array.
  $i = 0; 
  // Use fetchAll to return an array containing all of the remaining rows in
  // the result set.
  // Use PDO::FETCH_ASSOC to fetch $sql query results and return each row as an
  // array indexed by column name.
  $rawPois = $sql->fetchAll(PDO::FETCH_ASSOC);
 
  /* Process the $pois result */
  // if $rawPois array is not empty 
  if ($rawPois) {
    // Put each POI information into $hotspots array.
    foreach ( $rawPois as $rawPoi ) {
      $myPoiParameters = new POI(); 
      // Get anchor object information
      $myPoiParameters->add('anchor', getAnchor($rawPoi));
      // Get POI action array
      $myPoiParameters->add('actions' , getPoiActions($db , $rawPoi));
      // Get object object information if objectID is not null
      if(count($rawPoi['objectID']) != 0) 
        $myPoiParameters->add('object', getObject($db, $rawPoi['objectID']));
      // Get transform object information if transformID is not null
      if(count($rawPoi['transformID']) != 0)
        $myPoiParameters->add('transform', 
          getTransform($db, $rawPoi['transformID']));
      // Put the filtered poi parameters into the $hotspots array.
      $hotspots[$i] = $myPoiParameters->getFiltered();
      $i++;
    }//foreach
  }//if
  return $hotspots;
}//getHotspots

?>

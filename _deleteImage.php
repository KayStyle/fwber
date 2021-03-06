<?php
/*
    Copyright 2020 FWBer.com

    This file is part of FWBer.

    FWBer is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    FWBer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero Public License for more details.

    You should have received a copy of the GNU Affero Public License
    along with FWBer.  If not, see <https://www.gnu.org/licenses/>.
*/
	session_start();

	require_once("_init.php");
	require_once("_profileVars.php");
	require_once("_secrets.php");
	require_once("_globals.php");
	
	//first make sure we are a legit user.
	if(validateSessionOrCookiesReturnLoggedIn()==false){header('Location: '.getSiteURL());return;}//full auth for actions
	
	goHomeIfCookieNotSet();
	
	//make sure we've got an action.
	if(!isset($_GET['img'])||empty($_GET['img']))exit('no img');

	$db = mysqli_connect($dburl,$dbuser,$dbpass);
	if(!$db)exit(mysqli_connect_error());
	
	$email = mysqli_escape_string($db,$_SESSION["email"]);
	$img = mysqli_escape_string($db,$_GET['img']);

	//get my userid
	$dbquerystring = sprintf("SELECT id, publicPics, privatePics FROM ".$dbname.".users WHERE email='%s'",$email);
	$dbquery = mysqli_query($db,$dbquerystring);
	$dbresults = mysqli_fetch_array($dbquery);
	
	$userid=$dbresults['id'];
	
	$publicPics=explode(",",trim(trim($dbresults['publicPics']),","));
	$privatePics=explode(",",trim(trim($dbresults['privatePics']),","));
	
	mysqli_free_result($dbquery);
	
	//figure out which database list it's in.
	$foundWhere="";
	foreach($publicPics as $s)if($s==$img)$foundWhere="public";
	
	if($foundWhere=="")foreach($privatePics as $s)if($s==$img)$foundWhere="private";
	
	if($foundWhere=="")exit("Image not found.");

	//remove image filename in type database.
	if($foundWhere=="public")rem_array($publicPics,$img);
	if($foundWhere=="private")rem_array($privatePics,$img);
	
	$dbquerystring = 
	sprintf("UPDATE ".$dbname.".users SET publicPics = '%s',privatePics = '%s' WHERE id='%s'",
	trim(trim(implode(",",$publicPics)),","),
	trim(trim(implode(",",$privatePics)),","),
	$userid
	);
	if(!mysqli_query($db,$dbquerystring))exit("didn't work");
	
	//done
	mysqli_close($db);

	//send back done
	echo "done";


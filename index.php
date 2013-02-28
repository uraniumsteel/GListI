<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>Intelligent ToDo by Gould</title>
        <link rel="stylesheet" type="text/css" href="includes/css/redmond/jquery-ui-1.9.2.custom.min.css"/>
        <link rel="stylesheet" type="text/css" href="includes/css/main.css"/>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"></script>
        <script type="text/javascript" src="includes/js/jquery.mjs.nestedSortable.js"></script>
        <script type="text/javascript" src="includes/js/main.js"></script>
    </head>
    <body>
        <div id="header">
            <div id="sect-left">
                <h1>IDo = Intelligent to Do</h1>
            </div>
            <div id="sect-right" >
                <form name="logout" action="index.php" method="post">
                <a href="" onclick="javascript: document.logout.submit();">logout</a>
                <input type="hidden" name="logout" value="logout"/>
                </form>
            </div>
        </div>
<?php
session_start();
if (isset($_SESSION['yourname'])) { $loggedin = true; }
else { $loggedin = false; }

/* check login status */
if (isset($_POST['logout'])) {
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
}
if (isset($_POST['yourname']) && isset($_POST["password"])) {
    if ((strtolower(trim($_POST["password"])) == "admin") && (strtolower(trim($_POST['yourname'])) == "admin")) {
        $_SESSION['yourname'] = trim($_POST['yourname']);
        $loggedin = true;
    }
}
if (!$loggedin) {  // If Not Logged in:
    $_SESSION['listinuse'] = 'default';
?>
         <form name="login" action="index.php" method="post">
                <fieldset><legend>Intelligent to Do Login</legend>
         <label for="username">Name:</label><input type='text' value='' name='yourname'id="username"/><br/>
                <label for="password">Password:</label><input type='password' val='' name='password' id="password"/><br/>
                <input type='submit' value='Enter' name="OK"/><br/>
                </fieldset>
<?php
} else {
 ?>

        <div id="main">
            <div id="lists">
            <div id="listview" class="ui-widget ui-corner-all">
                <div class="ui-widget ui-widget-header">List <span id="showlistname">Default</span> <div id="maintoolbar" class="ui-state-default ui-corner-all"><span class="ui-button ui-button-icon-only ui-icon ui-corner-all ui-icon-folder-open" ></span></div></div>
                <div id="listtoolbar" class="toolbar ui-state-default ui-corner-all"><span class="floatl ui-icon ui-icon-plusthick" onclick="ShowAdd();"></span><div id="newitem" style="display: none;"><input id="newiteminput" type="text" value=""/></div>
                    <span id="showsortable" class="floatr ui-icon ui-icon-circle-minus" onclick="ShowSortable();"> </span>
                </div>
                <ul id="listdata" class="connectedSortable">
                    <li class=""><input class="checkbox" type="checkbox" />Test Item 1<div class="notes">My Test Note 1</div></li>
                    <li><input class="checkbox" type="checkbox" />Test Item 2</li>
                </ul>
            </div>
            <div id="NotesNDue" class="ui-dialog ui-widget ui-widget-content   popup">
                <div class="control" onclick="UpdateNotesNDue();">&lt</div>
                <label for="notes">Notes:</label><input id="notes" name="notes" type="text" value="" multiline="true" /><br/>
                <label for="due">Due:</label><input type="text" value="" id="due" name="due" /><br/>
                <button type="button" onclick="MorC('move');">Move</button> or <button type="button" onclick="MorC('copy');">Copy</button>
                <br/>to list:   <select id="MorCList" name="MorCList" value=""></select>
            </div>
            </div>
            <div id="journal" class="ui-widget">
                <div class="ui-widget-header" style="padding-left: 5px;">Journal</div>
                <textarea id='edit1' style='width: 99%; height: 80%; min-height: 450px;'></textarea>
                <span id='lcount'>0</span>
                <button type="button" onclick="javascript:SaveJournal();" class="ui-icon ui-icon-disk ui-state-default"></button> <span id="journalsaved"></span>
                <button type="button" onclick="javascript:$('#loadjournal').show();">Load Journal</div><div id="loadjournal" class="popup"><input type="text" id="datepicker" /><button type="button" onclick="javascript:LoadJournal();">Load</button></div>
            </div>
             <div id="openlist" class="popup ui-widget ui-dialog ui-state-default">
                 <div class="ui-widget-header">Select List</div>
                 <ul>
                     
                 </ul>
                 <div class="separator1">
                 <button type="button" value="newlist" onclick="javascript:NewList();">Create New List</button>
                 <input type="text" value="" name="newlist" id="newlist"/>
                 </div>
                 <div class="separator1">
                     <button type="button" value="CloneList" onclick="javascript:CloneList();">Clone active list</button>
                     <br/>
                     to:<input type="text" value="" name="clonelist" id="clonelist"/>
                     <br/>
                     <input type="checkbox" value="" id="RemoveCompleted" name="RemoveCompleted" checked="checked"/> Remove Completed Items
                 </div>
                 <button type="button" value="Close" class="cancelbut" onclick="javascript:$('#openlist').hide();">Close</button>
             </div>
<?php
}
?>
             <div id="debug"></div>
    </body>
</html>
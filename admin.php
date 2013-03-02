<?php
include("/includes/session.php");
if (!$session->logged_in) {   //If user is not logged in
    echo "NOT LOGGED IN";
    header("Location: index.php");
}

/**
 * displayBannedUsers - Displays the banned users
 * database table in a nicely formatted html table.
 */
function displayBannedUsers() {
    global $database;
    $q = "SELECT username,timestamp "
            . "FROM " . TBL_BANNED_USERS . " ORDER BY username";
    $result = $database->query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);
    if (!$result || ($num_rows < 0)) {
        echo "Error displaying info";
        return;
    }
    if ($num_rows == 0) {
        echo "Database table empty";
        return;
    }
    /* Display table contents */
    echo "<table align=\"left\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
    echo "<tr><td><b>Username</b></td><td><b>Time Banned</b></td></tr>\n";
    for ($i = 0; $i < $num_rows; $i++) {
        $uname = mysql_result($result, $i, "username");
        $time = mysql_result($result, $i, "timestamp");

        echo "<tr><td>$uname</td><td>$time</td></tr>\n";
    }
    echo "</table><br>\n";
}

/**
 * User not an administrator, redirect to main page
 * automatically.
 */
if (!$session->isAdmin()) {
    echo "YOU ARE NOT ADMIN";
    //header("Location: ../index.php");
} else {
    /**
     * Administrator is viewing page, so display all
     * forms.
     */
    ?>
    <h1>-- Administration User Maintenance --</h1>
    <div class="clearfix">
        <ul style=" float: left; margin: 0; padding: 0;">
            <li class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-plusthick" id="al-tbbut"> </span></li>
        </ul>
    </div>
    <?php
    if ($form->num_errors > 0) {
        echo "<font size=\"4\" color=\"#ff0000\">"
        . "!*** Error with request, please fix</font><br><br>";
    }
    ?>

    <h3>Users:</h3>
    <table id="UserList" class="list1">
        <thead>
            <tr><th></th><th>Username</th><th>Real Name</th><th>Level</th><th>Email</th><th>Last Active</th><th></th></tr>
        </thead>
        <tbody>

        </tbody>
    </table>


    <?php
}
?>
<!--======  EDIT USER POPUP ===== -->
<div id="EditUser"  class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-dialog-buttons "  >
    <div class='ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix' >Add/Edit User</div>
    <label for="username">Username:</label> <input id="username" name="username" type="text" value="" size="24"/><br/>
    <label for="password">Password:</label> <input id="password" name="password" type="password" value="" size="12"/><br/>
    <label for="realname">Real Name:</label> <input id="realname" name="realname" type="text" value="" size="34"/><br/>
    <label for="userlevel">Security Level:</label> <select id="userlevel" name="userlevel">
        <option value="1">1 User</option>
        <option value="9">9 Admin</option>
    </select><br/>
    <label for="email">Email:</label> <input id="email" name="email" type="text" value="" size="48"/><br/>      
    <input type="hidden" id="loteditrequest" name="request" value="addlot" />
    <button id="usernewdatabut" type="button" name="createuser" value="Create This User" onclick="adduser();">Add This User</button>
    <button type="button" id="cancelcreate" name="cancelcreate" value="Cancel Creation" class="cancelbutton" onclick="CloseUserWin();">Cancel</button>
</div>



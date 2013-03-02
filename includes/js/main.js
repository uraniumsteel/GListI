
var NoteParent = null;
var Editing = false;
var TrashLine = '<li id="trash"><div class="sublist"><span class="ui-icon ui-icon-trash"></span><span class="disclose"><span></span></span></div></li>';
//--- end of list type --

var lcount = 0;
//================== DOCUMENT LOAD/READY =======================================
$(document).ready(function(){
    //GetLists();
    $('.toolbar .ui-icon').hover(function(){
        $(this).toggleClass('ui-state-hover');
    });

   $('#listdata').nestedSortable({
       handle: ".handle",
       items: "li",
       listType: "ul",
       tabSize: 25,
       isTree: true,
       startCollapsed: true,
       stop: function() {SaveLists();}
    });

    $('.disclose').on('click', function() {
        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
    })

   
    $('#due').datepicker();
    $('#newiteminput').keydown(function(event){
        var ItemStr = '';
        var ListItems='';
        if (event.which == 13) {  // Enter key/Carriage Return pressed
            ItemStr = $(this).val();   //Grab Value
            $(this).val('');      //Empty the input string
            //$('#trash').remove();
            $('#listdata').prepend('<li><div class="sublist"><span class="handle ui-icon ui-icon-arrowthick-2-n-s">&nbsp;</span><span class="disclose"><span></span></span><div class="xtra control">&gt;</div><input class="checkbox" type="checkbox" /><span class="itemtext">' + ItemStr + '</span><br/><div class="itemnotes"></div><div class="itemdate"></div></div></li>');    //Append new item at top of list
             //$('#listdata').append(TrashLine);  //Add back in the trash line
            $('#newitem').hide();
            SaveLists();
            SetupItem();
        }
    });
    $('#NotesNDue').draggable();

    //For Journal Section
    $('#edit1').keydown(function(e){

    var rightnow;
    var dtstr;
   switch(e.which) {
        case 117: // F7 = reset count to zero
            lcount = $('#edit1').val();
 	    lcount = lcount.match(/\n/ig);
            if (lcount == null) {lcount = -1;}
	    else lcount=lcount.length-1;
	case 13:
	    lcount++;
	    $('#lcount').html(lcount);
	    break;
        case 115:   //F4 - insert date time
                rightnow = new Date();
                dtstr = rightnow.getFullYear() + '-' + rightnow.getMonth() + '-' + rightnow.getDate() + " " + rightnow.getHours() + ':' + rightnow.getMinutes();
                $('#edit1').val($('#edit1').val()+dtstr);
                break;
	case 119:   //F8 blue on white
		$('#edit1').css({'background-color':'#ffffff','color': '#800080'});
		break;
	case 120:   //F9 blue on black
		$('#edit1').css({'background-color':'#000000','color': '#800080'});
	case 122:   //F11 smaller font
	case 123:   //F12 larger font
    } // -- end of switch --
});
    $( "#datepicker" ).datepicker();

    //If user wants to open a different list, create a new list, or clone a list
    // show the popup where they can do this
    $(".ui-icon-folder-open").click(function(){
          $.post("process.php",{
            "request":"ListLists"
        },function(data){
            data=$.parseJSON(data);
            if (data.request!="ACK") {
                alert("Could not read lists!");
            } else {
                $('#openlist').show();
                $('#openlist ul').html(''); //Empty the lists out
                for( liel in data.lists) {
                    $('#openlist ul').append("<li class='ui-widget ui-state-default'><span class='ui-icon ui-icon-pencil' style='float: left;'></span> <span class='listname'>" + data.lists[liel] + "</span> <span class='ui-icon ui-icon-trash' style='float: right;'></span></li>");
                }
                $('#openlist .ui-icon-pencil').unbind();
                $('#openlist .ui-icon.pencil').click(function(){  //Open input box over name and get new name
                    var listname = $(this).parent().children(".listname").html();
                }); //--- end of click on ui-icon-pencil

                $('#openlist .ui-icon-trash').unbind();
                $('#openlist .ui-icon-trash').click(function(){ //User clicked on trash clan next to list name
                    var listname = $(this).parent().children(".listname").html();
                    $.post("process.php",{
                        "request" : "DeleteList",
                        "listname" : listname
                    },function(data){
                        data=$.parseJSON(data);
                        if (data.request != "ACK") {
                            alert(data.msg);
                        } else {
                            alert("List has been deleted!");
                            $('#openlist').hide();
                        }
                    });
                });  // -- end of click on ui-icon-trash --

                //$('#openlist ul').selectable({
                $('#openlist ul li .listname').unbind();
                $('#openlist ul li .listname').click(function(data){
                    var ListName = $(this).html();
                    $.post("process.php",{
                        "request" : "CreateList",
                        "listname" : ListName
                    },function(data){
                        data = $.parseJSON(data);
                        if (data.request != "ACK") {
                            alert(data.msg);
                        } else {  // We did create the list and we received back the data
                            GetLists();
                            $('#openlist').hide();
                            $('#showlistname').html(ListName);
                        }
                    });
                });
            }
        });
        });
});  //------------------ END OF DOCUMENT LOAD/READY ---------------------------


//==============================================================================
// CloneList
//
// Make a copy of the current active list to a new name
// Option of removing completed items
//------------------------------------------------------------------------------
function CloneList() {
  var KeepCompleted = !$('#RemoveCompleted').attr("checked");
  var ListName = $('#clonelist').val();
  $.post("process.php",{
      "request" : "CloneList",
      "KeepCompleted" : KeepCompleted,
      "ClName" :  ListName
  },function(data){
      data=$.parseJSON(data); //decode the JSON data from the server
      if (data.request == "NAK") {  //Server gave us an error
          alert(data.msg);  // Show error to user
      } else {        //We received an ACK so everything is good
           GetLists();
            $('#openlist').hide();
            $('#showlistname').html(ListName);//So switch to list
      }
  });
} //-------------------- end of CloneList --------------------------------------

//==============================================================================
// NewList
//
// Create a new list with the given name provided
//------------------------------------------------------------------------------
function NewList() {
    var ListName = $('#newlist').val(); //Get the name of the new list
    $.post("process.php",{
        "request" : "CreateList",
        "listname" : ListName
    },function(data){
        data = $.parseJSON(data);
        if (data.request != "ACK") {
            alert(data.msg);
        } else {  // We did create the list and we received back the data
            GetLists();
            $('#openlist').hide();
            $('#showlistname').html(ListName);
        }
    });
} //------------------------ end of NewList ------------------------------------

//==============================================================================
// ShowAdd
//
// Show the new list item screen
//------------------------------------------------------------------------------
function ShowAdd(){
    $('#newitem').show();
}  //-------------------- end of ShowAdd ---------------------------------------

//==============================================================================
// SetupItem
//
// Set up the List items to respond to keyboard and mouse appropriately
//------------------------------------------------------------------------------
function SetupItem() {
    $('#listdata li').unbind();
    $('#listdata li .checkbox').change(function(){
        SaveLists();
    });
    $('#listdata li .itemtext').click(function(){
        if (!Editing) {
            var ItemText = $(this).html(); //Get text of item
            $(this).html("<input type='text' value='" + ItemText + "'/>");
            $(this).unbind();
            var ListItem = this;
            Editing = true;
            $(this).find('input').keydown(function(event){
                if (event.which==13) {
                    ItemText = $(this).val();
                    $(ListItem).html(ItemText);
                    Editing=false;
                    SaveLists();
                    SetupItem();
                }
            });
        }
    });

    // User clicked the > sign on the right side of the ToDo Item
    $('#listdata .xtra').click(function(event){
        if (NoteParent == null) {
            //Display the notes and due date popup
            event.stopPropagation();
            var ElLocation = $(this).offset(); //Get the location of the item they clicked on
            ElLocation.left = 200;
            NoteParent = $(this).parent();  //Save Parent of Note
             $('#notes').val($(NoteParent).find('.itemnotes').html());
            $('#due').val($(NoteParent).find('.itemdate').html());
            //Get the list and populate the select for the Copy/move
            /* $.post("process.php",{
                "request":"ListLists"
            },function(data){
                data=$.parseJSON(data);
                if (data.request!="ACK") {
                    alert("Could not read lists!");
                } else {
                    $('#MorCList').html(''); //Empty the lists out
                    for( liel in data.lists) {
                        $('#MorCList').append("<option value='"+data.lists[liel]  + "'> " +  data.lists[liel] + "</option>");
                    }
                }
            });
            */
            $('#NotesNDue').show().offset(ElLocation);
            $('#NotesNDue > .itemnotes').focus();
        }
    });

} //------------------------ end of SetupItem ----------------------------------

//==============================================================================
// ShowSortable
//
// If true the show the sortable handle else remove the sortable handle
//------------------------------------------------------------------------------
function ShowSortable(task) {
    if (!Editing) {
        $(".handle").addClass('ui-icon ui-icon-arrowthick-2-n-s');
        $('#showsortable').removeClass('ui-icon-circle-minus').addClass('ui-icon-circle-triangle-s');
        Editing=true;
    } else {
        $(".handle").removeClass('ui-icon ui-icon-arrowthick-2-n-s');
        $('#showsortable').removeClass('ui-icon-circle-triangle-s').addClass('ui-icon-circle-minus');
        Editing=false;
    }
} //----------------------- end of ShowSortable --------------------------------

//==============================================================================
// MorC(morcop)
// morcop = "move" or "copy"
// Move or Copy the element to the provided list
// Move = copy and destroy,
// Copy = copy and DO NOT destroy
//------------------------------------------------------------------------------
function MorC(morcop) {
    DebugMsg("MorC");
    $.post("process.php",{ //Copy the item either way (move or copy)
        "request"   : "CopyItem",
        "itemtext"  : escape($(NoteParent).find('.itemtext').html()),
        "itemnotes" : escape($(NoteParent).find('.itemnotes').html()),
        "itemdate"  : $(NoteParent).find('.itemdate').html(),
        "itemcheck" : $(NoteParent).children('.checkbox').attr('checked')?1:0,
        "listname"  : $("#MorCList option:selected").text()
    },function(data){
        data = $.parseJSON(data);
        if (data.request != "ACK") {
            alert(data.msg);
        } else {  //Everything is okay so now decide if we need to delete or leave it alone
            if (morcop == "move") { //User asked us to move
                alert("Moving");
                $(NoteParent).remove();
                SaveLists();  //Save the updated list
                GetLists();
            }
        }
    });

    $('#NotesNDue').hide();
    $('#notes').val('');
    $('#due').val('');
    NoteParent = null; //Reset Note Parent

} // ------------------------- end of MorC -------------------------------------

//==============================================================================
// SaveLists
// Data has been changed in the ToDo's list so save all of the items back to
// server
//------------------------------------------------------------------------------
function SaveLists() {
    //ListItems=escape($('#listdata').html());
    var LIItemId='#listdata'; // >li'; //First Item to look at
    var ListItems='{' + JsonLIItem(LIItemId) + '}';  
    $.post("process.php",{
        "request" : "SaveList",
        "listdata" : ListItems
    },function(data){
        data = $.parseJSON(data);
        if (data.request != "ACK") {
            alert(data.msg);
        }
    });
} //--------------------------- end of SaveLists -------------------------------

//==============================================================================
// JsonLIItem
//
//------------------------------------------------------------------------------
function JsonLIItem(LIItem){
 var JStr='';
 $(LIItem).children("li").each(function(LiIndex){
        if ($(this).attr('id')!='trash') {          // Make sure we ignore the trash item
            if (LiIndex >0) JStr += ",";
            var ItemChecked = $(this).find('.checkbox').attr('checked')?1:0;
            JStr += '"' + LiIndex + '":{';
            JStr += '"itemtext":"' + escape($(this).find('.itemtext').html()) + '",';
            JStr += '"itemnotes":"' + escape($(this).find('.itemnotes').html()) + '",';
            JStr += '"itemcheck":"' + ItemChecked + '",';
            JStr += '"itemdate":"' +  escape($(this).find('.itemdate').html()) + '"';          
            if (typeof $(this).children("ul") != 'undefined') {  //If there is a subitem
                JStr += ',"sublist":{' + JsonLIItem($(this).children("ul")) + '}'; // >li")) + '}'; //Process child list
            }
            JStr += '}';
        }
    });
 return(JStr);
} //----------------------- end of JsonLIItem -------------------------------------

//==============================================================================
// GetLists()
//
// Read list from Server
// Show on screen
//------------------------------------------------------------------------------
function GetLists() {
    $.post("process.php",{
        "request" : "GetList"
    },function(data){
        var LiItem="";
        var ItemChecked = "";
        data = $.parseJSON(data);
        data = (data.listdata);
        $('#listdata').html('');
        for (item in data) {           
            LiItem = HtmlList(data[item]);
            //Check for sublists and add them in if necessary
            if (typeof data[item].sublist != 'undefined' && !$.isEmptyObject(data[item].sublist)) {
                LiItem += "<ul>";
                for(sitem in data[item].sublist) {
                    LiItem += HtmlList(data[item].sublist[sitem]);
                }
                 LiItem += '</div>';
                 LiItem += '</li>';
                 LiItem += '</ul>';
                 
            } //--end of while
            LiItem += '</div>';
            LiItem += '</li>';
            $('#listdata').append(LiItem);    //Append new item at top of list
        }
        $('#listdata').append(TrashLine);  //Add back in the trash line
        SetupTrash();
        SetupItem();
    });
} //--------------------------- end of GetLists --------------------------------

//==============================================================================
// HtmlList
//
// Return the HTML string for the given list item data
//------------------------------------------------------------------------------
function HtmlList(ListItem) {
    var ItemChecked = (ListItem.itemcheck=="0")?"":'checked="checked"';
    var LiItem = '<li><div class="sublist"><span class="handle ui-icon ui-icon-arrowthick-2-n-s">&nbsp;</span>';
    LiItem += '<span class="disclose"><span></span></span>';
    LiItem += '<div class="xtra control">&gt;</div><input class="checkbox" type="checkbox"' +  ItemChecked + '/>';
    //LiItem += ''; //Allow for nested lists
    LiItem += '<span class="itemtext">' + unescape(ListItem.itemtext) + '</span><br/>';
    LiItem += '<div class="itemnotes">' + unescape(ListItem.itemnotes) + '</div>';
    LiItem += '<div class="itemdate">' +  unescape(ListItem.itemdate) + '</div>';
    return(LiItem);
} //----------------------- end of HtmlList ------------------------------------

//==============================================================================
// SetupTrash
//
// If user clicks on trash can then immediately remove the trash item from the
// DOM.  Otherwise the trash items are not saved and therefore will not appear
// when this list is reloaded
// 
// 
//------------------------------------------------------------------------------
function SetupTrash(){
 $('#trash .ui-icon-trash').click(function(){ //Remove Trash when icon clicked
        $('#trash ul').each(function(){ $(this).remove(); });
  });
} //--------------------- end of SetupTrash ------------------------------------

//==============================================================================
// UpdateNotesNDue
//
// User has finished updating notes and due information - update the item list
// with this new information
//------------------------------------------------------------------------------
function UpdateNotesNDue() {
    var NoteTxt = $('#notes').val();
    var DueTxt = $('#due').val();
    $(NoteParent).find('.itemnotes').html(NoteTxt);
    $(NoteParent).find('.itemdate').html(DueTxt);
    NoteParent = null; //Reset Note Parent
    $('#NotesNDue').hide();
    $('#notes').val('');
    $('#due').val('');
    SaveLists();
} //----------------------- end of UpdateNotesNDue -----------------------------

//==============================================================================
// SaveJournal
//
// Save the Journal text to the web
//------------------------------------------------------------------------------
function SaveJournal() {
    DebugMsg("SaveJournal");
    var JournalText = $('#edit1').val();
    $.post('process.php',{
        "request" : "SaveJournal",
        "journal" : escape(JournalText)
    },function(data){
         data = $.parseJSON(data);
        if (data.request != "ACK") {
            alert(data.msg);
        } else {
            $('#journalsaved').html("saved: " + Date());
        }
    });

} //------------------------ end of SaveJournal --------------------------------

//==============================================================================
// GetJournal
//
// Get The Specified Journal from web
//------------------------------------------------------------------------------
function LoadJournal() {
    var jdate = $('#datepicker').val(); //get the date wanted from the input box
  $.post('process.php',{
      "request" : "GetJournal",
      "jdate" : jdate
  },function(data){
      data = $.parseJSON(data);
      if (data.request != "ACK") {
            alert(data.msg);
        } else {
            $('#edit1').val(unescape(data.journal));
        }
        $('#loadjournal').hide();
  });
}  //----------------------- end of GetJournal ---------------------------------

//==============================================================================
//DebugMsg
//
//Add a debug msg to the debug div
//------------------------------------------------------------------------------
function DebugMsg(dmsg){
 $('#debug').append(dmsg + '<br/>');

} //------------------------- end of DebugMsg ----------------------------------
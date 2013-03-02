GListI
======

List Manager, To Do's, etc.  Multi level
Uses JQuery, JQuery UI (with sortable) and nestedsortable.js plugin by https://github.com/mjsarfatti/nestedSortable  
Lists are each a flat json file in a 'lists' subfolder

##Status
###2012-03-01
Hooked into a localhost MySQL database - check includes/constant.php for database parameters. Have not created
the admin screen yet so you have to add/edit the users in the MySQL database by hand.  
In the setup folder there is a users.sql which is a MySQL script for creating the table.

###2012-02-28
Right now there is only one login: admin admin 
Journal is a daily journal auto saved by date - non-configurable (see plan)

#The Plan (in no particular order)
(IN PROGRESS) 1. Hook into a MySQL database for login username & password. With an admin screen to administer  
2. Allowing sharing of lists between users specified by a username or email list.  
3. Fixup some of the list handling (long list of stuff here)  
4. Store the lists in MySQL instead of flat files.  
5. Allow the tie of a Journal to an item, so each item can have a journal of activity tied to it.
6. Create a mobile version

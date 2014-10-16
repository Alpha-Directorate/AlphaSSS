<?php

 //********************************************start of help
 
 
 
 function pg_help() {
 ?>
			
						<table class="form-table">
					
					<tr valign="top">
						<th colspan="2">
						
						<h3>Forum Visibility Settings</h3>

<p>You have various display options</p>

<h4><span style="color:blue">Only logged in users see group forums, and even then only ones that they have access to.</span></h4>

<p><i>Description : </i> the default view.  Allows unlimited groups with unique combination of access to forums, but they only see those they have access to.</p>

<p><i>To set : </i>Do not set the visibility.</p>

<h4><span style="color:blue">All forum titles (and optionally descriptions) visible to both logged on and non-logged on users.</span></h4>

<p><i>Description: </i>Users and non-users will be able to see that group forums exist, but not access topics/replies.</p>

<p><i>Possible Usage : </i>show that lots of forums exist, with ability to go to “sign-up” page.</p>

<p><i>To set: </i>Set visibility to public below and set forums to public in each forum’s attributes (dashboard>forums).</p>

<h4><span style="color:blue">Public forums titles (and optionally descriptions) visible to logged on and non-logged on users.
Private forums set visible to logged on users</span></h4>

<p><i>Description : </i>Non-logged in will only see group forums that are public, but not access topics/replies.  Logged in users will see and access topics/replies for Private Group forums that are set to public, private forums that have no groups set will be accessible to all logged in users, but private group forums that the user does not belong to will be hidden. </p> 

<p><i>Possible Usage : </i>Show some group forums exists, whilst keeping others visible only for private groups.  Allows rich display options for each group, and user.</p>

<p><i>To set : </i>Set visibility to public below and set any private forums to private in that forum’s attributes (dashboard>forums). </p>

 </th>
</tr>
</table>
<table class="form-table">
					
<tr valign="top">
<th colspan="2">
<h3>Role Capabilities</h3>
										
<tr>
<td>
<a href="http://www.rewweb.co.uk/wp-content/uploads/2014/10/private_group_role.jpg"><img src="http://www.rewweb.co.uk/wp-content/uploads/2014/10/private_group_role.jpg"/> </a>
</td>
</tr>
</table>
<table class="form-table">
					
<tr valign="top">
	<th colspan="2">
<h3>Group Allocation</h3>
<tr>
<td colspan="2">
<p>You can allocate users to groups in two places</p>
<p> 1. Within this settings area, you can use 'user management' To set single groups, you can use either 'bulk actions' or edit each user (you will see an edit when you hover over the user) .
The 'bulk actions' CANNOT be used to set a multiple groups group against a user or users.  To set multiple groups, edit each user.  </p>
<p> 2. Within Dashboard>users and edit user you can set one or more groups agianst the user.</p>

</td>
</tr>					
<tr>
<td>
<a href="http://www.rewweb.co.uk/wp-content/uploads/2014/10/private_group_groups.jpg"><img src="http://www.rewweb.co.uk/wp-content/uploads/2014/10/private_group_groups.jpg"/> </a>
</td>
<td>
<p><i>Example 1 : </i> A simple relationship where a forum belongs to a group, and users are allocated to that group.</p>
<p><i>Example 2 : </i> Group 1 users can only see forum A, but group 2 users can see both forum A and B.</p>
<p><p><p><i>Example 3 : </i> A complex arrangement.  User M can only see forum A, User N can see forum A and C, and users P and Q can see both Forum A and B.</p>
					
</td>
</tr>
					

</table>
</div><!--end sf-wrap-->
</div><!--end wrap-->
<?php
}
?>

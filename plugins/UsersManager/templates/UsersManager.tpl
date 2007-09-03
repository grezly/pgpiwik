<script type="text/javascript" src="libs/jquery/jquery.js"></script>
<script type="text/javascript" src="themes/default/common.js"></script>

{literal}

<style>
#access td, #users td
{ 
	spacing: 0px;
	padding: 2px 5px 5px 4px; 
	border: 1px solid #660000; 
	width:100px;
}


#ajaxError{
	color:red;
	text-align:center;
	font-weight:bold;
	width:550px;
	border: 3px solid red;
	margin: 10px;	
	padding: 10px;
}

#addrow img {
	 vertical-align:middle;
}
#addrow a {
	 text-decoration:none;
}

#accessUpdated{
	display:none;
	border:2px solid green;
	color:green;
	width:100px;
	text-align:center;
}

</style>
{/literal}

<h2>Access</h2>

<div id="sites">
<form method="get" action="{$formUrl}" id="accessSites">
	<input type="hidden" name="module" value="UsersManager">
	<p>Sites: <select id="selectIdsite" name="idsite" onchange="this.form.submit()">
	
	<optgroup label="All websites">
	   	<option label="All websites" value="-1" {if $idSiteSelected==-1} selected="selected"{/if}>Apply to all websites</option>
	</optgroup>
	<optgroup label="Sites">
	   {foreach from=$websites item=info}
	   		<option value="{$info.idsite}" {if $idSiteSelected==$info.idsite} selected="selected"{/if}>{$info.name}</option>
	   {/foreach}
	</optgroup>
	
	</select></p>
</form>
</div>

<table id="access">
<tr>
	<td>User</td>
	<td>No access</td>
	<td>View</td>
	<td>Admin</td>
</tr>
{foreach from=$usersAccessByWebsite key=login item=access}
{assign var=accesValid value="<img src='plugins/UsersManager/images/ok.png' class='accessGranted'>"}
{assign var=accesInvalid value="<img src='plugins/UsersManager/images/no-access.png' class='updateAccess'>"}
<tr>
	<td id='login'>{$login}</td>
	<td id='noaccess'>{if $access=='noaccess' and $idSiteSelected!=-1}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='view'>{if $access=='view' and $idSiteSelected!=-1}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
	<td id='admin'>{if $access=='admin' and $idSiteSelected!=-1}{$accesValid}{else}{$accesInvalid}{/if}&nbsp;</td>
</tr>
{/foreach}
</table>

<div id="accessUpdated">Done!</div>


<h2>Users</h2>

<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">Loading... <img src="themes/default/loading.gif"></div>
<table id="users">
    <tbody>
        <tr>
            <td>Login</td>
            <td>Password</td>
            <td>Email</td>
            <td>Alias</td>
            <td>Edit</td>
            <td>Delete</td>
        </tr>
        {foreach from=$users item=user key=i}
        <tr class="editable" id="row{$i}">
            <td id="userLogin" class="editable">{$user.login}</td>
            <td id="password" class="editable">-</td>
            <td id="email" class="editable">{$user.email}</td>
            <td id="alias" class="editable">{$user.alias}</td>
            <td><img src='plugins/UsersManager/images/edit.png' class="edituser" id="row{$i}" href='#'></td>
            <td><img src='plugins/UsersManager/images/remove.png' class="deleteuser" id="row{$i}" value="Delete"></td>
        </tr>
        {/foreach}
    </tbody>
    
</table>
<div id="addrow"><img src='plugins/UsersManager/images/add.png'> <a href="#">Add a new user</a></div>
<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>

<?php

if($loguser['powerlevel'] < 3)
	Kill(__("You're not an administrator. There is nothing for you here."));

$ip = $_GET["id"];
if(!filter_var($ip, FILTER_VALIDATE_IP))
	Kill("Invalid IP");

$links = new PipeMenu();
$links -> add(new PipeMenuAnyLinkEntry(__("WHOIS query"), "http://dnsquery.org/ipwhois/$ip"));
$links -> add(new PipeMenuHtmlEntry("<a onclick=\"if(confirm('Are you sure you want to IP-ban $ip?')) {document.getElementById('banform').submit();} return false;\" href=\"#\">IP Ban</a>"));
makeLinks($links);

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry(__("Admin"), "admin"));
$crumbs->add(new PipeMenuLinkEntry(__("IP bans"), "ipbans"));
$crumbs->add(new PipeMenuLinkEntry($ip, "ipquery", $id));
makeBreadcrumbs($crumbs);

$rUsers = Query("select * from {users} where lastip={0}", $ip);

echo "<h3>".__("Users with this IP")."</h3>";

$userList = "";
$ipBanComment = "";
$i = 1;
if(NumRows($rUsers))
{
	while($user = Fetch($rUsers))
	{
		$ipBanComment .= $user["name"]." ";
		$cellClass = ($cellClass+1) % 2;
		if($user['lasturl'])
			$lastUrl = "<a href=\"".$user['lasturl']."\">".$user['lasturl']."</a>";
		else
			$lastUrl = __("None");

		$userList .= format(
	"
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td>
				{2}
			</td>
			<td>
				{3}
			</td>
			<td>
				{4}
			</td>
			<td>
				{5}
			</td>
			<td>
				{6}
			</td>
		</tr>
	",	$cellClass, $i, UserLink($user), cdate("d-m-y G:i:s", $user['lastactivity']),
		($user['lastposttime'] ? cdate("d-m-y G:i:s",$user['lastposttime']) : __("Never")),
		$lastUrl, formatIP($user['lastip']));
		$i++;
	}
}
else
	$userList = "<tr class=\"cell0\"><td colspan=\"6\">".__("No users")."</td></tr>";

echo "<form id=\"banform\" action=\"".actionLink('ipbans')."\" method=\"post\">
	<input type=\"hidden\" name=\"ip\" value=\"$ip\">
	<input type=\"hidden\" name=\"reason\" value=\"".htmlentities($ipBanComment)."\">
	<input type=\"hidden\" name=\"days\" value=\"0\">
	<input type=\"hidden\" name=\"actionadd\" value=\"yes, do it!\">
</form>";

echo "
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th style=\"width: 30px;\">
				#
			</th>
			<th>
				".__("Name")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last view")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last post")."
			</th>
			<th>
				".__("URL")."
			</th>
			<th style=\"width: 140px;\">
				".__("IP")."
			</th>
		</tr>
		$userList
	</table>";

echo "<h3>".__("Log entries from this IP")."</h3>";
doLogList("l.ip='".sqlEscape($ip)."'");



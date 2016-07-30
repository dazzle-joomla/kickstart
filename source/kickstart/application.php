<?php
/**
 * Akeeba Kickstart
 * A JSON-powered archive extraction tool
 *
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

// Register additional feature classes
callExtraFeature();

// Is this a CLI call?
$isCli = !isset($_SERVER) || !is_array($_SERVER);

if (isset($_SERVER) && is_array($_SERVER))
{
	$isCli = !array_key_exists('REQUEST_METHOD', $_SERVER);
}

if (isset($_GET) && is_array($_GET) && !empty($_GET))
{
	if (isset($_GET['cli']))
	{
		$isCli = $_GET['cli'] == 1;
	}
	elseif (isset($_GET['web']))
	{
		$isCli = $_GET['web'] != 1;
	}
}

// Route the application
if ($isCli)
{
	kickstart_application_cli();
}
else
{
	kickstart_application_web();
}

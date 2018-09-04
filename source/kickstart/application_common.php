<?php
/**
 * Akeeba Kickstart
 * A JSON-powered archive extraction tool
 *
 * @copyright Copyright (c)2008-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

/**
 * Clear the code caches for the extracted files. Used when finalizing the restoration.
 *
 * @return  void
 */
function clearCodeCaches()
{
	// Zend OPcache
	if (function_exists('opcache_reset'))
	{
		opcache_reset();
	}

	// APC code cache
	if (function_exists('apc_clear_cache'))
	{
		@apc_clear_cache();
	}
}

/**
 * Removes all files pertaining to Kickstart.
 *
 * Using when finalizing the archive extraction from the web
 *
 * @param   AKAbstractPostproc   $postProc  The post-processing engine of Akeeba Restore in use
 */
function removeKickstartFiles(AKAbstractPostproc $postProc)
{
	// Remove self
	$postProc->unlink(basename(__FILE__));

	// Delete translations
	removeKickstartTranslationFiles($postProc);

	// Delete feature files
	deleteKickstartFeatureFiles($postProc);

	// Delete the temporary directory IF AND ONLY IF it's called "kicktemp"
	deleteKickstartTempDirectory($postProc);

	// Delete cacert.pem
	$postProc->unlink('cacert.pem');

	// Delete jquery.min.js and json2.min.js
	$postProc->unlink('jquery.min.js');
	$postProc->unlink('json2.min.js');
}

/**
 * Remove feature files, e.g. kickstart.transfer.php
 *
 * @param AKAbstractPostproc $postProc
 *
 * @return void
 */
function deleteKickstartFeatureFiles(AKAbstractPostproc $postProc)
{
	$dh = opendir(AKKickstartUtils::getPath());

	if ($dh === false)
	{
		return;
	}

	$basename = basename(__FILE__, '.php');

	while (false !== $file = @readdir($dh))
	{
		if (
			(substr($file, 0, strlen($basename) + 1) == $basename . '.')
			&& (substr($file, -4) == '.php')
		)
		{
			$postProc->unlink($file);
		}
	}

	closedir($dh);
}

/**
 * Delete the temporary directory IF AND ONLY IF it's called "kicktemp"
 *
 * @param AKAbstractPostproc $postProc
 *
 * @return void
 */
function deleteKickstartTempDirectory(AKAbstractPostproc $postProc)
{
	$tempDir = $postProc->getTempDir();
	$tempDir = trim($tempDir);

	if (empty($tempDir))
	{
		return;
	}

	$basename = basename($tempDir);

	if (strtolower($basename) != 'kicktemp')
	{
		return;
	}

	recursive_remove_directory($tempDir);
}

/**
 * Delete language files, e.g. el-GR.kickstart.ini
 *
 * @param AKAbstractPostproc $postProc
 *
 * @return void
 */
function removeKickstartTranslationFiles(AKAbstractPostproc $postProc)
{
	$dh = opendir(AKKickstartUtils::getPath());

	if ($dh === false)
	{
		return;
	}

	$basename = basename(__FILE__, '.php');

	while (false !== $file = @readdir($dh))
	{
		if (strstr($file, $basename . '.ini'))
		{
			$postProc->unlink($file);
		}
	}

	closedir($dh);
}

/**
 * Finalization after the restoration. Removes the installation directory, the backup archive and rolls back automatic
 * file renames.
 *
 * @param   AKAbstractUnarchiver  $unarchiver  The unarchiver engine used by Akeeba Restore
 * @param   AKAbstractPostproc    $postProc    The post-processing engine used by Akeeba Restore
 */
function finalizeAfterRestoration(AKAbstractUnarchiver $unarchiver, AKAbstractPostproc $postProc)
{
    // Remove installation
	recursive_remove_directory('installation');

	// Run the renames, backwards
	rollbackAutomaticRenames($unarchiver, $postProc);

	// Delete the archive
	foreach ($unarchiver->archiveList as $archive)
	{
		$postProc->unlink($archive);
	}
}

/**
 * Rolls back automatic file renames.
 *
 * @param   AKAbstractUnarchiver  $unarchiver  The unarchiver engine used by Akeeba Restore
 * @param   AKAbstractPostproc    $postProc    The post-processing engine used by Akeeba Restore
 */
function rollbackAutomaticRenames(AKAbstractUnarchiver $unarchiver, AKAbstractPostproc $postProc)
{
	$renameBack = AKFactory::get('kickstart.setup.renameback', true);

	if ($renameBack)
	{
		$renames = $unarchiver->renameFiles;

		if (!empty($renames))
		{
			foreach ($renames as $original => $renamed)
			{
				$postProc->rename($renamed, $original);
			}
		}
	}
}

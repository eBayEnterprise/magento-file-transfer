<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class EbayEnterprise_FileTransfer_Helper_File
{
	/**
	 * Get an Iterable of SPLFileInfo objects for all files in the given directory
	 * that match the specified pattern.
	 * @param  string $localDirectory
	 * @param  string $pattern
	 * @return FilesystemIterator Constructed with the FilesystemIterator::CURRENT_AS_PATHNAME flag
	 * @codeCoverageIgnore Dependency on the file system makes this trivial code non-trivial to test.
	 */
	public function listFilesInDirectory($localDirectory, $pattern='*')
	{
		return new GlobIterator($localDirectory . DS . $pattern);
	}
	/**
	 * Move the file to the target directory, creating any needed directories.
	 * If the file was moved successfully, should return true. False otherwise.
	 * All relative paths will be resolved relative to Mage::getBaseDir('var').
	 * The local file may be relative or absolute but must have appropriate
	 * permissions, target dir must be relative.
	 * @param  string $srcFile Current location of the file to move
	 * @param  string $targetPath Relative path to target directory
	 * @return boolean True if file moved successfully, false otherwise
	 */
	public function mvToDir($srcFile, $targetPath)
	{
		$file = Mage::getModel('Varien_Io_File');

		// open relative to var dir - makes all realtive paths relative to that directory
		$file->open(array('path' => Mage::getBaseDir('var')));

		// clean the given paths so they should be a bit easier to work with
		$srcFile = $file->getCleanPath($srcFile);

		// Can't move a file that doesn't exist
		if (!$file->fileExists($srcFile)) {
			throw new EbayEnterprise_FileTransfer_Exception_Configuration(
				sprintf('Cannot move %s: No such file or directory', $srcFile)
			);
		}

		// Make sure the given target path is relative. If it isn't, throw an
		// exception as this is not allowed.
		if (strpos($targetPath, '/') === 0) {
			throw new EbayEnterprise_FileTransfer_Exception_Configuration(
				sprintf('Path must be relative, given %s', $targetPath)
			);
		}

		// absolutize the path within the Mage 'var' directory
		$targetPath = $file->getCleanPath(Mage::getBaseDir('var') . DS . $targetPath);

		// checkAndCreateFolder may throw a generaic exception which may not be
		// all that useful to calling methods. Catch the generic exception from
		// the checkAndCreateFolder method and throw a more useful exception.
		try {
			$file->checkAndCreateFolder(dirname($targetPath));
		} catch (Exception $e) {
			throw new EbayEnterprise_FileTransfer_Exception_Configuration(
				sprintf(
					"Target directory '%s' does not exist and could not be created.",
					dirname($targetPath)
				)
			);
		}

		return $file->mv($srcFile, $targetPath);
	}
}

<?php
namespace Ameos\AmeosFilemanager\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
 
class FilemanagerUtility
{
    /**
     * check recursion
     * @param \Ameos\AmeosFilemanager\Domain\Model\Folder $rootFolder
     * @param \Ameos\AmeosFilemanager\Domain\Model\Folder $childFolder
     * @param int $recursion
     */
    public static function hasTooMuchRecursion($rootFolder, $childFolder, $recursion)
    {
        if (!$recursion) {
            return false;
        }        
        return self::calculRecursion($rootFolder, $childFolder) > $recursion;
    }

    /**
     * check is is the last recursion
     * @param \Ameos\AmeosFilemanager\Domain\Model\Folder $rootFolder
     * @param \Ameos\AmeosFilemanager\Domain\Model\Folder $childFolder
     * @param int $recursion
     */
    public static function isTheLastRecursion($rootFolder, $childFolder, $recursion)
    {
        if (!$recursion) {
            return false;
        }        
        return self::calculRecursion($rootFolder, $childFolder) >= $recursion;
    }
    
    /**
     * calcul recursion
     * @param \Ameos\AmeosFilemanager\Domain\Model\Folder $rootFolder
     * @param \Ameos\AmeosFilemanager\Domain\Model\Folder $childFolder
     * @return int $recursion
     */
    public static function calculRecursion($rootFolder, $childFolder)
    {
        $deltaPath = trim(str_replace($rootFolder->getGedPath(), '', $childFolder->getGedPath()), '/');
        $deltaPart = GeneralUtility::trimExplode('/', $deltaPath);
        return count($deltaPart);
    }

    /**
	 * return the image corresponding to the given extension
	 * @param string $type extension of the file
	 * @param string $iconFolder icon to look for the images
	 * @return string
	 */
	public static function getImageIconeTagForType($type, $iconFolder)
    {
		if(empty($iconFolder)) {
			$iconFolder = 'typo3conf/ext/ameos_filemanager/Resources/Public/Icons/';
		}

		switch ($type) {
			case 'folder':
				if(file_exists($iconFolder.'icon_folder.png')) {
					return '<img src="'.$iconFolder.'icon_folder.png" alt="folder" title="folder" class="icone_file_manager" />';
				}
				else {
					return self::getDefaultIcon($iconFolder);
				}
				break;
			case 'previous_folder':
				if(file_exists($iconFolder.'icon_previous_folder.png')) {
					return '<img src="'.$iconFolder.'icon_previous_folder.png" alt="folder" title="folder" class="icone_file_manager" />';
				}
				else {
					return self::getDefaultIcon($iconFolder);
				}
				break;
			default:
				if(file_exists($iconFolder.'icon_'.$type.'.png')) {
					return '<img src="'.$iconFolder.'icon_'.$type.'.png" alt="file" title="file" class="icone_file_manager" />';
				}
				else {
					return '<i class="fa fa-file-text-o" aria-hidden="true"></i>';
					return self::getDefaultIcon($iconFolder);
				}
				break;
		}
	}

	/**
	 * return the default icon
	 * @param string $iconFolder icon to look for the images
	 * @return string
	 */
	public static function getDefaultIcon($iconFolder)
    {
		if (file_exists($iconFolder.'icon_default_file.png')) {
			return '<img src="'.$iconFolder.'icon_default_file.png" alt="file" title="file" class="icone_file_manager" />';
		} else {
			return '<img src="typo3conf/ext/ameos_filemanager/Resources/Public/Icons/icon_default_file.png" alt="file" title="file" class="icone_file_manager" />';
		}
	}

    
	/**
	 * return objects of $repo where uid in $uids
	 * @param Repository $repo
	 * @param array $uids
	 * @return object
	 */
	public static function getByUids($repo, $uids)
    {
		if (!is_array($uids)) {
			$uids = explode(',', $uids);
		}
		$query = $repo->createQuery();
		$query->matching($query->in('uid', $uids));
		return $query->execute();
	}

	/**
	 * return folder parent
	 * @param integer $uid uid of the child folder
	 * @return string
	 */
	public static function getFolderPathFromUid($uid)
    {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'uid_parent, title',
			'tx_ameosfilemanager_domain_model_folder',
			'tx_ameosfilemanager_domain_model_folder.uid = '.$uid
		);
		if ($res['uid_parent'] != '' && $res['uid_parent'] != 0) {
			return self::getFolderPathFromUid($res['uid_parent']).'/'.$res['title'];
		}
		return '/'.$res['title'];
	}

    /**
     * parse folder for indexing new content
     */ 
	public static function parseFolderForNewElements($storage, $folderIdentifier, $folderName)
    {
		$slot = GeneralUtility::makeInstance('Ameos\AmeosFilemanager\Slots\Slot');
		$falFolder = GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\Folder', $storage, $folderIdentifier, $folderName);
		$subfolders = $falFolder->getSubfolders();
		foreach ($subfolders as $folder) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid", "tx_ameosfilemanager_domain_model_folder", "tx_ameosfilemanager_domain_model_folder.title like '".$folder->getName()."'" );
			$exist = false;
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				// Si il n'existe on ne fait rien
				if (self::getFolderPathFromUid($row['uid']).'/' == $folder->getIdentifier()) {
					$exist = true;
					$uid = $row['uid'];
					break;
				}
			}
			if (!$exist) {
				$slot->postFolderAdd($folder);
			}
		}
		
		$files = $falFolder->getFiles();
		foreach ($files as $file) {
			$slot->postFileAdd($file,$falFolder);
		}
	}
}
<?php
namespace Ameos\AmeosFilemanager\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Ameos\AmeosFilemanager\Tools\Tools;
use Ameos\AmeosFilemanager\Domain\Model\Folder;
use Ameos\AmeosFilemanager\Domain\Model\File;
use Ameos\AmeosFilemanager\Domain\Repository\FolderRepository;

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
 
class DisplayRowViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * Initializes arguments
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerArgument('folder',   Folder::class, 'Folder', false);
        $this->registerArgument('file',     File::class, 'File', false);
        $this->registerArgument('settings', 'array', 'Settings.', false);
    }

    /**
     * This method decides if the condition is TRUE or FALSE
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    static protected function evaluateCondition($arguments = null)
    {
        if (!is_a($arguments['folder'], Folder::class) && !is_a($arguments['file'], File::class)) {
            throw new \Exception('DisplayRowViewHelper : Folder or File are required');
        }
        if ($arguments['settings']['displayArchive'] == 1) {
            return true;
        }
        
        if (is_a($arguments['folder'], Folder::class)) {
            return self::testFolder($arguments['folder']);
        }

        if (is_a($arguments['file'], File::class)) {
            if ($arguments['file']->getStatus() == 1) {
                return true;
            }            
            return self::testFolder($arguments['file']->getParentFolder());
        }
        
        return false;
    }

    /**
     * test folder archive state
     */ 
    static protected function testFolder($folder)
    {
        $folderRepository = GeneralUtility::makeInstance(FolderRepository::class);
        do {
            if ($folder->getStatus() == 2) {
                return false;
            }
            if ($folder->getStatus() == 1) {
                return true;
            }
            $folder = $folder->getParent();
            
        } while ($folder);
        return false;
    }
}

<?php

declare(strict_types=1);

namespace Ameos\AmeosFilemanager\EventListener\Core\Resource;

use Ameos\AmeosFilemanager\Service\FolderService;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\Event\BeforeFolderDeletedEvent;
use TYPO3\CMS\Core\Resource\Folder as ResourceFolder;

class BeforeFolderDeletedEventListener
{
    /**
     * @param FolderService $folderService
     */
    public function __construct(private readonly FolderService $folderService)
    {
    }

    /**
     * invoke event
     *
     * @param BeforeFolderDeletedEvent $event
     * @return void
     */
    public function __invoke(BeforeFolderDeletedEvent $event): void
    {
        $this->unindex($event->getFolder());
    }

    /**
     * unindex
     *
     * @param ResourceFolder $folder
     * @return void
     */
    protected function unindex(ResourceFolder $folder): void
    {
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $this->folderService->unindex($folder);
        }
    }
}

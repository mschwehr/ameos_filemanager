<?php

declare(strict_types=1);

namespace Ameos\AmeosFilemanager\Enum;

class Configuration
{
    public const EXTENSION_KEY = 'AmeosFilemanager';

    public const SETTINGS_STARTFOLDER = 'startFolder';
    public const SETTINGS_RECURSION = 'recursion';

    public const TABLENAME_FOLDER = 'tx_ameosfilemanager_domain_model_folder';
    public const TABLENAME_CONTENT = 'tx_ameosfilemanager_domain_model_filecontent';
    public const TABLENAME_DOWNLOAD = 'tx_ameosfilemanager_domain_model_filedownload';
}

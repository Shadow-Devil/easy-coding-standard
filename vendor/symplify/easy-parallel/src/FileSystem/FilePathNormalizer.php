<?php

declare (strict_types=1);
namespace ECSPrefix202301\Symplify\EasyParallel\FileSystem;

use ECSPrefix202301\Symplify\SmartFileSystem\SmartFileInfo;
final class FilePathNormalizer
{
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return string[]
     */
    public function resolveFilePathsFromFileInfos(array $fileInfos) : array
    {
        $filePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $filePaths[] = $fileInfo->getRelativeFilePathFromCwd();
        }
        return $filePaths;
    }
}

<?php

declare (strict_types=1);
namespace ECSPrefix202311;

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
return static function (ECSConfig $ecsConfig) : void {
    $ecsConfig->paths([__DIR__ . '/config', __DIR__ . '/ecs.php', __DIR__ . '/rector.php', __DIR__ . '/easy-ci.php', __DIR__ . '/src', __DIR__ . '/tests']);
    $ecsConfig->sets([SetList::COMMON, SetList::PSR_12]);
};

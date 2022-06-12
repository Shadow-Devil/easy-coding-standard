<?php

declare (strict_types=1);
namespace ECSPrefix20220612\Symplify\RuleDocGenerator\Contract;

use ECSPrefix20220612\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @api
 */
interface DocumentedRuleInterface
{
    public function getRuleDefinition() : RuleDefinition;
}

<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace ECSPrefix20220115\Nette\Neon\Node;

use ECSPrefix20220115\Nette\Neon\Entity;
use ECSPrefix20220115\Nette\Neon\Node;
/** @internal */
final class EntityNode extends \ECSPrefix20220115\Nette\Neon\Node
{
    /** @var Node */
    public $value;
    /** @var ArrayItemNode[] */
    public $attributes = [];
    public function __construct(\ECSPrefix20220115\Nette\Neon\Node $value, array $attributes, int $startPos = null, int $endPos = null)
    {
        $this->value = $value;
        $this->attributes = $attributes;
        $this->startPos = $startPos;
        $this->endPos = $endPos ?? $startPos;
    }
    public function toValue() : \ECSPrefix20220115\Nette\Neon\Entity
    {
        return new \ECSPrefix20220115\Nette\Neon\Entity($this->value->toValue(), \ECSPrefix20220115\Nette\Neon\Node\ArrayItemNode::itemsToArray($this->attributes));
    }
    public function toString() : string
    {
        return $this->value->toString() . '(' . ($this->attributes ? \ECSPrefix20220115\Nette\Neon\Node\ArrayItemNode::itemsToInlineString($this->attributes) : '') . ')';
    }
    public function getSubNodes() : array
    {
        $res = [&$this->value];
        foreach ($this->attributes as &$item) {
            $res[] =& $item;
        }
        return $res;
    }
}

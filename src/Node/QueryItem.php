<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

abstract class QueryItem
{
    /**
     * @return array
     */
    public function toArray()
    {
        return array();
    }

    /**
     * @param QueryItemVisitorinterface $visitor
     */
    abstract public function accept(QueryItemVisitorinterface $visitor);
}

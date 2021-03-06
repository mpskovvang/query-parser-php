<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Builder\QueryBuilder;

final class Subquery extends Node
{
    const NODE_TYPE = 'subquery';
    const COMPOUND_NODE = true;

    /** @var Node[] */
    private $nodes = [];

    /**
     * Subquery constructor.
     *
     * @param Node[] $nodes
     * @param bool $useBoost
     * @param float|mixed $boost
     *
     * @throws \LogicException
     */
    public function __construct(array $nodes, $useBoost = false, $boost = self::DEFAULT_BOOST)
    {
        parent::__construct(null, null, $useBoost, $boost);
        $this->nodes = $nodes;

        foreach ($this->nodes as $node) {
            if ($node->isCompoundNode()) {
                throw new \LogicException('A Subquery cannot contain compound nodes.  (Field, Range, Subquery)');
            }
        }
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data = [])
    {
        $useBoost = isset($data['use_boost']) ? (bool)$data['use_boost'] : false;
        $boost    = isset($data['boost']) ? (float)$data['boost'] : self::DEFAULT_BOOST;

        $nodes = [];
        if (isset($data['nodes'])) {
            foreach ($data['nodes'] as $node) {
                $nodes[] = self::factory($node);
            }
        }

        return new self($nodes, $useBoost, $boost);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['nodes'] = [];

        foreach ($this->nodes as $node) {
            $array['nodes'][] = $node->toArray();
        }

        return $array;
    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param QueryBuilder $builder
     */
    public function acceptBuilder(QueryBuilder $builder)
    {
        $builder->addSubquery($this);
    }
}

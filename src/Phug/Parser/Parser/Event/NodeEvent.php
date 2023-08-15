<?php

namespace Phug\Parser\Event;

use Phug\Event;
use Phug\Parser\NodeInterface;
use Phug\ParserEvent;

class NodeEvent extends Event
{
    private $node;

    /**
     * NodeEvent constructor.
     *
     * @param ParserEvent::* $name
     * @param NodeInterface  $node
     */
    public function __construct($name, NodeInterface $node)
    {
        parent::__construct($name);

        $this->node = $node;
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param NodeInterface $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }
}

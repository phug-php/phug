<?php

namespace Phug\Formatter;

use Phug\Formatter\Element\AssignmentElement;
use Phug\Util\AttributesOrderInterface;

interface AssignmentContainerInterface extends ElementInterface, AttributesOrderInterface
{
    public function getName();

    public function addAssignment(AssignmentElement $element);

    public function removedAssignment(AssignmentElement $element);

    public function getAssignments();

    public function getAssignmentsByName($name);
}

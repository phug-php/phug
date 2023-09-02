<?php

namespace Phug\Formatter\Format;

use Closure;
use Generator;
use InvalidArgumentException;
use Phug\Formatter;
use Phug\Formatter\AbstractFormat;
use Phug\Formatter\AssignmentContainerInterface;
use Phug\Formatter\Element\AbstractValueElement;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinCallElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\MarkupInterface;
use Phug\Formatter\Partial\AssignmentHelpersTrait;
use Phug\FormatterException;
use Phug\Util\AttributesInterface;
use Phug\Util\Joiner;
use Phug\Util\OrderedValue;
use SplObjectStorage;

class XmlFormat extends AbstractFormat
{
    use AssignmentHelpersTrait;

    const DOCTYPE = '<?xml version="1.0" encoding="utf-8" ?>';
    const OPEN_PAIR_TAG = '<%s>';
    const CLOSE_PAIR_TAG = '</%s>';
    const SELF_CLOSING_TAG = '<%s />';
    const ATTRIBUTE_PATTERN = ' %s="%s"';
    const BOOLEAN_ATTRIBUTE_PATTERN = ' %s="%s"';
    const BUFFER_VARIABLE = '$__value';

    public function __construct(Formatter $formatter = null)
    {
        parent::__construct($formatter);

        $this
            ->setOptionsDefaults([
                'attributes_mapping'    => [],
                'assignment_handlers'   => [],
                'attribute_assignments' => [],
            ])
            ->registerHelper('available_attribute_assignments', [])
            ->addPatterns([
                'open_pair_tag'             => static::OPEN_PAIR_TAG,
                'close_pair_tag'            => static::CLOSE_PAIR_TAG,
                'self_closing_tag'          => static::SELF_CLOSING_TAG,
                'attribute_pattern'         => static::ATTRIBUTE_PATTERN,
                'boolean_attribute_pattern' => static::BOOLEAN_ATTRIBUTE_PATTERN,
                'save_value'                => static::SAVE_VALUE,
                'buffer_variable'           => static::BUFFER_VARIABLE,
            ])
            ->provideAttributeAssignments()
            ->provideAttributeAssignment()
            ->provideStandAloneAttributeAssignment()
            ->provideMergeAttributes()
            ->provideArrayEscape()
            ->provideAttributesAssignment()
            ->provideClassAttributeAssignment()
            ->provideStandAloneClassAttributeAssignment()
            ->provideStyleAttributeAssignment()
            ->provideStandAloneStyleAttributeAssignment();

        $handlers = $this->getOption('attribute_assignments');
        foreach ($handlers as $name => $handler) {
            $this->addAttributeAssignment($name, $handler);
        }
    }

    protected function addAttributeAssignment($name, $handler)
    {
        $availableAssignments = $this->getHelper('available_attribute_assignments');
        $this->registerHelper($name.'_attribute_assignment', $handler);
        $availableAssignments[] = $name;

        return $this->registerHelper('available_attribute_assignments', $availableAssignments);
    }

    public function requireHelper($name)
    {
        $provider = $this->formatter
            ->getDependencies()
            ->getProvider(
                $this->helperName('available_attribute_assignments')
            );
        $required = $provider->isRequired();

        parent::requireHelper($name);

        if (!$required && $provider->isRequired()) {
            foreach ($this->getHelper('available_attribute_assignments') as $assignment) {
                $this->requireHelper($assignment.'_attribute_assignment');
            }
        }

        return $this;
    }

    public function __invoke(ElementInterface $element)
    {
        return $this->format($element);
    }

    protected function isSelfClosingTag(MarkupInterface $element, $isSelfClosing = null)
    {
        if (is_null($isSelfClosing)) {
            $isSelfClosing = $element->isAutoClosed();
        }

        if ($isSelfClosing && $element->hasChildren()) {
            $visibleChildren = array_filter($element->getChildren(), function ($child) {
                return $child && (
                    !($child instanceof TextElement) ||
                    trim($child->getValue()) !== ''
                );
            });
            if (count($visibleChildren) > 0) {
                $this->throwException(
                    $element->getName().' is a self closing element: '.
                    '<'.$element->getName().'/> but contains nested content.',
                    $element
                );
            }
        }

        return $isSelfClosing;
    }

    protected function isBlockTag(MarkupInterface $element)
    {
        return true;
    }

    public function isWhiteSpaceSensitive(MarkupInterface $element)
    {
        return false;
    }

    protected function hasNonStaticAttributes(MarkupInterface $element)
    {
        if ($element instanceof MarkupElement || $element instanceof MixinCallElement) {
            foreach ($element->getAttributes() as $attribute) {
                if ($attribute->hasStaticMember('value')) {
                    continue;
                }
                if ($attribute->getValue() instanceof ExpressionElement &&
                    $attribute->getValue()->hasStaticMember('value')) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    protected function formatAttributeElement(AttributeElement $element)
    {
        $value = $element->getValue();
        $name = $element->getName();
        $nonEmptyAttribute = ($name === 'class' || $name === 'id');
        if ($nonEmptyAttribute && (
            !$value ||
            ($value instanceof TextElement && ((string) $value->getValue()) === '') ||
            (is_string($value) && in_array(trim($value), ['', '""', "''"], true))
        )) {
            return '';
        }
        if ($value instanceof ExpressionElement) {
            if ($nonEmptyAttribute && in_array(trim($value->getValue()), ['', '""', "''"], true)) {
                return '';
            }
            if (strtolower($value->getValue()) === 'true') {
                $formattedValue = null;
                if ($name instanceof ExpressionElement) {
                    $bufferVariable = $this->pattern('buffer_variable');
                    $name = $this->pattern(
                        'php_display_code',
                        $this->pattern(
                            'save_value',
                            $bufferVariable,
                            $this->formatCode($name->getValue(), $name->isChecked())
                        )
                    );
                    $value = new ExpressionElement($bufferVariable);
                    $formattedValue = $this->format($value);
                }
                $formattedName = $this->format($name);
                $formattedValue = $formattedValue || $formattedValue === '0'
                    ? $formattedValue
                    : $formattedName;

                return $this->pattern(
                    'boolean_attribute_pattern',
                    $formattedName,
                    $formattedValue
                );
            }
            if (in_array(strtolower($value->getValue()), ['false', 'null', 'undefined'], true)) {
                return '';
            }
        }

        return $this->pattern(
            'attribute_pattern',
            $this->format($name),
            $this->format($value)
        );
    }

    protected function formatPairTagChildren(MarkupElement $element)
    {
        $firstChild = $element->getChildAt(0);
        $needIndent = (
            (
                (
                    $firstChild instanceof CodeElement &&
                    $this->isBlockTag($element)
                ) || (
                    $firstChild instanceof MarkupInterface &&
                    $this->isBlockTag($firstChild)
                )
            ) &&
            !$this->isWhiteSpaceSensitive($element)
        );

        return sprintf(
            $needIndent
                ? $this->getNewLine().'%s'.$this->getIndent()
                : '%s',
            $this->formatElementChildren($element)
        );
    }

    protected function formatPairTag($open, $close, MarkupElement $element)
    {
        return $this->pattern(
            'pair_tag',
            $open,
            $element->hasChildren()
                ? $this->formatPairTagChildren($element)
                : '',
            $close
        );
    }

    /**
     * @param AssignmentElement $element
     *
     * @throws FormatterException
     *
     * @return iterable
     */
    protected function yieldAssignmentElement(AssignmentElement $element)
    {
        foreach ($this->getOption('assignment_handlers') as $handler) {
            $iterator = $handler($element) ?: [];

            foreach ($iterator as $newElement) {
                yield $newElement;
            }
        }

        /* @var MarkupElement $markup */
        $markup = $element->getContainer();
        $attributeOrder = $this->hasOption('attribute_precedence')
            ? $this->getOption('attribute_precedence')
            : 'assignment';

        switch ($attributeOrder) {
            case 'assignment':
            case 'assignments':
                $arguments = array_merge(
                    $markup instanceof AttributesInterface
                        ? $this->formatMarkupAttributes($markup)
                        : [],
                    $markup instanceof AssignmentContainerInterface
                        ? $this->formatAttributeAssignments($markup)
                        : []
                );
                break;

            case 'attribute':
            case 'attributes':
                $arguments = array_merge(
                    $markup instanceof AssignmentContainerInterface
                        ? $this->formatAttributeAssignments($markup)
                        : [],
                    $markup instanceof AttributesInterface
                        ? $this->formatMarkupAttributes($markup)
                        : []
                );
                break;

            case 'left':
                $arguments = $this->getSortedAttributes($markup, static function (OrderedValue $a, OrderedValue $b) {
                    return $b->getOrder() - $a->getOrder();
                });
                break;

            case 'right':
                $arguments = $this->getSortedAttributes($markup, static function (OrderedValue $a, OrderedValue $b) {
                    return $a->getOrder() - $b->getOrder();
                });
                break;

            default:
                if (!is_callable($attributeOrder)) {
                    throw new InvalidArgumentException(
                        'Option attribute_precedence must be '.
                        '"assignment" (default), "attribute", "left", "right" or a callable.'
                    );
                }

                $arguments = array_map(static function ($argument) {
                    return $argument instanceof OrderedValue ? $argument->getValue() : $argument;
                }, $attributeOrder(
                    $markup instanceof AssignmentContainerInterface
                        ? $this->formatOrderedAttributeAssignments($markup)
                        : [],
                    $markup instanceof AttributesInterface
                        ? $this->formatOrderedMarkupAttributes($markup)
                        : []
                ));
        }

        foreach ($markup->getAssignments() as $assignment) {
            /* @var AssignmentElement $assignment */
            $this->throwException(
                'Unable to handle '.$assignment->getName().' assignment',
                $assignment
            );
        }

        if (count($arguments)) {
            yield $this->attributesAssignmentsFromPairs($arguments);
        }
    }

    /**
     * @param AssignmentContainerInterface $markup
     *
     * @return array<string>
     */
    protected function formatAttributeAssignments(AssignmentContainerInterface $markup)
    {
        $arguments = [];

        foreach ($this->yieldAssignmentAttributes($markup) as $attribute) {
            $arguments[] = $this->formatInnerCodeValue($attribute);
        }

        return $arguments;
    }

    /**
     * @param AssignmentContainerInterface $markup
     *
     * @return list<OrderedValue<string>>
     */
    protected function formatOrderedAttributeAssignments(AssignmentContainerInterface $markup)
    {
        $arguments = [];

        foreach ($this->yieldAssignmentOrderedAttributes($markup) as $attribute => $order) {
            $arguments[] = new OrderedValue($this->formatInnerCodeValue($attribute), $order);
        }

        return $arguments;
    }

    /**
     * @param AbstractValueElement|mixed $value
     *
     * @return string
     */
    protected function formatInnerCodeValue($value)
    {
        $checked = method_exists($value, 'isChecked') && $value->isChecked();

        while (method_exists($value, 'getValue')) {
            $value = $value->getValue();
        }

        return $this->formatCode($value, $checked);
    }

    /**
     * @param AssignmentContainerInterface $markup
     *
     * @return Generator<AbstractValueElement>
     */
    protected function yieldAssignmentAttributes(AssignmentContainerInterface $markup)
    {
        foreach ($markup->getAssignmentsByName('attributes') as $attributesAssignment) {
            /* @var AssignmentElement $attributesAssignment */
            foreach ($attributesAssignment->getAttributes() as $attribute) {
                /* @var AbstractValueElement $attribute */
                yield $attribute;
            }

            $markup->removedAssignment($attributesAssignment);
        }
    }

    /**
     * @param AssignmentContainerInterface $markup
     *
     * @return Generator<AbstractValueElement, int|null>
     */
    protected function yieldAssignmentOrderedAttributes(AssignmentContainerInterface $markup)
    {
        foreach ($markup->getAssignmentsByName('attributes') as $attributesAssignment) {
            /* @var AssignmentElement $attributesAssignment */
            foreach ($attributesAssignment->getAttributes() as $attribute) {
                /* @var AbstractValueElement $attribute */
                yield $attribute => $attributesAssignment->getOrder();
            }

            $markup->removedAssignment($attributesAssignment);
        }
    }

    /**
     * @param AttributesInterface $markup
     *
     * @return list<string>
     */
    protected function formatMarkupAttributes(AttributesInterface $markup)
    {
        $arguments = [];
        $attributes = $markup->getAttributes();

        foreach ($attributes as $attribute) {
            /* @var AttributeElement $attribute */
            $arguments[] = $this->formatAttributeAsArrayItem($attribute);
        }

        $attributes->removeAll($attributes);

        return $arguments;
    }

    /**
     * @param AttributesInterface $markup
     *
     * @return list<OrderedValue<string>>
     */
    protected function formatOrderedMarkupAttributes(AttributesInterface $markup)
    {
        $arguments = [];
        $attributes = $markup->getAttributes();

        foreach ($attributes as $attribute) {
            /* @var AttributeElement $attribute */
            $arguments[] = new OrderedValue($this->formatAttributeAsArrayItem($attribute), $attribute->getOrder());
        }

        $attributes->removeAll($attributes);

        return $arguments;
    }

    /**
     * @param AssignmentElement $element
     *
     * @throws FormatterException
     *
     * @return string
     */
    protected function formatAssignmentElement(AssignmentElement $element)
    {
        return (new Joiner($this->yieldAssignmentElement($element)))->mapAndJoin([$this, 'format'], '');
    }

    protected function hasDuplicateAttributeNames(MarkupInterface $element)
    {
        if ($element instanceof MarkupElement || $element instanceof MixinCallElement) {
            $names = [];
            foreach ($element->getAttributes() as $attribute) {
                $name = $attribute->getName();
                if (($name instanceof ExpressionElement && !$name->hasStaticValue()) ||
                    in_array($name, $names, true)
                ) {
                    return true;
                }

                $names[] = $name;
            }
        }

        return false;
    }

    protected function formatAttributes(MarkupElement $element)
    {
        if ($this->hasNonStaticAttributes($element) ||
            $this->hasDuplicateAttributeNames($element)) {
            $empty = true;
            foreach ($element->getAssignmentsByName('attributes') as $attribute) {
                $empty = false;
                break;
            }
            if ($empty) {
                $data = new SplObjectStorage();
                $data->attach(new ExpressionElement('[]'));
                $element->addAssignment(new AssignmentElement('attributes', $data, $element));
            }
        }

        foreach ($element->getAssignments() as $assignment) {
            return $this->format($assignment);
        }

        $code = '';

        foreach ($element->getAttributes() as $attribute) {
            $code .= $this->format($attribute);
        }

        return $code;
    }

    protected function formatMarkupElement(MarkupElement $element)
    {
        $tag = $this->format($element->getName());
        $saveAttributes = clone $element->getAttributes();
        $saveAssignments = clone $element->getAssignments();
        $attributes = $this->formatAttributes($element);
        $dirtyAttributes = $element->getAttributes();
        $dirtyAttributes->removeAll($dirtyAttributes);
        $dirtyAttributes->addAll($saveAttributes);
        $dirtyAssignments = $element->getAssignments();
        $dirtyAssignments->removeAll($dirtyAssignments);
        $dirtyAssignments->addAll($saveAssignments);

        $tag = $this->isSelfClosingTag($element)
            ? $this->pattern(
                $element->isAutoClosed() && $this->hasPattern('explicit_closing_tag')
                    ? 'explicit_closing_tag'
                    : 'self_closing_tag',
                $tag.$attributes
            )
            : $this->formatPairTag(
                $this->pattern('open_pair_tag', $tag.$attributes),
                $this->pattern('close_pair_tag', $tag),
                $element
            );

        return !$element->isAutoClosed() && $this->isBlockTag($element)
            ? $this->getIndent().$tag.$this->getNewLine()
            : $tag;
    }

    /**
     * @param AssignmentContainerInterface|AttributesInterface|mixed $markup
     * @param Closure(OrderedValue, OrderedValue): int               $sorter
     *
     * @return list<string>
     */
    private function getSortedAttributes($markup, Closure $sorter)
    {
        $arguments = array_merge(
            $markup instanceof AssignmentContainerInterface
                ? $this->formatOrderedAttributeAssignments($markup)
                : [],
            $markup instanceof AttributesInterface
                ? $this->formatOrderedMarkupAttributes($markup)
                : []
        );
        usort($arguments, $sorter);

        return array_map(static function (OrderedValue $value) {
            return $value->getValue();
        }, $arguments);
    }
}

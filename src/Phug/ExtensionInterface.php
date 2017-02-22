<?php

namespace Phug;

interface ExtensionInterface
{
    public function __construct(Phug $phug);
    // Contents
    public function getParameters();
    public function getMixins();
    public function getBlocks();
    // Lexer
    public function getTokens();
    public function getScanners();
    // Parser
    public function getNodes();
    public function getFilters();
    public function getHandlers();
    // Formatter
    public function getElements();
    public function getFormats();
    // Compiler
    public function getPathResolvers();
    public function getTranslators();
    // Renderer
    public function getAdapters();
}

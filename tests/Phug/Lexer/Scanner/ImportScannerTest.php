<?php

namespace Phug\Test\Lexer\Scanner;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\ImportToken;
use Phug\Test\AbstractLexerTest;

class ImportScannerTest extends AbstractLexerTest
{
    /**
     * @covers \Phug\Lexer\Scanner\ImportScanner
     * @covers \Phug\Lexer\Scanner\ImportScanner::scan
     */
    public function testImport()
    {
        /** @var ImportToken $import */
        list($import) = $this->assertTokens('extend foo/bar.pug', [
            ImportToken::class,
        ]);

        self::assertSame('extend', $import->getName());
        self::assertSame('foo/bar.pug', $import->getPath());

        /** @var ImportToken $import */
        list($import) = $this->assertTokens('extends foo/bar.pug', [
            ImportToken::class,
        ]);

        self::assertSame('extend', $import->getName());
        self::assertSame('foo/bar.pug', $import->getPath());

        /** @var ImportToken $import */
        list($import) = $this->assertTokens('include:markdown-it _foo\\bar', [
            ImportToken::class,
            FilterToken::class,
        ]);

        self::assertSame('include', $import->getName());
        self::assertSame('_foo\\bar', $import->getPath());

        /** @var ImportToken $import */
        /** @var FilterToken $filter */
        list($import, $filter) = $this->assertTokens('includes:markdown-it _foo\\bar', [
            ImportToken::class,
            FilterToken::class,
        ]);

        self::assertSame('include', $import->getName());
        self::assertSame('markdown-it', $filter->getName());
        self::assertSame('_foo\\bar', $import->getPath());

        /** @var ImportToken $import */
        /** @var FilterToken $filter */
        list($import, $filter) = $this->assertTokens('includes:markdown-it(option="(aa)") _foo\\bar', [
            ImportToken::class,
            FilterToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        self::assertSame('include', $import->getName());
        self::assertSame('markdown-it', $filter->getName());
        self::assertSame('_foo\\bar', $import->getPath());

        /** @var ImportToken $import */
        /** @var FilterToken $filter */
        list($import, $filter) = $this->assertTokens('includes:coffee(minify=true) /inc.coffee', [
            ImportToken::class,
            FilterToken::class,
            AttributeStartToken::class,
            AttributeToken::class,
            AttributeEndToken::class,
        ]);

        self::assertSame('include', $import->getName());
        self::assertSame('coffee', $filter->getName());
        self::assertSame('/inc.coffee', $import->getPath());
    }
}

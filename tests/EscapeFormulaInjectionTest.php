<?php

namespace LeagueTest\Csv;

use InvalidArgumentException;
use League\Csv\EscapeFormulaInjection;
use League\Csv\Writer;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;
use TypeError;

/**
 * @group filter
 * @coversDefaultClass League\Csv\EscapeFormulaInjection
 */
class EscapeFormulaInjectionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::filterSpecialCharacters
     */
    public function testConstructorThrowsTypError()
    {
        $this->expectException(TypeError::class);
        new EscapeFormulaInjection("\t", [(object) 'i']);
    }

    /**
     * @covers ::__construct
     * @covers ::getSpecialCharacters
     * @covers ::filterSpecialCharacters
     */
    public function testConstructorThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        new EscapeFormulaInjection("\t", ['i', 'foo']);
    }

    /**
     * @covers ::__construct
     * @covers ::getEscape
     */
    public function testGetEscape()
    {
        $formatter = new EscapeFormulaInjection();
        $this->assertSame("\t", $formatter->getEscape());
        $formatterBis = new EscapeFormulaInjection("\n");
        $this->assertSame("\n", $formatterBis->getEscape());
    }

    /**
     * @covers ::__construct
     * @covers ::getSpecialCharacters
     * @covers ::filterSpecialCharacters
     */
    public function testGetSpecialChars()
    {
        $formatter = new EscapeFormulaInjection();
        $this->assertNotContains('i', $formatter->getSpecialCharacters());
        $formatterBis = new EscapeFormulaInjection("\t", ['i']);
        $this->assertContains('i', $formatterBis->getSpecialCharacters());
    }

    /**
     * @covers ::escapeRecord
     * @covers ::escapeField
     * @covers ::isStringable
     */
    public function testEscapeRecord()
    {
        $record = ['2', '2017-07-25', 'Important Client', '=2+5', 240, null, (object) 'yes'];
        $expected = ['2', '2017-07-25', 'Important Client', "\t=2+5", 240, null, (object) 'yes'];
        $formatter = new EscapeFormulaInjection();
        $this->assertEquals($expected, $formatter->escapeRecord($record));
    }

    /**
     * @covers ::__invoke
     * @covers ::escapeRecord
     * @covers ::escapeField
     * @covers ::isStringable
     */
    public function testFormatterOnWriter()
    {
        $record = ['2', '2017-07-25', 'Important Client', '=2+5', 240, null];
        $expected = "2,2017-07-25,\"Important Client\",\"\t=2+5\",240,\n";
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->addFormatter(new EscapeFormulaInjection());
        $csv->insertOne($record);
        $this->assertContains($expected, $csv->getContent());
    }
}

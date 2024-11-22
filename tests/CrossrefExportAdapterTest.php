<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\adaptsCrossrefForPostprints\classes\CrossrefExportAdapter;

class CrossrefExportAdapterTest extends TestCase
{
    private $crossrefXml;
    private $crossrefExportAdapter;

    public function setUp(): void
    {
        $this->crossrefXml = $this->loadCrossrefXml();
        $this->crossrefExportAdapter = new CrossrefExportAdapter();
    }

    private function loadCrossrefXml()
    {
        $fileContent = file_get_contents(__DIR__ . '/assets/crossrefExport.xml');
        $xml = new DOMDocument('1.0');
        $xml->loadXML($fileContent);

        return $xml;
    }

    public function testAdaptationChangesContentType(): void
    {
        $adaptedExport = $this->crossrefExportAdapter->adaptExport($this->crossrefXml);
        $submissionNodes = $adaptedExport->getElementsByTagName('posted_content');

        foreach ($submissionNodes as $submissionNode) {
            $this->assertEquals('other', $submissionNode->getAttribute('type'));
        }
    }

    public function testAdaptationRemovesRelationsNode(): void
    {
        $originalRelationsNode = $this->crossrefXml->getElementsByTagName('rel:program');
        $this->assertEquals(1, $originalRelationsNode->count());

        $adaptedExport = $this->crossrefExportAdapter->adaptExport($this->crossrefXml);
        $adaptedRelationsNode = $this->crossrefXml->getElementsByTagName('rel:program');

        $this->assertEquals(0, $adaptedRelationsNode->count());
    }
}

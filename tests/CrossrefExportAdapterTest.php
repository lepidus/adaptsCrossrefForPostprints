<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\adaptsCrossrefForPostprints\classes\CrossrefExportAdapter;

class CrossrefExportAdapterTest extends TestCase
{
    private $crossrefXml;

    public function setUp(): void
    {
        $this->crossrefXml = $this->loadCrossrefXml();
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
        $crossrefExportAdapter = new CrossrefExportAdapter();

        $adaptedExport = $crossrefExportAdapter->adaptExport($this->crossrefXml);
        $submissionNodes = $adaptedExport->getElementsByTagName('posted_content');

        foreach ($submissionNodes as $submissionNode) {
            $this->assertEquals('other', $submissionNode->getAttribute('type'));
        }
    }
}

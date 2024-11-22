<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\crossref\CrossrefExportDeployment;
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
        $originalSubmissionNode = $this->crossrefXml->getElementsByTagName('posted_content')->item(0);
        $originalRelationsNode = $originalSubmissionNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'program'
        );
        $this->assertEquals(1, $originalRelationsNode->count());

        $adaptedExport = $this->crossrefExportAdapter->adaptExport($this->crossrefXml);
        $adaptedSubmissionNode = $adaptedExport->getElementsByTagName('posted_content')->item(0);
        $adaptedRelationsNode = $adaptedSubmissionNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'program'
        );

        $this->assertEquals(0, $adaptedRelationsNode->count());
    }
}

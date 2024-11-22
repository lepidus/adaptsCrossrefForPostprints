<?php

use PHPUnit\Framework\TestCase;
use APP\submission\Submission;
use APP\plugins\generic\crossref\CrossrefExportDeployment;
use APP\plugins\generic\adaptsCrossrefForPostprints\classes\CrossrefExportAdapter;

class CrossrefExportAdapterTest extends TestCase
{
    private $crossrefXml;
    private $submission;
    private $adaptedExport;

    public function setUp(): void
    {
        $this->crossrefXml = $this->loadCrossrefXml();
        $this->submission = $this->createSubmission();
    }

    private function loadCrossrefXml()
    {
        $fileContent = file_get_contents(__DIR__ . '/assets/crossrefExport.xml');
        $xml = new DOMDocument('1.0');
        $xml->loadXML($fileContent);

        return $xml;
    }

    private function createSubmission(): Submission
    {
        $submission = new Submission();
        $submission->setData('id', 27);
        $submission->setData('isTranslationOfDoi', '10.7531/OriginalArticle101');
        $submission->setData('locale', 'pt_BR');

        return $submission;
    }

    public function testAdaptationChangesContentType(): void
    {
        $submissionNodes = $this->adaptedExport->getElementsByTagName('posted_content');

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

        $adaptedSubmissionNode = $this->adaptedExport->getElementsByTagName('posted_content')->item(0);
        $adaptedRelationsNode = $adaptedSubmissionNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'program'
        );

        $this->assertEquals(0, $adaptedRelationsNode->count());
    }

    public function testAddsTranslationInformationNode(): void
    {
        $submissionNode = $this->adaptedExport->getElementsByTagName('posted_content')->item(0);
        $programNode = $submissionNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'program'
        )->item(0);
        $relatedItemNode = $programNode->getElementsByTagName(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'related_item'
        )->item(0);
        $descriptionNode = $relatedItemNode->getElementsByTagName(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'description'
        )->item(0);
        $intraWorkRelationNode = $relatedItemNode->getElementsByTagName(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'intra_work_relation'
        )->item(0);

        $this->assertEquals('Portuguese translation', $descriptionNode->nodeValue);
        $this->assertEquals('isTranslationOf', $intraWorkRelationNode->getAttribute('relationship-type'));
        $this->assertEquals('doi', $intraWorkRelationNode->getAttribute('identifier-type'));
        $this->assertEquals($this->submission->getData('isTranslationOfDoi'), $intraWorkRelationNode->nodeValue);
    }
}

<?php

use PHPUnit\Framework\TestCase;
use APP\submission\Submission;
use APP\publication\Publication;
use APP\plugins\generic\crossref\CrossrefExportDeployment;
use APP\plugins\generic\adaptsCrossrefForPostprints\classes\CrossrefExportAdapter;

class CrossrefExportAdapterTest extends TestCase
{
    private $crossrefXml;
    private $crossrefExportAdapter;
    private $submission;

    public function setUp(): void
    {
        $this->crossrefXml = $this->loadCrossrefXml();
        $this->crossrefExportAdapter = new CrossrefExportAdapter();
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
        $publication = new Publication();
        $publication->setData('id', 28);
        $publication->setData('originalDocumentDoi', '10.7531/OriginalArticle101');

        $submission = new Submission();
        $submission->setData('id', 27);
        $submission->setData('locale', 'pt_BR');
        $submission->setData('publications', [$publication]);
        $submission->setData('currentPublicationId', $publication->getId());

        return $submission;
    }

    public function testAdaptationChangesContentType(): void
    {
        $adaptedExport = $this->crossrefExportAdapter->adaptExport(
            $this->crossrefXml,
            [$this->submission->getId() => $this->submission]
        );
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

        $adaptedExport = $this->crossrefExportAdapter->adaptExport(
            $this->crossrefXml,
            [$this->submission->getId() => $this->submission]
        );
        $adaptedSubmissionNode = $adaptedExport->getElementsByTagName('posted_content')->item(0);
        $adaptedRelationsNodes = $adaptedSubmissionNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'program'
        );

        $this->assertEquals(1, $adaptedRelationsNodes->count());
        $adaptedRelationsNode = $adaptedRelationsNodes->item(0);

        $this->assertNotEquals('relations', $adaptedRelationsNode->getAttribute('name'));
    }

    public function testAddsTranslationInformationNode(): void
    {
        $adaptedExport = $this->crossrefExportAdapter->adaptExport(
            $this->crossrefXml,
            [$this->submission->getId() => $this->submission]
        );
        $submissionNode = $adaptedExport->getElementsByTagName('posted_content')->item(0);
        $programNode = $submissionNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'program'
        )->item(0);
        $relatedItemNode = $programNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'related_item'
        )->item(0);
        $descriptionNode = $relatedItemNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'description'
        )->item(0);
        $intraWorkRelationNode = $relatedItemNode->getElementsByTagNameNS(
            CrossrefExportDeployment::CROSSREF_XMLNS_REL,
            'intra_work_relation'
        )->item(0);

        $publication = $this->submission->getCurrentPublication();

        $this->assertEquals('Portuguese translation', $descriptionNode->nodeValue);
        $this->assertEquals('isTranslationOf', $intraWorkRelationNode->getAttribute('relationship-type'));
        $this->assertEquals('doi', $intraWorkRelationNode->getAttribute('identifier-type'));
        $this->assertEquals($publication->getData('originalDocumentDoi'), $intraWorkRelationNode->nodeValue);
    }
}

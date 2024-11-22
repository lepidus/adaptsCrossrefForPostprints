<?php

namespace APP\plugins\generic\adaptsCrossrefForPostprints\classes;

use APP\plugins\generic\crossref\CrossrefExportDeployment;
use PKP\facades\Locale;

class CrossrefExportAdapter
{
    public function adaptExport($crossrefExport, $submissions)
    {
        $submissionNodes = $crossrefExport->getElementsByTagName('posted_content');

        foreach ($submissionNodes as $submissionNode) {
            $submissionNode->setAttribute('type', 'other');

            $relationsNode = $submissionNode->getElementsByTagNameNS(
                CrossrefExportDeployment::CROSSREF_XMLNS_REL,
                'program'
            );

            if ($relationsNode->count() > 0) {
                $relationsNode->item(0)->remove();
            }

            $submissionId = $this->getSubmissionIdFromNode($submissionNode);
            $submission = $submissions[$submissionId];

            if ($submission and $submission->getData('isTranslationOfDoi')) {
                $this->addTranslationInfoNode($crossrefExport, $submissionNode, $submission);
            }
        }

        return $crossrefExport;
    }

    public function getSubmissionIdFromNode($postedContentNode)
    {
        $doiDataNode = $postedContentNode->getElementsByTagName('doi_data')->item(0);
        $resourceNode = $doiDataNode->getElementsByTagName('resource')->item(0);

        preg_match('/\/view\/(\d+)/', $resourceNode->nodeValue, $matches);
        $submissionId = (int) $matches[1];

        return $submissionId;
    }

    private function addTranslationInfoNode($crossrefExport, $postedContentNode, $submission)
    {
        $originalDoi = $submission->getData('isTranslationOfDoi');
        $submissionLocale = $submission->getData('locale');
        $localesMetadata = Locale::getLocales();
        $localeName = $localesMetadata[$submissionLocale]->getDisplayName();

        $relationsNamespace = CrossrefExportDeployment::CROSSREF_XMLNS_REL;
        $doiDataNode = $postedContentNode->getElementsByTagName('doi_data')->item(0);

        $programNode = $crossrefExport->createElementNS($relationsNamespace, 'program');
        $relatedItemNode = $crossrefExport->createElementNS($relationsNamespace, 'related_item');
        $relatedItemNode->appendChild($node = $crossrefExport->createElementNS(
            $relationsNamespace,
            'description',
            htmlspecialchars("$localeName translation", ENT_COMPAT, 'UTF-8')
        ));
        $relatedItemNode->appendChild($node = $crossrefExport->createElementNS(
            $relationsNamespace,
            'intra_work_relation',
            htmlspecialchars($originalDoi, ENT_COMPAT, 'UTF-8')
        ));
        $node->setAttribute('relationship-type', 'isTranslationOf');
        $node->setAttribute('identifier-type', 'doi');
        $programNode->appendChild($relatedItemNode);

        $doiDataNode->parentNode->insertBefore($programNode, $doiDataNode);
    }
}

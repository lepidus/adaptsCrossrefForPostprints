<?php

namespace APP\plugins\generic\adaptsCrossrefForPostprints\classes;

use APP\plugins\generic\crossref\CrossrefExportDeployment;

class CrossrefExportAdapter
{
    public function adaptExport($crossrefExport)
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
        }

        return $crossrefExport;
    }
}

<?php

namespace APP\plugins\generic\adaptsCrossrefForPostprints\classes;

class CrossrefExportAdapter
{
    public function adaptExport($crossrefExport)
    {
        $submissionNodes = $crossrefExport->getElementsByTagName('posted_content');

        foreach ($submissionNodes as $submissionNode) {
            $submissionNode->setAttribute('type', 'other');
        }

        return $crossrefExport;
    }
}

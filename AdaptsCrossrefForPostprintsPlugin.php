<?php

/**
 * @file plugins/generic/adaptsCrossrefForPostprints/AdaptsCrossrefForPostprintsPlugin.php
 *
 * Copyright (c) 2024 Lepidus Tecnologia
 * Copyright (c) 2024 SciELO
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt.
 *
 * @class AdaptsCrossrefForPostprintsPlugin
 * @ingroup plugins_generic_adaptsCrossrefForPostprints
 *
 */

namespace APP\plugins\generic\adaptsCrossrefForPostprints;

use PKP\plugins\Hook;
use PKP\plugins\GenericPlugin;
use APP\core\Application;
use APP\facades\Repo;
use APP\plugins\generic\adaptsCrossrefForPostprints\classes\CrossrefExportAdapter;

class AdaptsCrossrefForPostprintsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('preprintcrossrefxmlfilter::execute', [$this, 'adaptsCrossrefExporting']);
        }

        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.adaptsCrossrefForPostprints.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.adaptsCrossrefForPostprints.description');
    }

    public function adaptsCrossrefExporting($hookName, $params)
    {
        $preliminaryOutput = &$params[0];
        $crossrefExportAdapter = new CrossrefExportAdapter();

        $submissionNodes = $preliminaryOutput->getElementsByTagName('posted_content');
        $submissions = [];

        foreach ($submissionNodes as $submissionNode) {
            $submissionId = $crossrefExportAdapter->getSubmissionIdFromNode($submissionNode);
            $submission = Repo::submission()->get($submissionId);

            $submission->setData('isTranslationOfDoi', '10.7531/OriginalArticle101');

            if ($submission) {
                $submissions[$submissionId] = $submission;
            }
        }

        $adaptedExport = $crossrefExportAdapter->adaptExport($preliminaryOutput, $submissions);
        $preliminaryOutput = $adaptedExport;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\adaptsCrossrefForPostprints\AdaptsCrossrefForPostprintsPlugin', '\AdaptsCrossrefForPostprintsPlugin');
}

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

class AdaptsCrossrefForPostprintsPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }

        // if ($success && $this->getEnabled($mainContextId)) {
        // }

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
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\adaptsCrossrefForPostprints\AdaptsCrossrefForPostprintsPlugin', '\AdaptsCrossrefForPostprintsPlugin');
}

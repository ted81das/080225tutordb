<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\JetEngine\JetEngineRecordHelper;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_jet_engine_create_post_type_actions', [JetEngineRecordHelper::class, 'createPostTypeActions'], 10, 3);
Hooks::filter('btcbi_jet_engine_create_content_type_actions', [JetEngineRecordHelper::class, 'createContentTypeActions'], 10, 3);
Hooks::filter('btcbi_jet_engine_create_taxonomy_actions', [JetEngineRecordHelper::class, 'createTaxonomyActions'], 10, 3);
Hooks::filter('btcbi_jet_engine_create_relation_actions', [JetEngineRecordHelper::class, 'createRelationActions'], 10, 3);

<?php

return array(
    'autoresolve' => true, // If this is disabled you'll need to call Lang::resolve() manually somewhere in your code
    'route_segment'=> 1, // Language code position in the route segments (starting from 1)
    'use_database' => false, // Whether to rely on the database languages or the config ones only (false by default)
    'use_useragent' => false, // Whether to resolve from HTTP_ACCEPT_LANGUAGE as a route fallback or not (false by default)
    'available_locales' => array('es' => 'es_ES', 'en' => 'en_US', 'fr' => 'fr_FR', 'it' => 'it_IT', 'de' => 'de_DE', 'pt' => 'pt_PT')
);

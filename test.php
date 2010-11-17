<?php

function expireCacheBlock( $template, $keyAttributeValue )
{
    $functionPlacement = fetchCacheBlockPlacement( $template );
    $placementKeyString = eZTemplateCacheBlock::placementString( $functionPlacement );

    $keys = array(
        // cache-block key attribute
        $keyAttributeValue,
        // cache-block placement in the template
        $placementKeyString,
        // siteaccess
        $GLOBALS['eZCurrentAccess']['name'],
    );

    // subtree_expiry is NOT supported
    $nodeID = false;

    $cachePath = eZTemplateCacheBlock::cachePath(
        eZTemplateCacheBlock::keyString( $keys ), $nodeID
    );

    // should use the cluster API here
    unlink( $cachePath );
}

function fetchCacheBlockPlacement( $template )
{
    $ini = eZINI::instance();
    $ini->setVariable( 'TemplateSettings', 'TemplateCompile', 'disabled' );

    $tpl = eZTemplate::factory();
    $resource = $tpl->loadURIRoot(
        "file:{$template}", $displayErrors = false, $extraParameters = array()
    );

    $tpl->process( $resource['root-node'], $text, "", "" );
    $children = $resource['root-node'][1];

    $functionPlacement = array();

    // searching for the {cache-block ...} function
    // and its placement
    foreach ( $children as $child)
    {
        $nodeType = $child[0];
        if( $nodeType == eZTemplate::NODE_FUNCTION )
        {
            $functionPlacement = $child[4];
        }
    }

    return $functionPlacement;
}

//
// change the $template and $key variable and run
// this file with ezexec.php :
// php ./bin/php/ezexec.php -s <siteaccess> <thisfile>.php

$template = 'extension/varnishcache/design/varnishcachetests/templates/testcacheblock.tpl';
$key = 'varnishtest';
expireCacheBlock( $template, $key );
?>

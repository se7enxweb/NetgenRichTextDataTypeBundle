<?php

use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\RichTextStorage\Gateway;

/**
 * Rich text storage gateway of the legacy datatype: the URL bookkeeping goes through
 * eZRichTextLegacyUrlGateway and the remote id lookup through the legacy database connection,
 * so that storing an attribute never writes through a second connection (see that class).
 *
 * Only loaded when the platform's rich text package (ezplatform-richtext) is installed.
 */
class eZRichTextLegacyStorageGateway extends Gateway
{
    public function getContentIds(array $remoteIds)
    {
        $map = array();
        if (empty($remoteIds)) {
            return $map;
        }

        $db = eZDB::instance();
        $rows = $db->arrayQuery(
            'SELECT id, remote_id FROM ezcontentobject WHERE ' .
            'remote_id IN (' . eZRichTextLegacyUrlGateway::quotedList($db, $remoteIds) . ')'
        );
        foreach ((array)$rows as $row) {
            $map[$row['remote_id']] = $row['id'];
        }

        return $map;
    }
}

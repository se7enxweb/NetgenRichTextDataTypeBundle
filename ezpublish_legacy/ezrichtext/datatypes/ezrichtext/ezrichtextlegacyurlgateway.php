<?php

use Ibexa\Core\FieldType\Url\UrlStorage\Gateway;

/**
 * URL storage gateway of the rich text datatype that works through the legacy database connection.
 *
 * The platform's own gateway writes ezurl and ezurl_object_link through the Symfony (Doctrine)
 * connection. The legacy kernel stores an object attribute inside its own transaction on its own
 * connection, so those writes went to a second connection while the first one held the write
 * lock: on SQLite every edit, publish and upload of content with a rich text field waited for
 * its own lock and failed with "database is locked"; on the other engines the URL links were
 * written outside the legacy transaction and survived its rollback. This gateway runs the same
 * queries as the platform's DoctrineStorage, inside the legacy transaction.
 */
class eZRichTextLegacyUrlGateway extends Gateway
{
    const URL_TABLE = 'ezurl';
    const URL_LINK_TABLE = 'ezurl_object_link';

    /**
     * @return \eZDBInterface
     */
    protected function db()
    {
        return eZDB::instance();
    }

    /**
     * Returns the strings as a comma separated list of escaped SQL string literals.
     *
     * @param \eZDBInterface $db
     * @param string[] $values
     *
     * @return string
     */
    public static function quotedList($db, array $values)
    {
        $quoted = array();
        foreach (array_unique(array_map('strval', $values)) as $value) {
            $quoted[] = "'" . $db->escapeString($value) . "'";
        }

        return implode(', ', $quoted);
    }

    public function getIdUrlMap(array $ids)
    {
        $map = array();
        if (empty($ids)) {
            return $map;
        }

        $db = $this->db();
        $rows = $db->arrayQuery(
            'SELECT id, url FROM ' . self::URL_TABLE . ' WHERE ' .
            $db->generateSQLINStatement(array_map('intval', $ids), 'id', false, true, 'int')
        );
        foreach ((array)$rows as $row) {
            $map[$row['id']] = $row['url'];
        }

        return $map;
    }

    public function getUrlIdMap(array $urls)
    {
        $map = array();
        if (empty($urls)) {
            return $map;
        }

        $db = $this->db();
        $rows = $db->arrayQuery(
            'SELECT id, url FROM ' . self::URL_TABLE . ' WHERE ' .
            'url IN (' . self::quotedList($db, $urls) . ')'
        );
        foreach ((array)$rows as $row) {
            $map[$row['url']] = $row['id'];
        }

        return $map;
    }

    public function insertUrl($url)
    {
        $db = $this->db();
        $time = time();
        $db->query(
            'INSERT INTO ' . self::URL_TABLE . ' (created, modified, original_url_md5, url) VALUES (' .
            $time . ', ' . $time . ", '" . $db->escapeString(md5($url)) . "', '" . $db->escapeString($url) . "')"
        );

        return (int)$db->lastSerialID(self::URL_TABLE, 'id');
    }

    public function linkUrl($urlId, $fieldId, $versionNo)
    {
        $this->db()->query(
            'INSERT INTO ' . self::URL_LINK_TABLE .
            ' (contentobject_attribute_id, contentobject_attribute_version, url_id) VALUES (' .
            (int)$fieldId . ', ' . (int)$versionNo . ', ' . (int)$urlId . ')'
        );
    }

    public function unlinkUrl($fieldId, $versionNo, array $excludeUrlIds = array())
    {
        $db = $this->db();
        $sql = 'DELETE FROM ' . self::URL_LINK_TABLE .
            ' WHERE contentobject_attribute_id = ' . (int)$fieldId .
            ' AND contentobject_attribute_version = ' . (int)$versionNo;
        if (!empty($excludeUrlIds)) {
            $sql .= ' AND ' . $db->generateSQLINStatement(array_map('intval', $excludeUrlIds), 'url_id', true, true, 'int');
        }
        $db->query($sql);

        $this->deleteOrphanedUrls();
    }

    /**
     * Removes the URLs no object attribute links to any more, as the platform's gateway does.
     */
    protected function deleteOrphanedUrls()
    {
        $db = $this->db();
        $rows = $db->arrayQuery(
            'SELECT url.id AS id FROM ' . self::URL_TABLE . ' url LEFT JOIN ' . self::URL_LINK_TABLE .
            ' link ON url.id = link.url_id WHERE link.url_id IS NULL'
        );
        $ids = array();
        foreach ((array)$rows as $row) {
            $ids[] = (int)$row['id'];
        }
        if (empty($ids)) {
            return;
        }

        $db->query(
            'DELETE FROM ' . self::URL_TABLE . ' WHERE ' . $db->generateSQLINStatement($ids, 'id', false, true, 'int')
        );
    }
}

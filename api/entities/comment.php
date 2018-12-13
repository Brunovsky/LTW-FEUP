<?php
require_once __DIR__ . '/apientity.php';
require_once __DIR__ . '/entity.php';
require_once __DIR__ . '/story.php';

class Comment extends APIEntity {
    /**
     * $more Default Constants
     */
    protected static $defaultSince = 0;
    protected static $defaultLimit = 50;
    protected static $defaultOffset = 0;
    protected static $defaultSortTable = 'CommentSortBest';

    /**
     * Extend a normal query's arguments $args with since, limit and offset.
     * The query string ends with:
     *
     *      [AND|WHERE] createdat >= ? LIMIT ? OFFSET ?
     *                               ^       ^        ^- $offset
     *                               |       +--- $limit
     *                               +--- $since
     *
     * So we push to $args array values $since, $limit and $offset IN THIS ORDER.
     */
    private static function extend(array $args, array $more) {
        $since = static::since($more);
        $limit = static::limit($more);
        $offset = static::offset($more);

        $args[] = $since;
        $args[] = $limit;
        $args[] = $offset;

        return $args;
    }

    /**
     * AUXILIARY
     *
     * Select the appropriate story table view based on sorting desired.
     *
     * Switch statement prevents SQL injection.
     */
    private static function sortTablename($more) {
        if (!isset($more['order'])) return static::$defaultSortTable;

        $order = $more['order'];
        if (!is_string($order)) return static::$defaultSortTable;

        switch ($order) {
        case 'top': return 'CommentSortTop';
        case 'bot': return 'CommentSortBot';
        case 'new': return 'CommentSortNew';
        case 'old': return 'CommentSortOld';
        case 'best': return 'CommentSortBest';
        case 'controversial': return 'CommentSortControversial';
        case 'average': return 'CommentSortAverage';
        case 'hot': return 'CommentSortHot';
        default: return static::$defaultSortTable;
        }
    }

    /**
     * CREATE
     */
    public static function create(int $parentid, int $authorid, string $content) {
        $query = '
            INSERT INTO Comment(parentid, authorid, content)
            VALUES (?, ?, ?)
            ';

        $stmt = DB::get()->prepare($query);

        try {
            DB::get()->beginTransaction();
            $stmt->execute([$parentid, $authorid, $content]);
            $row = DB::get()->query('SELECT max(entityid) id FROM Entity')->fetch();
            DB::get()->commit();
            return (int)$row['id'];
        } catch (PDOException $e) {
            DB::get()->rollback();
            return false;
        }
    }

    /**
     * READ
     */
    public static function getChildrenAuthor(int $parentid, int $authorid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT * FROM $sorttable ST WHERE parentid = ? AND authorid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$parentid, $authorid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    public static function getChildren(int $parentid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT * FROM $sorttable ST WHERE parentid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$parentid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    public static function getAuthor(int $authorid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT * FROM $sorttable ST WHERE authorid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$authorid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    public static function read(int $id) {
        $query = '
            SELECT * FROM CommentAll WHERE entityid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$id]);
        return static::fetch($stmt);
    }

    public static function readAll(array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT * FROM $sorttable ST
            WHERE createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    /**
     * VOTED READ
     */
    public static function getChildrenAuthorVoted(int $parentid, int $authorid,
            int $userid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT ST.*, coalesce(V.vote, '') vote
            FROM $sorttable ST NATURAL JOIN UserVote V
            WHERE parentid = ? AND authorid = ? AND V.userid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$parentid, $authorid, $userid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    public static function getChildrenVoted(int $parentid, int $userid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT ST.*, coalesce(V.vote, '') vote
            FROM $sorttable ST NATURAL JOIN UserVote V
            WHERE parentid = ? AND V.userid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$parentid, $userid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    public static function getAuthorVoted(int $authorid, int $userid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT ST.*, coalesce(V.vote, '') vote
            FROM $sorttable ST NATURAL JOIN UserVote V
            WHERE authorid = ? AND V.userid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$authorid, $userid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    public static function readVoted(int $id, int $userid) {
        $query = '
            SELECT CA.*, coalesce(V.vote, "") vote
            FROM CommentAll CA NATURAL JOIN UserVote V
            WHERE CA.entityid = ? AND V.userid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$id, $userid]);
        return static::fetch($stmt);
    }

    public static function readAllVoted(int $userid, array $more = []) {
        $sorttable = static::sortTablename($more);

        $query = "
            SELECT ST.*, coalesce(V.vote, '') vote
            FROM $sorttable ST NATURAL JOIN UserVote V
            WHERE V.userid = ?
            AND createdat >= ?
            ORDER BY rating DESC
            LIMIT ? OFFSET ?
            ";

        $queryArguments = static::extend([$userid], $more);

        $stmt = DB::get()->prepare($query);
        $stmt->execute($queryArguments);
        return static::fetchAll($stmt);
    }

    /**
     * UPDATE
     */
    public static function update(int $entityid, string $content) {
        $query = '
            UPDATE Comment SET content = ? WHERE entityid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$content, $entityid]);
        return $stmt->rowCount();
    }

    public static function clear(int $entityid) {
        $query = '
            UPDATE Comment SET content = "" WHERE entityid = ?
            ';

        $stmt = DB::get()->prepare($query);
        return $stmt->execute([$entityid]);
    }

    /**
     * DELETE
     */
    public static function delete(int $entityid) {
        $query = '
            DELETE FROM Comment WHERE entityid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$entityid]);
        return $stmt->rowCount();
    }

    public static function deleteAuthor(int $authorid) {
        $query = '
            DELETE FROM Comment WHERE authorid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$authorid]);
        return $stmt->rowCount();
    }

    public static function deleteChildren(int $parentid) {
        $query = '
            DELETE FROM Comment WHERE parentid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$parentid]);
        return $stmt->rowCount();
    }

    public static function deleteChildrenAuthor(int $parentid, int $authorid) {
        $query = '
            DELETE FROM Comment WHERE parentid = ? AND authorid = ?
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute([$parentid, $authorid]);
        return $stmt->rowCount();
    }

    public static function deleteAll() {
        $query = '
            DELETE FROM Comment
            ';

        $stmt = DB::get()->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>

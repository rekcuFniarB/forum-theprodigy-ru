<?php
$dbprefix = $this->app->db->db_prefix;
$r = $this->app->db->query(
    "SELECT ID_BOARD, b.name AS boardname, b.ID_CAT, c.name AS catname, boardOrder, catOrder
        FROM {$dbprefix}boards b LEFT JOIN {$dbprefix}categories c ON b.ID_CAT = c.ID_CAT
        ORDER BY catOrder, boardOrder"
) or database_error(__FILE__, __LINE__, $app->db);
$boardscats = array();
$catnames = array();
while ($row = $r->fetch_assoc()) {
    if (!isset($boardscats[$row['ID_CAT']])) {
        $boardscats[$row['ID_CAT']] = array();
        $catnames[$row['ID_CAT']] = $row['catname'];
    }
    $boardscats[$row['ID_CAT']][] = $row;
}
$this->menu = $boardscats;
$this->menuCatNames = $catnames;
        
?>

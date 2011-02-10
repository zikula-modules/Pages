<?php
/**
 * pages table information
*/
function Pages_tables()
{
    // Initialise table array
    $table = array();

    // Full table definition
    $table['pages'] = DBUtil::getLimitedTablename('pages');
    $table['pages_column'] = array ('pageid'         => 'pn_pageid',
                                      'title'          => 'pn_title',
                                      'urltitle'       => 'pn_urltitle',
                                      'content'        => 'pn_content',
                                      'counter'        => 'pn_counter',
                                      'displaywrapper' => 'pn_displaywrapper',
                                      'displaytitle'   => 'pn_displaytitle',
                                      'displaycreated' => 'pn_displaycreated',
                                      'displayupdated' => 'pn_displayupdated',
                                      'displaytextinfo' => 'pn_displaytextinfo',
                                      'displayprint'   => 'pn_displayprint',
                                      'language'       => 'pn_language');
    $table['pages_column_def'] = array('pageid'         => 'I AUTOINCREMENT PRIMARY',
                                         'title'          => "X NOTNULL DEFAULT ''",
                                         'urltitle'       => "X NOTNULL DEFAULT ''",
                                         'content'        => "X NOTNULL DEFAULT ''",
                                         'counter'        => "I NOTNULL DEFAULT '0'",
                                         'displaywrapper' => "I1 NOTNULL DEFAULT '1'",
                                         'displaytitle'   => "I1 NOTNULL DEFAULT '1'",
                                         'displaycreated' => "I1 NOTNULL DEFAULT '1'",
                                         'displayupdated' => "I1 NOTNULL DEFAULT '1'",
                                         'displaytextinfo' => "I1 NOTNULL DEFAULT '1'",
                                         'displayprint'   => "I1 NOTNULL DEFAULT '1'",
                                         'language'       => "C(30) NOTNULL DEFAULT ''");

    // Enable categorization services
    $table['pages_db_extra_enable_categorization'] = ModUtil::getVar('Pages', 'enablecategorization');
    $table['pages_primary_key_column'] = 'pageid';

    // add standard data fields
    ObjectUtil::addStandardFieldsToTableDefinition($table['pages_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($table['pages_column_def']);

    // old tables for upgrade/renaming purposes
    $table['seccont']  = DBUtil::getLimitedTablename('seccont');
    $table['sections'] = DBUtil::getLimitedTablename('sections');

    return $table;
}
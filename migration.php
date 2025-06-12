<?php

//example
$db = Entities\Database::getInstance();
$db->getConnection();
$db->createUsersTable();
$db->createNotesTable();
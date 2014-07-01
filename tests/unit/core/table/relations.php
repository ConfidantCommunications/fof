<?php

use FOF30\Table\Relations as F0FTableRelations;

class FtestTableRelations extends F0FTableRelations
{
    public function normaliseParameters($pivot = false, &$itemName, &$tableClass, &$localKey, &$remoteKey, &$ourPivotKey, &$theirPivotKey, &$pivotTable)
    {
        parent::normaliseParameters($pivot, $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $pivotTable);
    }
}
<?php

use FOF30\Table\Relations as FOFTableRelations;

class FtestTableRelations extends FOFTableRelations
{
    public function normaliseParameters($pivot = false, &$itemName, &$tableClass, &$localKey, &$remoteKey, &$ourPivotKey, &$theirPivotKey, &$pivotTable)
    {
        parent::normaliseParameters($pivot, $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $pivotTable);
    }
}
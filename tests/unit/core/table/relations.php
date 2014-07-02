<?php

class FtestTableRelations extends FOF30\Table\Relations
{
    public function normaliseParameters($pivot = false, &$itemName, &$tableClass, &$localKey, &$remoteKey, &$ourPivotKey, &$theirPivotKey, &$pivotTable)
    {
        parent::normaliseParameters($pivot, $itemName, $tableClass, $localKey, $remoteKey, $ourPivotKey, $theirPivotKey, $pivotTable);
    }
}
<?php

namespace Ujamii\Cobot\Filter\Enum;

enum AssetCollectionFilterType: string
{
    case ALL = 'all';
    case SELECTED = 'selected';
    case WITHOUT = 'without';
}
